moodle-availability_sectioncompleted
========================

Moodle availability plugin which lets users restrict resources, activities and sections based on sections being completed


Requirements
------------

This plugin requires Moodle 3.9+


Motivation for this plugin
--------------------------

This plugin was built to enable teachers to easily restrict activities or sections to students that have completed another, usually the previous section.

This is currently already possible by adding several restrictions; this plugin simplifies that for the teacher – and has also the nice and intended side effect that it looks better for the student.


Installation
------------

Install the plugin like any other plugin to folder
/availability/condition/sectioncompleted

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it is ready to use without the need for any configuration.

Teachers (and other users with editing rights) can add the "Section Complet" availability condition to activities / resources / sections in their courses. While adding the condition, they have to define the role which students have to have in course context to access the activity / resource / section.

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

This plugin is maintained for the two most recent major releases of Moodle as well as the most recent LTS release of Moodle. 

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.


Copyright
---------

bdecent gmbh
bdecent.de
