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
use core_privacy\tests\provider_testcase;

/**
 * Unit tests for the sectioncompleted condition.
 *
 * @package   availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_sectioncompleted_privacy_testcase extends provider_testcase {

    /**
     * Test returning metadata.
     * @covers ::availability_sectioncompleted\privacy\provider
     */
    public function test_get_metadata() {
        $collection = new \core_privacy\local\metadata\collection('availability_sectioncompleted');
        $reason = \availability_sectioncompleted\privacy\provider::get_reason($collection);
        $this->assertEquals($reason, 'privacy:metadata');
        $this->assertStringContainsString('does not store', get_string($reason, 'availability_sectioncompleted'));
    }
}
