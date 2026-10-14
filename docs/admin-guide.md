---
pagetypes:
  - admin-tool-availabilityconditions-*
---

# Restriction by other section completion — Administrator guide

*Restriction by other section completion* (`availability_sectioncompleted`) is an availability
condition for activities and course sections: it releases them once a learner has completed the
activities of another section. How teachers use it is described in the
[Teacher guide](teacher-guide.md).

## Requirements

- Moodle 4.5 or later; supported on Moodle 4.5 up to 5.3.
- **Enable completion tracking** and **Enable restricted access** in *Site administration ▸ Advanced
  features*. Courses need completion tracking too; teachers enable it in the course settings.
- For subsections (Moodle 4.5 and later), the **Subsection** activity must be enabled in
  *Site administration ▸ Plugins ▸ Activity modules ▸ Manage activities*.

## Installation

1. Install it from the Moodle Marketplace under *Site administration ▸ Plugins ▸ Install plugins*, or
   unpack the `sectioncompleted` folder of the ZIP into `availability/condition/` of your Moodle code
   (`public/availability/condition/` on Moodle 5.1 and later).
2. Run the upgrade under *Site administration ▸ Notifications*.

The plugin has no settings. Like every restriction, it can be hidden from the *Add restriction* dialog
in *Site administration ▸ Plugins ▸ Availability restrictions ▸ Manage restrictions*; restrictions
that already exist keep working.

## Upgrading from 1.1

Version 1.2 changes what some existing restrictions require:

- **Subsections count for their section.** A restriction on a section with subsections now also
  needs the activities in those subsections.
- **Sections without completion-tracked activities** were never restricting anything. They are no
  longer listed, so teachers who edit such a restriction choose a section again or remove it.
- **Inverted restrictions** ("must not") on a deleted section or on a section without tracked
  activities no longer lock the item.

All changes are listed in [CHANGES.md](../CHANGES.md).

## Permissions

There are no capabilities of its own. Whoever can edit an activity or a section
(`moodle/course:manageactivities`) can add the restriction; core's restriction editor checks this.

## Performance

When Moodle checks the restriction for other users, as badge, forum, completion and report tasks
do, it reads that user's completion of the section's activities in one query and never rebuilds the
course cache. Up to version 1.1, chained section restrictions could make such tasks run for hours.

## Backup and restore

Restrictions are part of Moodle's own backup. On restore and import they point to the restored
course's sections. When a restored activity refers to a section that was not restored, the restriction
loses its section and the restore log shows a warning.

## Privacy

The plugin stores no personal data. It reads the activity completion that Moodle stores; Moodle's
completion privacy provider covers that.
