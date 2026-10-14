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
 * Tests for the condition.
 *
 * @package    availability_sectioncompleted
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_sectioncompleted;

use core_availability\mock_info;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
require_once($CFG->libdir . '/completionlib.php');

#[\PHPUnit\Framework\Attributes\CoversClass(condition::class)]
/**
 * Tests for the condition.
 *
 * @covers \availability_sectioncompleted\condition
 */
final class condition_test extends \advanced_testcase {
    /** @var \stdClass Course with completion and three sections */
    private \stdClass $course;

    /** @var \stdClass Student */
    private \stdClass $student;

    /** @var int[] Ids of the completion-tracked pages in section 1 */
    private array $pages = [];

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        condition::wipe_static_cache();
        set_config('enableavailability', 1);
        set_config('enablecompletion', 1);

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['format' => 'topics', 'numsections' => 3, 'enablecompletion' => 1]);
        $this->student = $generator->create_and_enrol($this->course, 'student');
        course_update_section($this->course, $this->section(1), ['name' => 'Topic 1']);
        for ($i = 1; $i <= 3; $i++) {
            $this->pages[] = (int)$generator->create_module('page', ['course' => $this->course->id, 'section' => 1,
                'completion' => COMPLETION_TRACKING_MANUAL])->cmid;
        }
    }

    /**
     * Gets a section of the test course.
     *
     * @param int $sectionnum Section number
     * @return \section_info
     */
    private function section(int $sectionnum): \section_info {
        return get_fast_modinfo($this->course)->get_section_info($sectionnum);
    }

    /**
     * Sets the completion state of an activity for the student.
     *
     * @param int $cmid Course-module id
     * @param int $state One of the COMPLETION_ constants
     */
    private function complete(int $cmid, int $state = COMPLETION_COMPLETE): void {
        global $DB;
        $DB->delete_records('course_modules_completion', ['coursemoduleid' => $cmid, 'userid' => $this->student->id]);
        $DB->insert_record('course_modules_completion', (object)['coursemoduleid' => $cmid,
            'userid' => $this->student->id, 'completionstate' => $state, 'timemodified' => time()]);
        \cache::make('core', 'completion')->purge();
    }

    /**
     * Checks a condition for the student.
     *
     * @param \stdClass $json Condition structure
     * @param bool $not Whether the condition is inverted
     * @return bool
     */
    private function available(\stdClass $json, bool $not = false): bool {
        $condition = new condition($json);
        return $condition->is_available($not, new mock_info($this->course, $this->student->id), true, $this->student->id);
    }

    public function test_constructor(): void {
        $condition = new condition((object)[]);
        $this->assertSame('{sectioncompleted:#0 complete}', (string)$condition);

        $condition = new condition((object)['id' => 12, 'e' => condition::STATE_PASS, 'm' => 2]);
        $this->assertSame('{sectioncompleted:#12 pass >= 2}', (string)$condition);

        $invalid = [['id' => '12'], ['id' => 12, 'e' => 'done'], ['id' => 12, 'm' => -1], ['id' => 12, 'm' => '2'],
            ['id' => 12, 'm' => condition::MAX_MIN + 1]];
        foreach ($invalid as $structure) {
            try {
                new condition((object)$structure);
                $this->fail('Expected a coding exception for ' . json_encode($structure));
            } catch (\coding_exception $e) {
                $this->assertStringContainsString('Invalid ->', $e->getMessage());
            }
        }
    }

    public function test_save(): void {
        $structure = (object)['id' => 12, 'e' => condition::STATE_FAIL, 'm' => 1];
        $condition = new condition($structure);
        $structure->type = 'sectioncompleted';
        $this->assertEquals($structure, $condition->save());

        // Conditions saved before 1.2 have no state and minimum.
        $condition = new condition((object)['type' => 'sectioncompleted', 'id' => 12]);
        $this->assertEquals(condition::get_json(12), $condition->save());
    }

    public function test_is_available_all_activities(): void {
        $json = condition::get_json($this->section(1)->id);
        $this->assertFalse($this->available($json));
        $this->assertTrue($this->available($json, true));

        $this->complete($this->pages[0]);
        $this->complete($this->pages[1], COMPLETION_COMPLETE_PASS);
        $this->assertFalse($this->available($json));

        $this->complete($this->pages[2], COMPLETION_COMPLETE_FAIL);
        $this->assertTrue($this->available($json));
        $this->assertFalse($this->available($json, true));
    }

    public function test_is_available_pass_and_fail(): void {
        $pass = condition::get_json($this->section(1)->id, condition::STATE_PASS);
        $fail = condition::get_json($this->section(1)->id, condition::STATE_FAIL, 1);

        // Completion without a grade is neither passed nor failed.
        foreach ($this->pages as $cmid) {
            $this->complete($cmid);
        }
        $this->assertFalse($this->available($pass));
        $this->assertFalse($this->available($fail));

        $this->complete($this->pages[0], COMPLETION_COMPLETE_FAIL);
        $this->complete($this->pages[1], COMPLETION_COMPLETE_PASS);
        $this->complete($this->pages[2], COMPLETION_COMPLETE_PASS);
        $this->assertFalse($this->available($pass));
        $this->assertTrue($this->available($fail));

        $this->complete($this->pages[0], COMPLETION_COMPLETE_PASS);
        $this->assertTrue($this->available($pass));
        $this->assertFalse($this->available($fail));
    }

    public function test_is_available_minimum(): void {
        $two = condition::get_json($this->section(1)->id, condition::STATE_COMPLETE, 2);
        $ten = condition::get_json($this->section(1)->id, condition::STATE_COMPLETE, 10);

        $this->complete($this->pages[0]);
        $this->assertFalse($this->available($two));
        $this->complete($this->pages[1]);
        $this->assertTrue($this->available($two));
        $this->assertFalse($this->available($two, true));

        // A minimum above the number of activities requires all of them.
        $this->assertFalse($this->available($ten));
        $this->complete($this->pages[2]);
        $this->assertTrue($this->available($ten));
    }

    /**
     * Resetting completion keeps the viewed record, so core returns a completion record without state.
     */
    public function test_is_available_viewed_without_completion_record(): void {
        global $DB;
        $DB->insert_record('course_modules_viewed', (object)['coursemoduleid' => $this->pages[0],
            'userid' => $this->student->id, 'timecreated' => time()]);
        $json = condition::get_json($this->section(1)->id, condition::STATE_COMPLETE, 1);

        $this->setUser($this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher'));
        $this->assertFalse($this->available($json));

        $this->setUser($this->student);
        $condition = new condition($json);
        $info = new mock_info($this->course, $this->student->id);
        $this->assertFalse($condition->is_available(false, $info, false, $this->student->id));
    }

    public function test_is_available_current_user(): void {
        // The current user's states come from the completion cache instead of a query.
        $this->setUser($this->student);
        $pass = condition::get_json($this->section(1)->id, condition::STATE_PASS, 2);
        $this->complete($this->pages[0], COMPLETION_COMPLETE_PASS);
        $this->assertFalse($this->available($pass));
        $this->complete($this->pages[1], COMPLETION_COMPLETE_PASS);
        $this->assertTrue($this->available($pass));
    }

    public function test_is_available_untracked_and_missing_sections(): void {
        // Section 2 has no completion-tracked activities: nothing to complete.
        $this->getDataGenerator()->create_module('page', ['course' => $this->course->id, 'section' => 2]);
        $this->assertTrue($this->available(condition::get_json($this->section(2)->id)));
        $this->assertTrue($this->available(condition::get_json($this->section(2)->id), true));

        // A condition without a section, or on a deleted one, does not restrict.
        $this->assertTrue($this->available(condition::get_json(0)));
        $this->assertTrue($this->available(condition::get_json(0), true));
        $sectionid = $this->section(3)->id;
        course_delete_section($this->course, 3);
        $this->assertTrue($this->available(condition::get_json($sectionid)));
        $this->assertTrue($this->available(condition::get_json($sectionid), true));
    }

    public function test_is_available_subsections(): void {
        \core\plugininfo\mod::enable_plugin('subsection', 1);
        $generator = $this->getDataGenerator();
        $subsection = $generator->create_module('subsection', ['course' => $this->course->id, 'section' => 1,
            'name' => 'Homework']);
        $delegated = get_fast_modinfo($this->course)->get_section_info_by_component('mod_subsection', $subsection->id);
        $homework = (int)$generator->create_module('page', ['course' => $this->course->id, 'section' => $delegated->section,
            'completion' => COMPLETION_TRACKING_MANUAL])->cmid;

        foreach ($this->pages as $cmid) {
            $this->complete($cmid);
        }
        // The activities of a subsection count for its parent section.
        $this->assertFalse($this->available(condition::get_json($this->section(1)->id)));
        $this->assertFalse($this->available(condition::get_json($delegated->id)));
        $this->complete($homework);
        $this->assertTrue($this->available(condition::get_json($this->section(1)->id)));
        $this->assertTrue($this->available(condition::get_json($delegated->id)));
    }

    /**
     * The condition uses the modinfo of the checked user, not the current user's (issues #10 and #14).
     */
    public function test_is_available_for_another_user(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $this->setUser($teacher);
        foreach ($this->pages as $cmid) {
            $this->complete($cmid);
        }

        $json = condition::get_json($this->section(1)->id);
        $modinfo = get_fast_modinfo($this->course, $this->student->id);
        $this->assertTrue($this->available($json));
        // Loading the current user's modinfo would have replaced the student's in the static cache.
        $this->assertSame($modinfo, get_fast_modinfo($this->course, $this->student->id));
    }

    public function test_get_description(): void {
        $info = new mock_info($this->course, $this->student->id);
        $describe = function (\stdClass $json, bool $full, bool $not, bool $check = false) use ($info): string {
            $condition = new condition($json);
            if ($check) {
                $condition->is_available($not, $info, true, $this->student->id);
            }
            return \core_availability\info::format_info($condition->get_description($full, $not, $info), $this->course);
        };
        $sectionid = $this->section(1)->id;

        $this->assertSame('Topic 1 completed.', $describe(condition::get_json($sectionid), true, false));
        $this->assertSame('Topic 1 not completed.', $describe(condition::get_json($sectionid), true, true));
        $this->assertSame(
            'Topic 1 completed and passed.',
            $describe(condition::get_json($sectionid, condition::STATE_PASS), true, false)
        );
        $this->assertSame(
            'Fewer than 2 of the activities in Topic 1 completed and failed.',
            $describe(condition::get_json($sectionid, condition::STATE_FAIL, 2), true, true)
        );
        $this->assertSame('(Missing section) completed.', $describe(condition::get_json(0), true, false));

        // The student is told how far they are.
        $this->complete($this->pages[0]);
        $this->assertSame(
            'At least 2 of the activities in Topic 1 completed. (1 of 3 so far)',
            $describe(condition::get_json($sectionid, condition::STATE_COMPLETE, 2), false, false, true)
        );
        $this->assertSame(
            'At least 2 of the activities in Topic 1 completed.',
            $describe(condition::get_json($sectionid, condition::STATE_COMPLETE, 2), true, false, true)
        );
    }

    public function test_get_description_callback_value(): void {
        $modinfo = get_fast_modinfo($this->course);
        $context = \context_course::instance($this->course->id);
        $this->assertSame('Topic 1', condition::get_description_callback_value($modinfo, $context, [$this->section(1)->id]));
        $this->assertSame('(Missing section)', condition::get_description_callback_value($modinfo, $context, ['0']));
        $this->assertStringStartsWith('<!--', condition::get_description_callback_value($modinfo, $context, ['x']));
        $this->assertStringStartsWith('<!--', condition::get_description_callback_value($modinfo, $context, [1, 2]));
    }

    public function test_completion_value_used(): void {
        \core\plugininfo\mod::enable_plugin('subsection', 1);
        $generator = $this->getDataGenerator();
        $subsection = $generator->create_module('subsection', ['course' => $this->course->id, 'section' => 1]);
        $delegated = get_fast_modinfo($this->course)->get_section_info_by_component('mod_subsection', $subsection->id);
        $homework = $generator->create_module('page', ['course' => $this->course->id, 'section' => $delegated->section,
            'completion' => COMPLETION_TRACKING_MANUAL]);
        $other = $generator->create_module('page', ['course' => $this->course->id, 'section' => 3,
            'completion' => COMPLETION_TRACKING_MANUAL]);
        $this->assertFalse(condition::completion_value_used($this->course, $this->pages[0]));

        // Section 2 needs section 1, which includes the subsection.
        $json = json_encode((object)['op' => '&', 'showc' => [true], 'c' => [condition::get_json($this->section(1)->id)]]);
        course_update_section($this->course, $this->section(2), ['availability' => $json]);
        condition::wipe_static_cache();
        $this->assertTrue(condition::completion_value_used($this->course, $this->pages[0]));
        $this->assertTrue(condition::completion_value_used($this->course, $homework->cmid));
        $this->assertFalse(condition::completion_value_used($this->course, $other->cmid));
    }

    public function test_get_section_title(): void {
        \core\plugininfo\mod::enable_plugin('subsection', 1);
        $subsection = $this->getDataGenerator()->create_module('subsection', ['course' => $this->course->id,
            'section' => 1, 'name' => 'Homework & extras']);
        $modinfo = get_fast_modinfo($this->course);
        $delegated = $modinfo->get_section_info_by_component('mod_subsection', $subsection->id);

        $this->assertSame('Topic 1', condition::get_section_title($modinfo, $this->section(1)));
        $this->assertSame('Topic 1 › Homework &amp; extras', condition::get_section_title($modinfo, $delegated));
        $this->assertEquals($this->section(1)->id, condition::get_parent_section($modinfo, $delegated)->id);
        $this->assertNull(condition::get_parent_section($modinfo, $this->section(1)));
    }

    public function test_update_after_restore(): void {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $this->setAdminUser();

        // A page in section 2 that needs section 1.
        $json = json_encode((object)['op' => '&', 'showc' => [true], 'c' => [condition::get_json($this->section(1)->id)]]);
        $page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id, 'section' => 2,
            'availability' => $json]);

        course_update_section($this->course, $this->section(3), ['availability' => $json]);

        // Duplicated within the course, it keeps the section.
        $duplicate = duplicate_module($this->course, get_fast_modinfo($this->course)->get_cm($page->cmid));
        $this->assertEquals($json, $DB->get_field('course_modules', 'availability', ['id' => $duplicate->id]));

        // Imported into another course, all three need that course's section 1. The target has no
        // sections yet: an import into existing sections keeps their settings.
        $target = $this->getDataGenerator()->create_course(['numsections' => 0, 'enablecompletion' => 1]);
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $this->course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();
        $rc = new \restore_controller(
            $backupid,
            $target->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id,
            \backup::TARGET_EXISTING_ADDING
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        $restored = $DB->get_fieldset_select(
            'course_modules',
            'availability',
            'course = ? AND availability IS NOT NULL',
            [$target->id]
        );
        $this->assertCount(2, $restored);
        $restored[] = get_fast_modinfo($target)->get_section_info(3)->availability;
        foreach ($restored as $restoredjson) {
            $this->assertEquals(get_fast_modinfo($target)->get_section_info(1)->id, json_decode($restoredjson)->c[0]->id);
        }
    }

    public function test_frontend_sections(): void {
        \core\plugininfo\mod::enable_plugin('subsection', 1);
        $generator = $this->getDataGenerator();
        $subsection = $generator->create_module('subsection', ['course' => $this->course->id, 'section' => 1,
            'name' => 'Homework']);
        $modinfo = get_fast_modinfo($this->course);
        $delegated = $modinfo->get_section_info_by_component('mod_subsection', $subsection->id);
        $homework = $generator->create_module('page', ['course' => $this->course->id, 'section' => $delegated->section,
            'completion' => COMPLETION_TRACKING_MANUAL]);
        $generator->create_module('page', ['course' => $this->course->id, 'section' => 2,
            'completion' => COMPLETION_TRACKING_MANUAL]);
        $this->setAdminUser();

        $sections = function (?\cm_info $cm, ?\section_info $section): array {
            $frontend = new frontend();
            $params = (new \ReflectionMethod($frontend, 'get_javascript_init_params'))->invoke(
                $frontend,
                $this->course,
                $cm,
                $section
            );
            return array_map(fn($option) => $option->name . ':' . $option->count, $params[0]);
        };
        $modinfo = get_fast_modinfo($this->course);
        $section2 = condition::get_section_title($modinfo, $modinfo->get_section_info(2));

        // Section 1 counts its subsection's page. Sections 0 and 3 have no tracked activities, and
        // a section cannot depend on itself.
        $this->assertSame(
            ['Previous section:1', 'Topic 1:4', 'Topic 1 › Homework:1', $section2 . ':1'],
            $sections(null, $modinfo->get_section_info(3))
        );
        // Section 1 cannot depend on its own subsection.
        $this->assertSame([$section2 . ':1'], $sections(null, $modinfo->get_section_info(1)));
        // An activity in section 1 can depend on the subsection, which does not contain it.
        $this->assertSame(['Topic 1 › Homework:1', $section2 . ':1'], $sections($modinfo->get_cm($this->pages[0]), null));
        // An activity in the subsection can depend on neither the subsection nor section 1.
        $this->assertSame([$section2 . ':1'], $sections($modinfo->get_cm($homework->cmid), null));

        // The condition is offered when the course tracks completion and there is a section to choose.
        $allowadd = new \ReflectionMethod(frontend::class, 'allow_add');
        $this->assertTrue($allowadd->invoke(new frontend(), $this->course, null, null));
        $nocompletion = $generator->create_course(['numsections' => 2, 'enablecompletion' => 0]);
        $this->assertFalse($allowadd->invoke(new frontend(), $nocompletion, null, null));
        $notracking = $generator->create_course(['numsections' => 2, 'enablecompletion' => 1]);
        $generator->create_module('page', ['course' => $notracking->id, 'section' => 1]);
        $this->assertFalse($allowadd->invoke(new frontend(), $notracking, null, null));
    }
}
