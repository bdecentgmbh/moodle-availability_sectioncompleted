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
 * Language strings.
 *
 * @package    availability_sectioncompleted
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['description'] = 'Require students to complete (or not complete) the activities of another section.';
$string['error_selectcmid'] = 'You must select a section for the completion condition.';
$string['getdescription'] = '{$a} completed.';
$string['getdescription_fail'] = '{$a} completed and failed.';
$string['getdescription_fail_min'] = 'At least {$a->min} of the activities in {$a->section} completed and failed.';
$string['getdescription_min'] = 'At least {$a->min} of the activities in {$a->section} completed.';
$string['getdescription_pass'] = '{$a} completed and passed.';
$string['getdescription_pass_min'] = 'At least {$a->min} of the activities in {$a->section} completed and passed.';
$string['getdescriptionnot'] = '{$a} not completed.';
$string['getdescriptionnot_fail'] = '{$a} not completed and failed.';
$string['getdescriptionnot_fail_min'] = 'Fewer than {$a->min} of the activities in {$a->section} completed and failed.';
$string['getdescriptionnot_min'] = 'Fewer than {$a->min} of the activities in {$a->section} completed.';
$string['getdescriptionnot_pass'] = '{$a} not completed and passed.';
$string['getdescriptionnot_pass_min'] = 'Fewer than {$a->min} of the activities in {$a->section} completed and passed.';
$string['label_min'] = 'Required number of activities';
$string['label_state'] = 'Required completion state';
$string['missing'] = '(Missing section)';
$string['option_all'] = 'all activities';
$string['option_complete'] = 'must be completed';
$string['option_fail'] = 'must be complete with fail grade';
$string['option_min'] = 'at least {$a} activities';
$string['option_min1'] = 'at least 1 activity';
$string['option_pass'] = 'must be complete with pass grade';
$string['pluginname'] = 'Restriction by other section completion';
$string['previoussection'] = 'Previous section';
$string['privacy:metadata'] = 'The Restriction by other section completion plugin does not store any personal data.';
$string['progress'] = '({$a->count} of {$a->total} so far)';
$string['subsectiontitle'] = '{$a->section} › {$a->subsection}';
$string['title'] = 'Section completion';
