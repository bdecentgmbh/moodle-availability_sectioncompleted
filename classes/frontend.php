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
 * Front-end class.
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_sectioncompleted;

/**
 * Front-end class.
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    /** @var array Cached init parameters */
    protected $cacheparams = [];

    /** @var string IDs of the course, activity and section the parameters were cached for */
    protected $cachekey = '';

    /**
     * Gets the string identifiers the form needs.
     *
     * @return array Array of required string identifiers
     */
    protected function get_javascript_strings() {
        return ['label_min', 'label_state', 'option_all', 'option_complete', 'option_fail', 'option_min', 'option_min1',
            'option_pass'];
    }

    /**
     * Gets the sections to choose from and the Moodle branch for the form.
     *
     * @param \stdClass $course Course object
     * @param \cm_info|null $cm Course-module currently being edited (null if none)
     * @param \section_info|null $section Section currently being edited (null if none)
     * @return array Array of parameters for the JavaScript function
     */
    protected function get_javascript_init_params($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        global $CFG;

        // Called twice per page (once from allow_add), so the sections are cached.
        $cachekey = $course->id . ',' . ($cm ? $cm->id : '') . ',' . ($section ? $section->id : '');
        if ($cachekey !== $this->cachekey) {
            $this->cachekey = $cachekey;
            $this->cacheparams = [$this->get_sections($course, $cm, $section), (int)$CFG->branch];
        }
        return $this->cacheparams;
    }

    /**
     * Offers the condition when the course tracks completion and has a section to choose.
     *
     * @param \stdClass $course Course object
     * @param \cm_info|null $cm Course-module currently being edited (null if none)
     * @param \section_info|null $section Section currently being edited (null if none)
     * @return bool True if the condition can be added
     */
    protected function allow_add($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        $completion = new \completion_info($course);
        if (!$completion->is_enabled()) {
            return false;
        }
        return !empty($this->get_javascript_init_params($course, $cm, $section)[0]);
    }

    /**
     * Gets the sections that have completion-tracked activities, in course order.
     *
     * Subsections follow their parent section. Sections the restricted item belongs to are left
     * out: their activities could not be completed while the item is locked.
     *
     * @param \stdClass $course Course object
     * @param \cm_info|null $cm Course-module currently being edited (null if none)
     * @param \section_info|null $section Section currently being edited (null if none)
     * @return \stdClass[] Objects with the section id, formatted name and number of tracked activities
     */
    protected function get_sections($course, ?\cm_info $cm, ?\section_info $section): array {
        global $PAGE;

        $format = course_get_format($course);
        if (!$format->uses_sections()) {
            return [];
        }
        $modinfo = get_fast_modinfo($course);
        $completion = new \completion_info($course);

        // Where the restricted item is: the edited section, the edited activity's section, or the
        // section a new activity is added to (course/modedit.php puts its number into the page URL).
        $current = $section;
        if (!$current && $cm) {
            $current = $cm->get_section_info();
        }
        if (!$current && $PAGE->has_set_url() && is_number($PAGE->url->get_param('section'))) {
            $current = $modinfo->get_section_info((int)$PAGE->url->get_param('section'));
        }

        $excluded = [];
        $top = null;
        if ($current) {
            $parent = condition::get_parent_section($modinfo, $current);
            $top = $parent ?? $current;
            $excluded[$current->id] = true;
            if ($parent) {
                $excluded[$parent->id] = true;
            }
            if ($section) {
                // A restricted section also locks its subsections.
                foreach ($this->get_subsections($modinfo, $section) as $subsection) {
                    $excluded[$subsection->id] = true;
                }
            }
        }
        if ($cm && ($delegated = condition::get_subsection($modinfo, $cm))) {
            $excluded[$delegated->id] = true;
        }

        $options = [];
        if ($top && $top->section > 0) {
            $previous = $modinfo->get_section_info($top->section - 1);
            $count = $previous ? count(condition::get_tracked_cms($modinfo, $previous, $completion)) : 0;
            if ($count && empty($excluded[$previous->id]) && $previous->uservisible) {
                $options[] = (object)[
                    'id' => (int)$previous->id,
                    'name' => get_string('previoussection', 'availability_sectioncompleted'),
                    'count' => $count,
                ];
            }
        }

        foreach ($modinfo->get_section_info_all() as $listed) {
            if (!empty($listed->component)) {
                continue;
            }
            foreach (array_merge([$listed], $this->get_subsections($modinfo, $listed)) as $candidate) {
                if (!empty($excluded[$candidate->id]) || !$candidate->uservisible) {
                    continue;
                }
                $count = count(condition::get_tracked_cms($modinfo, $candidate, $completion));
                if ($count) {
                    $options[] = (object)[
                        'id' => (int)$candidate->id,
                        'name' => condition::get_section_title($modinfo, $candidate),
                        'count' => $count,
                    ];
                }
            }
        }
        return $options;
    }

    /**
     * Gets the subsections of a section, in course order.
     *
     * @param \course_modinfo $modinfo Modinfo of the course
     * @param \section_info $section The section
     * @return \section_info[] The subsections
     */
    protected function get_subsections(\course_modinfo $modinfo, \section_info $section): array {
        $subsections = [];
        foreach ($modinfo->sections[$section->section] ?? [] as $cmid) {
            if (isset($modinfo->cms[$cmid]) && ($subsection = condition::get_subsection($modinfo, $modinfo->cms[$cmid]))) {
                $subsections[] = $subsection;
            }
        }
        return $subsections;
    }
}
