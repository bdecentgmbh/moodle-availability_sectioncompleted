moodle-availability_sectioncompleted
========================

Moodle availability plugin which lets users restrict resources, activities and sections based on sections being completed


Requirements
------------

This plugin requires Moodle 4.5 or later and is supported on Moodle 4.5 up to 5.3.


Motivation for this plugin
--------------------------

This plugin was built to enable teachers to easily restrict activities or sections to students that have completed another, usually the previous section.

This is currently already possible by adding several restrictions; this plugin simplifies that for the teacher – and has also the nice and intended side effect that it looks better for the student.


Installation
------------

Install the plugin like any other plugin to folder
/availability/condition/sectioncompleted (on Moodle 5.1 and later: /public/availability/condition/sectioncompleted)

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it is ready to use without the need for any configuration.

Teachers (and other users with editing rights) can add the "Section completion" restriction to activities, resources and sections in their courses. The restriction is offered when the course tracks completion and has a section with completion-tracked activities. It has three settings:

* The section. Only sections with completion-tracked activities are listed. "Previous section" picks the section before the one the item is in. Subsections (Moodle 4.5 and later) are listed after their section as "Section › Subsection".
* How many activities: all completion-tracked activities of the section, or at least a given number of them.
* The completion state an activity needs to count: completed in any way (complete, passed or failed), complete with pass grade, or complete with fail grade. Activities completed without a grade, such as pages, count as completed but neither as passed nor as failed.

The activities in a section's subsections count for that section. A subsection on its own only counts its own activities. A section without completion-tracked activities, or a section that was deleted, does not restrict anything.

Hidden activities count too. Students cannot complete an activity they cannot see, so hiding a completion-tracked activity keeps an "all activities" restriction from being met: remove its completion tracking, or require at least a number of activities, instead.

Students who do not meet the restriction see how many activities count so far, for example "Section 1 completed. (1 of 3 so far)". When they mark an activity of the section as done, the course page reloads, so that what it unlocks shows up.

If you want to learn more about using availability plugins in Moodle, please see https://docs.moodle.org/en/Restrict_access.


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin will be published and regularly updated in the Moodle plugins repository:
https://moodle.org/plugins/availability_sectioncompleted

The latest development version can be found on Github:
https://github.com/bdecentgmbh/moodle-availability_sectioncompleted


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/bdecentgmbh/moodle-availability_sectioncompleted/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Please issue feature proposals on Github:
https://github.com/bdecentgmbh/moodle-availability_sectioncompleted/issues

Please create pull requests on Github:
https://github.com/bdecentgmbh/moodle-availability_sectioncompleted/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

This plugin is maintained for the two most recent major releases of Moodle as well as the most recent LTS release of Moodle. Changes between releases are listed in [CHANGES.md](CHANGES.md).

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Releasing this plugin
---------------------

Releases are published from GitHub Actions (.github/workflows/moodle-release.yml) with Moodle HQ's release workflow:

1. Changes are merged into dev, then dev into main with a merge commit. version.php and the CHANGES.md section "## <release> (<version>)" must be updated.
2. Push a tag "v" followed by $plugin->release on the merge commit, for example v1.2. The workflow checks the tag, waits for Moodle Plugin CI on that commit, publishes the version with the CHANGES.md section as release notes to the Moodle Marketplace and creates the GitHub Release with the ZIP.
3. Actions › Release to Moodle Marketplace › Run workflow repeats a release or, with "Only check", runs the checks without publishing.

The repository secret MOODLE_MARKETPLACE_TOKEN holds an API token created by a maintainer of the plugin at https://marketplace.moodle.com/account/security.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.


Copyright
---------

bdecent gmbh
bdecent.de
