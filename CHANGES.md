# Changes

## 1.2 (2026101400)

- Moodle 4.5 to 5.3; Moodle 4.5 is now the minimum.
- Performance: checking the restriction for another user (badges, completion and report tasks) no
  longer rebuilds the course cache for every user (#14, thanks to Nicolas Jacquemet for #15).
- New: require at least a number of the section's activities instead of all of them.
- New: require the activities to be complete with pass grade or with fail grade. Activities completed
  without a grade count as completed, but neither as passed nor as failed.
- New: students who do not meet the restriction see how many activities count so far.
- Subsections (#12): they can be chosen as "Section › Subsection", and the activities in a section's
  subsections now count for that section. A section with subsections therefore needs their activities
  too.
- The restriction is only offered when the course tracks completion, and only sections with
  completion-tracked activities are listed (#13). A section cannot be restricted by itself, its
  subsections or its parent section.
- "Previous section" is right for activities in subsections.
- Backup and restore: restored and imported courses point the restriction to their own sections.
- Fixed: the form showed an unknown string when no section was selected.
- Reports for other users use that user's completion (#10, fixed in 1.1; now covered by tests).
- PHP 8.4: nullable parameter types.
- PHPUnit tests (they did not run before), Behat features and CI for Moodle 4.5 to 5.3.

## 1.1 (2025032401)

- Moodle 4.5 compatibility.
- Reports for other users use that user's completion (#10).
