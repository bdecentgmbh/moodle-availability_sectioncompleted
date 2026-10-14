---
exclude: true
---

# Restriction by other section completion — Developer guide

For developers who create or read these restrictions in code, for example in course generators,
migrations or reports.

## The condition in the availability JSON

```json
{"type": "sectioncompleted", "id": 123, "e": "pass", "m": 2}
```

| Key | Value | Default |
| --- | --- | --- |
| `id` | `course_sections.id` of the section whose activities count; `0` for none | `0` |
| `e` | `complete` (complete, passed or failed), `pass` (`COMPLETION_COMPLETE_PASS`) or `fail` (`COMPLETION_COMPLETE_FAIL`) | `complete` |
| `m` | Number of activities that must count, `0` to `1000`; `0` means all of them | `0` |

Restrictions saved by version 1.1 and earlier have only `id`; they mean `e = complete`, `m = 0`.
Any other value makes the constructor throw a `coding_exception`, which core reports as an invalid
restriction.

Build the structure with `\availability_sectioncompleted\condition::get_json($sectionid, $state, $min)`
and wrap it in a tree as for any condition:

```php
use availability_sectioncompleted\condition;

$json = json_encode([
    'op' => '&',
    'c' => [condition::get_json($section->id, condition::STATE_PASS, 2)],
    'showc' => [true],
]);
course_update_section($course, $targetsection, ['availability' => $json]);
```

## Rules the condition follows

- The activities that count are the section's completion-tracked activities that are not being deleted
  and, for a section that isn't a subsection, those of its subsections
  (`condition::get_tracked_cms()`). Hidden activities count.
- A minimum above the number of activities requires all of them.
- A missing section, or one without tracked activities, makes the condition available, also when it
  is inverted.
- `is_available()` uses only `$info->get_modinfo()`, the modinfo of the user being checked. Calling
  `get_fast_modinfo()` without that user there rebuilds the course cache for every checked user (#14).
  Subsections are therefore found with `course_modinfo::get_section_info_by_component()` and
  `get_instances_of()`; on Moodle 4.5, `section_info::get_component_instance()` and the methods built on
  it load the current user's modinfo.
- Completion states of the current user come from `completion_info::get_data()` and its cache; those of
  other users from one query on `course_modules_completion`.
- `completion_value_used()` returns true for activities in a section (or a subsection of a section)
  that a condition of the course depends on, so core reloads the course page when they are marked done.
- `update_after_restore()` maps the section through the `course_section` backup ids. Without a mapping
  it keeps a section of the target course and otherwise sets `id` to `0` and logs a warning.
- Descriptions name the section through `description_callback()`, resolved at display time by
  `get_description_callback_value()`.

## Tests

PHPUnit tests are in `tests/condition_test.php` and `tests/privacy/provider_test.php`, Behat scenarios in
`tests/behat/`. The GitHub workflow runs them with the Moodle code checker on Moodle 4.5 to 5.3, and
publishes tagged releases to the Moodle Marketplace (see the README).
