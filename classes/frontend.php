<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_sectioncompleted;

defined('MOODLE_INTERNAL') || die();

/**
 * Availability role - Frontend form class
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {

    /**
     * Get the initial parameters needed for JavaScript.
     *
     * @param \stdClass          $course
     * @param \cm_info|null      $cm
     * @param \section_info|null $section
     *
     * @return array
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
         $jsarray = array();
        $context = \context_course::instance($course->id);

        $format = course_get_format($course->id);
        $course = $format->get_course(); // Needed to have numsections property available.
        if (!$format->uses_sections()) {
            return array();
        }
        if (($format instanceof format_digidagotabs) || ($format instanceof format_horizontaltabs)) {
            // Don't show the menu in a tab.
                return array();
            // Only show the block inside activities of courses.
        } else {
            $coursesections = $format->get_sections();
        }
        if (empty($coursesections)) {
            return array();
        }
        foreach ($coursesections as $section) {
            if (@$cm->section == $section->id) {
                continue;
            }
            if (!$section->uservisible) {
                continue;
            }

            if (!empty($section->name)) {
                $title = format_string(
                        $section->name,
                        true,
                        ['context' => $context]
                );
            } else {
                $title = $format->get_section_name($section);
            }
            $jsarray[] = (object)array(
                'id' => $section->id,
                'name' => $title
            );
        }
        return array($jsarray);
    }


    /**
     * Decides whether this plugin should be available in a given course. The plugin can do this depending on course or
     * system settings. Default returns true.
     *
     * @param \stdClass          $course
     * @param \cm_info|null      $cm
     * @param \section_info|null $section
     *
     * @return bool
     */
    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {
        return true;
    }
}
