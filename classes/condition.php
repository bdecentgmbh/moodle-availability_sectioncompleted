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

use core_availability\info;
use core_availability\info_module;
use core_availability\info_section;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/completionlib.php');

/**
 * Availability role - Condition class
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var int ID of role that this condition requires */
     protected $sectionid;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        // Get section id.
        if (!property_exists($structure, 'id')) {
            $this->sectionid = 0;
        } else if (is_int($structure->id)) {
            $this->sectionid = $structure->id;
        } else {
            throw new \coding_exception('Invalid ->id for Section completion condition');
        }
    }

    /**
     * Save.
     *
     * @return object|\stdClass $result
     */
    public function save() {
        $result = (object)array('type' => 'sectioncompleted');
        if ($this->sectionid) {
            $result->id = $this->sectionid;
        } else {
            $result->activity = true;
        }
        return $result;
    }
    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $sectionid Required searction id (0 = any group)
     * @return stdClass Object representing condition
     */
    public static function get_json($sectionid = 0) {
        return (object)['type' => 'sectioncompleted', 'id' => (int)$sectionid];
    }

      /**
       * Adding the availability to restored course items.
       *
       * @param string       $restoreid
       * @param int          $courseid
       * @param \base_logger $logger
       * @param string       $name
       *
       * @return bool
       * @throws \dml_exception
       */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        return true;
    }

    /**
     * Check if the item is available with this restriction.
     *
     * @param bool                    $not
     * @param \core_availability\info $info
     * @param bool                    $grabthelot
     * @param int                     $userid
     *
     * @return bool
     * @throws \coding_exception
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $USER , $CFG , $DB;
        require_once("{$CFG->libdir}/completionlib.php");
        $context = \context_course::instance($info->get_course()->id);
        $modinfo = $info->get_modinfo();
        $completioninfo = new \completion_info($modinfo->get_course());
        $allow = true;
        if ($this->sectionid) {
            $modinfo = get_fast_modinfo($info->get_course());
            $section = $DB->get_record('course_sections', array('id' => $this->sectionid));
            if (isset($section)) {
                if (isset($modinfo->sections[@$section->section])) {
                    foreach ($modinfo->sections[$section->section] as $modnumber) {
                        $module = $modinfo->cms[$modnumber];
                        $completiondata = $completioninfo->get_data($module);
                        switch ($completiondata->completionstate) {
                            case COMPLETION_COMPLETE:
                            case COMPLETION_COMPLETE_FAIL:
                            case COMPLETION_COMPLETE_PASS:
                            break;
                            default:
                                $allow = false;
                        }
                    }
                }
            }
            if ($not) {
                $allow = !$allow;
            }
        }
        return $allow;
    }

    /**
     * Retrieve the description for the restriction.
     *
     * @param bool                    $full
     * @param bool                    $not
     * @param \core_availability\info $info
     *
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_description($full, $not, \core_availability\info $info) {
        if ($this->sectionid == '') {
            return '';
        }
        global $DB;
        $format = course_get_format($info->get_course()->id);
        $section = $DB->get_record('course_sections', array('id' => $this->sectionid));
        $title = @$format->get_section_name($section->section);
        if ($not) {
            return get_string('getdescriptionnot', 'availability_sectioncompleted', $title);
        }
            return get_string('getdescription', 'availability_sectioncompleted', $title);
    }
     /**
      * Checks whether this condition applies to user lists.
      *
      * @return bool
      * @throws \coding_exception
      */
    public function is_applied_to_user_lists() {
        // Group conditions are assumed to be 'permanent', so they affect the
        // display of user lists for activities.
        return true;
    }


    /**
     * Retrieve debugging string.
     *
     * @return string
     */
    public function get_debug_string() {
        return $this->sectionid ?? 'any';
    }
}
