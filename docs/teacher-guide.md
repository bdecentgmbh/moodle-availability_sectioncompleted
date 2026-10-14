---
audience: teacher, manager
pagetypes:
  - course-editsection
  - mod-assign-mod
  - mod-bigbluebuttonbn-mod
  - mod-book-mod
  - mod-choice-mod
  - mod-data-mod
  - mod-feedback-mod
  - mod-folder-mod
  - mod-forum-mod
  - mod-glossary-mod
  - mod-h5pactivity-mod
  - mod-imscp-mod
  - mod-label-mod
  - mod-lesson-mod
  - mod-lti-mod
  - mod-page-mod
  - mod-quiz-mod
  - mod-resource-mod
  - mod-scorm-mod
  - mod-subsection-mod
  - mod-url-mod
  - mod-wiki-mod
  - mod-workshop-mod
---

# Restriction by other section completion — Teacher guide

*Restriction by other section completion* releases an activity or a section once a learner has
completed the activities of another section: all of them, or a number of them, in any way, with a
pass grade or with a fail grade.

## Before you start

The restriction is offered in a course that tracks completion (*Course settings ▸ Completion
tracking*). It lists only sections that contain activities with completion tracking, so give the
activities of a section their completion conditions first.

## Adding a restriction

1. Open the settings of an activity or section and expand **Restrict access**.
2. Select **Add restriction… ▸ Section completion**.
3. Choose the **section** whose activities count.
4. Choose how many: **all activities**, or **at least 1, 2, …** of them. The numbers offered depend on
   the section's completion-tracked activities.
5. Choose what counts:

   | Option | An activity counts when it is |
   | --- | --- |
   | **must be completed** | complete in any way: completed, passed or failed |
   | **must be complete with pass grade** | complete with a passing grade |
   | **must be complete with fail grade** | complete with a failing grade |

6. Save. Like any restriction, you can combine it with others, turn it into *must not*, or hide the
   restricted item from learners who don't meet it (the eye icon).

## The section list

- **Previous section** is the section before the one you are editing, or before the section of the
  activity; for a subsection, the section before its parent. Pick it to build a course in which each
  section opens after the one before.
- Subsections appear after their section as **Section › Subsection**.
- The list leaves out the section you are editing or the activity is in, its parent section and,
  when you restrict a section, its subsections: learners could never complete them while the item is
  locked.
- Sections without completion-tracked activities are not listed.

## What counts

- **Subsections count for their section.** A restriction on *Week 2* needs the activities in the
  subsections of *Week 2* too. A restriction on the subsection *Week 2 › Homework* needs only the
  homework.
- **Pass and fail need a grade.** Activities completed without a grade, such as pages or manually
  ticked activities, are complete, but neither passed nor failed. "All activities, must be complete
  with pass grade" on a section with a page can therefore never be met; require a number instead, or
  use *must be completed*.
- **Hidden activities count.** Learners cannot complete an activity they cannot see, so hiding a
  completion-tracked activity keeps "all activities" from being met. Remove its completion tracking,
  or require a number of activities.
- **A number above the section's activities means all of them.** If activities are removed later,
  "at least 5" on a section with 3 requires the 3.
- **Nothing to complete, nothing restricted.** A section that has no completion-tracked activities
  (any more), or that was deleted, does not restrict anything, also when the restriction is turned
  into *must not*. For a deleted section, the restriction reads *(Missing section) completed.*

## What learners see

A learner who doesn't meet the restriction sees the requirement and how far they are, for example:

- *Not available unless: Week 1 completed. (2 of 3 so far)*
- *Not available unless: At least 2 of the activities in Week 1 completed. (1 of 3 so far)*
- *Not available unless: Week 3 completed and passed. (1 of 2 so far)*

When they mark the last activity they need as done, the course page reloads and the item opens.

## Examples

| Goal | Restriction |
| --- | --- |
| Open each week after the previous one | On the section: **Previous section**, all activities, must be completed |
| Open the next level after passing all quizzes of a section | All activities, must be complete with pass grade |
| Offer extra help after a failed quiz | At least 1, must be complete with fail grade; hide it otherwise (eye icon) |
| Let fast learners skip ahead | At least 2 activities of *Week 1*, must be completed |
| Show "getting started" tips until a section is done | *Must not*: all activities, must be completed |

## Copying, backup and restore

Restored and imported courses point the restriction to their own sections. An activity duplicated in
the same course keeps its restriction. When a single activity is restored into a course that doesn't
have the section, the restriction loses its section and the restore log says so: choose the section
again.

## When something doesn't work

- **"Section completion" is not offered.** The course doesn't track completion, or no other section
  has completion-tracked activities.
- **A section is missing from the list.** It has no completion-tracked activities, or the item you
  are editing is in it, in one of its subsections or in its parent section.
- **Learners stay locked out.** Look for hidden activities, and for pass or fail options on sections
  with activities that have no grade.
