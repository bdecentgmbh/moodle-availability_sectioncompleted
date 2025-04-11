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
 * Behat sectioncompleted-related steps definitions.
 *
 * @package   availability_sectioncompleted
 * @copyright 2025, bdecent gmbh bdecent.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Course-related steps definitions.
 *
 * @package   availability_sectioncompleted
 * @copyright 2025, bdecent gmbh bdecent.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_sectioncompleted extends behat_base {

    /**
     * Set the section name.
     *
     * @Given /^I set the section "([^"]*)" name$/
     * @param string $sectionname Section name
     */
    public function i_set_the_section_name(string $sectionname) {
        global $CFG;

        if ($CFG->branch >= "404") {
            $this->execute('behat_forms::i_set_the_field_to', ['Section name', $sectionname]);
        } else {
            $this->execute('behat_forms::i_set_the_field_to', ['Custom', '1']);
            $this->execute('behat_forms::i_set_the_field_to', ['Section name', $sectionname]);
        }
    }

    /**
     * Student toggle the manual completion state of activity.
     *
     * @Given /^Toggle the manual completion state of the "([^"]*)" activity$/
     * @param string $activityname Activity name
     */
    public function toggle_manual_completion_state_of_activity($activityname) {
        global $CFG;

        if ($CFG->branch >= "311") {
            $this->execute("behat_completion::toggle_the_manual_completion_state", [$activityname]);
            $this->execute("behat_completion::manual_completion_button_displayed_as", [$activityname, "Done"]);
        } else {
            $this->execute('behat_general::i_click_on', ['Mark as complete', 'button']);
        }
    }
}
