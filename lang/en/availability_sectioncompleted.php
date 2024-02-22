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

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Restriction by other section completion';
$string['title'] = 'Section completion';
$string['getdescriptionnot'] = '{$a} not completed.';
$string['getdescription'] = '{$a} completed.';
$string['error_selectsectioncompleted'] = 'Select section';
$string['description'] = 'Require students to complete (or not complete) the specified section.';
$string['error_selectcmid'] = 'You must select a section for the completion condition.';
$string['missing'] = '(Missing section)';
$string['requires_incomplete'] = 'You have not completed <strong>{$a}</strong>';
$string['requires_complete'] = 'You have completed <strong>{$a}</strong>';
$string['privacy:metadata'] = 'The Restriction by other section completion plugin does not store any personal data.';
$string['previoussection'] = "Previous section";
