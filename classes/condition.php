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
 * Condition class.
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_sectioncompleted;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Restriction by the completion of the activities in another section.
 *
 * The JSON structure is {type: 'sectioncompleted', id: section id, e: required state, m: minimum number}.
 * The state 'e' and the minimum 'm' are optional; conditions saved before 1.2 have neither and mean
 * "every completion-tracked activity of the section is complete, passed or failed".
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** An activity counts when it is complete, passed or failed. */
    const STATE_COMPLETE = 'complete';

    /** An activity counts when it is complete with a pass grade (COMPLETION_COMPLETE_PASS). */
    const STATE_PASS = 'pass';

    /** An activity counts when it is complete with a fail grade (COMPLETION_COMPLETE_FAIL). */
    const STATE_FAIL = 'fail';

    /** Highest minimum number of activities; the form offers one option per number. */
    const MAX_MIN = 1000;

    /** @var array Section ids that conditions in the course depend on, by course id */
    protected static $sectionsused = [];

    /** @var int ID of the section (course_sections.id) whose activities count, 0 if none. */
    protected $sectionid;

    /** @var string Completion state an activity needs to count, one of the STATE_ constants. */
    protected $state;

    /** @var int Number of activities that must count, 0 for all of them. */
    protected $min;

    /** @var int[]|null Counted and tracked activities of the last is_available() call, for the description. */
    protected $progress = null;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        if (!property_exists($structure, 'id')) {
            $this->sectionid = 0;
        } else if (is_int($structure->id)) {
            $this->sectionid = $structure->id;
        } else {
            throw new \coding_exception('Invalid ->id for section completion condition');
        }

        if (!property_exists($structure, 'e')) {
            $this->state = self::STATE_COMPLETE;
        } else if (in_array($structure->e, [self::STATE_COMPLETE, self::STATE_PASS, self::STATE_FAIL], true)) {
            $this->state = $structure->e;
        } else {
            throw new \coding_exception('Invalid ->e for section completion condition');
        }

        if (!property_exists($structure, 'm')) {
            $this->min = 0;
        } else if (is_int($structure->m) && $structure->m >= 0 && $structure->m <= self::MAX_MIN) {
            $this->min = $structure->m;
        } else {
            throw new \coding_exception('Invalid ->m for section completion condition');
        }
    }

    /**
     * Saves the condition to a data structure.
     *
     * @return \stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object)['type' => 'sectioncompleted', 'id' => $this->sectionid, 'e' => $this->state, 'm' => $this->min];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $sectionid ID of the section whose activities count
     * @param string $state Completion state an activity needs to count, one of the STATE_ constants
     * @param int $min Number of activities that must count, 0 for all of them
     * @return \stdClass Object representing condition
     */
    public static function get_json($sectionid, $state = self::STATE_COMPLETE, $min = 0) {
        return (object)['type' => 'sectioncompleted', 'id' => (int)$sectionid, 'e' => $state, 'm' => (int)$min];
    }

    /**
     * Points the condition to the restored section.
     *
     * When the section was not restored but belongs to the target course (an activity duplicated
     * or imported within the course), the condition keeps it. Otherwise the condition loses its
     * section, like core conditions on activities that were not restored.
     *
     * @param string $restoreid Restore identifier
     * @param int $courseid Target course id
     * @param \base_logger $logger Logger for any warnings
     * @param string $name Name of the restored item
     * @return bool True if the condition changed
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        global $DB;

        if (!$this->sectionid) {
            return false;
        }
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_section', $this->sectionid);
        if ($rec && $rec->newitemid) {
            $this->sectionid = (int)$rec->newitemid;
            return true;
        }
        if ($DB->record_exists('course_sections', ['id' => $this->sectionid, 'course' => $courseid])) {
            return false;
        }
        $this->sectionid = 0;
        $logger->process(
            'Restored item (' . $name . ') has availability condition on a section that was not restored',
            \backup::LOG_WARNING
        );
        return true;
    }

    /**
     * Determines whether the user meets the condition.
     *
     * Uses only the modinfo of the user being checked ($info->get_modinfo()): loading the
     * modinfo of the current user here rebuilds the course cache for every checked user (#14).
     *
     * @param bool $not Set true if we are inverting the condition
     * @param \core_availability\info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information required for all course-modules
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $this->progress = null;
        $modinfo = $info->get_modinfo();
        $completion = new \completion_info($modinfo->get_course());
        $section = $this->sectionid ? $modinfo->get_section_info_by_id($this->sectionid) : null;
        $tracked = $section ? self::get_tracked_cms($modinfo, $section, $completion) : [];
        if (!$tracked) {
            // A missing section, or one without completion-tracked activities, does not restrict anything.
            return true;
        }

        $count = 0;
        foreach ($this->get_states($completion, $tracked, $grabthelot, (int)$userid) as $state) {
            if ($this->counts($state)) {
                $count++;
            }
        }
        $this->progress = [$count, count($tracked)];

        // A minimum above the number of tracked activities means all of them.
        $required = $this->min ? min($this->min, count($tracked)) : count($tracked);
        $allow = $count >= $required;
        return $not ? !$allow : $allow;
    }

    /**
     * Gets the completion states of a user in activities.
     *
     * The current user's states come from the completion cache. Other users' states (cron tasks,
     * reports) are not cached, and completion_info::get_data() would also load grades and custom
     * rule states for every activity, so they are read in one query.
     *
     * @param \completion_info $completion Completion info of the course
     * @param \cm_info[] $cms The activities
     * @param bool $grabthelot Whether to load the current user's states for the whole course at once
     * @param int $userid User ID
     * @return int[] One of the COMPLETION_ constants for each activity
     */
    protected function get_states(\completion_info $completion, array $cms, bool $grabthelot, int $userid): array {
        global $DB, $USER;

        // A completion reset keeps the viewed record, for which core returns no state.
        if ($userid == $USER->id) {
            return array_map(function (\cm_info $cm) use ($completion, $grabthelot, $userid): int {
                return (int)($completion->get_data($cm, $grabthelot, $userid)->completionstate ?? COMPLETION_INCOMPLETE);
            }, $cms);
        }

        $cmids = array_map(fn(\cm_info $cm): int => (int)$cm->id, $cms);
        [$insql, $params] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
        $params['userid'] = $userid;
        $states = $DB->get_records_select_menu(
            'course_modules_completion',
            "userid = :userid AND coursemoduleid $insql",
            $params,
            '',
            'coursemoduleid, completionstate'
        );
        return array_map(fn(int $cmid): int => (int)($states[$cmid] ?? COMPLETION_INCOMPLETE), $cmids);
    }

    /**
     * Whether an activity with this completion state counts towards the condition.
     *
     * @param int $completionstate One of the COMPLETION_ constants
     * @return bool
     */
    protected function counts(int $completionstate): bool {
        switch ($this->state) {
            case self::STATE_PASS:
                return $completionstate === COMPLETION_COMPLETE_PASS;
            case self::STATE_FAIL:
                return $completionstate === COMPLETION_COMPLETE_FAIL;
            default:
                return in_array($completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL], true);
        }
    }

    /**
     * Obtains a string describing this restriction.
     *
     * The section name is a placeholder that core replaces when the text is displayed. For a
     * user who does not meet the condition, the text ends with how many activities count so far.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param \core_availability\info $info Item we're checking
     * @return string Information string (for admin) about all restrictions on this item
     */
    public function get_description($full, $not, \core_availability\info $info) {
        $section = $this->description_callback([$this->sectionid]);
        $key = 'getdescription' . ($not ? 'not' : '') . ($this->state === self::STATE_COMPLETE ? '' : '_' . $this->state);
        if ($this->min) {
            $description = get_string(
                $key . '_min',
                'availability_sectioncompleted',
                (object)['section' => $section, 'min' => $this->min]
            );
        } else {
            $description = get_string($key, 'availability_sectioncompleted', $section);
        }

        if (!$full && !$not && $this->progress && $this->progress[1]) {
            $description .= ' ' . get_string(
                'progress',
                'availability_sectioncompleted',
                (object)['count' => $this->progress[0], 'total' => $this->progress[1]]
            );
        }
        return $description;
    }

    /**
     * Gets the section name when the description is displayed.
     *
     * @param \course_modinfo $modinfo Modinfo
     * @param \context $context Context
     * @param string[] $params Parameters (just the section id)
     * @return string Section name
     */
    public static function get_description_callback_value(\course_modinfo $modinfo, \context $context, array $params): string {
        if (count($params) !== 1 || !is_number($params[0])) {
            return '<!-- Invalid section completion description callback -->';
        }
        $section = $params[0] ? $modinfo->get_section_info_by_id((int)$params[0]) : null;
        if (!$section) {
            return get_string('missing', 'availability_sectioncompleted');
        }
        return self::get_section_title($modinfo, $section);
    }

    /**
     * Obtains a representation of the options of this condition as a string, for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        return '#' . $this->sectionid . ' ' . $this->state . ($this->min ? ' >= ' . $this->min : '');
    }

    /**
     * Completion depends on the user's progress, so the condition does not filter user lists.
     *
     * @return bool False
     */
    public function is_applied_to_user_lists() {
        return false;
    }

    /**
     * Whether the completion of an activity affects the availability of other items.
     *
     * Core then reloads the course page when the user marks the activity complete, so that items
     * unlocked by it show up.
     *
     * @param \stdClass $course Moodle course object
     * @param int $cmid Course-module id
     * @return bool True if a section completion condition depends on the activity's section
     */
    public static function completion_value_used($course, $cmid): bool {
        if (!array_key_exists($course->id, self::$sectionsused)) {
            $modinfo = get_fast_modinfo($course);
            $infos = [];
            foreach ($modinfo->cms as $othercm) {
                if (!is_null($othercm->availability)) {
                    $infos[] = new \core_availability\info_module($othercm);
                }
            }
            foreach ($modinfo->get_section_info_all() as $section) {
                if (!is_null($section->availability)) {
                    $infos[] = new \core_availability\info_section($section);
                }
            }
            self::$sectionsused[$course->id] = [];
            foreach ($infos as $info) {
                foreach ($info->get_availability_tree()->get_all_children(self::class) as $condition) {
                    self::$sectionsused[$course->id][$condition->sectionid] = true;
                }
            }
        }

        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->cms[$cmid] ?? null;
        $section = $cm ? $modinfo->get_section_info_by_id($cm->section) : null;
        if (!$section) {
            return false;
        }
        $parent = self::get_parent_section($modinfo, $section);
        return isset(self::$sectionsused[$course->id][$section->id]) ||
            ($parent && isset(self::$sectionsused[$course->id][$parent->id]));
    }

    /**
     * Wipes the static cache of sections used in conditions, for unit tests.
     */
    public static function wipe_static_cache(): void {
        self::$sectionsused = [];
    }

    /**
     * Gets the completion-tracked activities that count for a section.
     *
     * These are the section's own activities and, unless the section is itself a subsection,
     * the activities of its subsections. Only modinfo data is used, so this is safe while the
     * availability of another user is checked.
     *
     * @param \course_modinfo $modinfo Modinfo of the course
     * @param \section_info $section The section
     * @param \completion_info $completion Completion info of the course
     * @return \cm_info[] The activities
     */
    public static function get_tracked_cms(
        \course_modinfo $modinfo,
        \section_info $section,
        \completion_info $completion
    ): array {
        $tracked = [];
        foreach (self::get_section_cms($modinfo, $section) as $cm) {
            if ($completion->is_enabled($cm)) {
                $tracked[] = $cm;
            }
            if (empty($section->component) && ($subsection = self::get_subsection($modinfo, $cm))) {
                foreach (self::get_section_cms($modinfo, $subsection) as $subcm) {
                    if ($completion->is_enabled($subcm)) {
                        $tracked[] = $subcm;
                    }
                }
            }
        }
        return $tracked;
    }

    /**
     * Gets the activities of a section that are not being deleted.
     *
     * @param \course_modinfo $modinfo Modinfo of the course
     * @param \section_info $section The section
     * @return \cm_info[] The activities in course order
     */
    protected static function get_section_cms(\course_modinfo $modinfo, \section_info $section): array {
        $cms = [];
        foreach ($modinfo->sections[$section->section] ?? [] as $cmid) {
            $cm = $modinfo->cms[$cmid] ?? null;
            if ($cm && !$cm->deletioninprogress) {
                $cms[] = $cm;
            }
        }
        return $cms;
    }

    /**
     * Gets the subsection an activity delegates, such as the section of a Subsection activity.
     *
     * @param \course_modinfo $modinfo Modinfo of the course
     * @param \cm_info $cm The activity
     * @return \section_info|null The delegated section, or null
     */
    public static function get_subsection(\course_modinfo $modinfo, \cm_info $cm): ?\section_info {
        return $modinfo->get_section_info_by_component('mod_' . $cm->modname, $cm->instance);
    }

    /**
     * Gets the section that contains a subsection.
     *
     * @param \course_modinfo $modinfo Modinfo of the course
     * @param \section_info $section The section
     * @return \section_info|null The parent section, or null when the section is not a subsection
     */
    public static function get_parent_section(\course_modinfo $modinfo, \section_info $section): ?\section_info {
        if (empty($section->component) || strpos($section->component, 'mod_') !== 0) {
            return null;
        }
        $cm = $modinfo->get_instances_of(substr($section->component, 4))[$section->itemid] ?? null;
        return $cm ? $modinfo->get_section_info($cm->sectionnum) : null;
    }

    /**
     * Gets the formatted name of a section, prefixed with its parent section for a subsection.
     *
     * @param \course_modinfo $modinfo Modinfo of the course
     * @param \section_info $section The section
     * @return string Name, safe to put into HTML
     */
    public static function get_section_title(\course_modinfo $modinfo, \section_info $section): string {
        $format = course_get_format($modinfo->get_course());
        $context = \context_course::instance($modinfo->get_course_id());
        $name = function (\section_info $section) use ($format, $context): string {
            if ((string)$section->name !== '') {
                return format_string($section->name, true, ['context' => $context]);
            }
            // Formats without a section name string return an empty name.
            return $format->get_section_name($section) ?: get_string('section') . ' ' . $section->section;
        };

        $parent = self::get_parent_section($modinfo, $section);
        if (!$parent) {
            return $name($section);
        }
        return get_string(
            'subsectiontitle',
            'availability_sectioncompleted',
            (object)['section' => $name($parent), 'subsection' => $name($section)]
        );
    }
}
