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
 * mod_practicalassessment file.
 *
 * @package    mod_practicalassessment
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/practicalassessment/backup/moodle2/restore_practicalassessment_stepslib.php');

class restore_practicalassessment_activity_task extends restore_activity_task {
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new restore_practicalassessment_activity_structure_step('practicalassessment_structure', 'practicalassessment.xml'));
    }

    public static function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_decode_content('practicalassessment', ['intro'], 'practicalassessment');
        return $contents;
    }

    public static function define_decode_rules() {
        $rules = [];
        $rules[] = new restore_decode_rule('PRACTICALASSESSMENTVIEWBYID', '/mod/practicalassessment/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('PRACTICALASSESSMENTINDEX', '/mod/practicalassessment/index.php?id=$1', 'course');
        return $rules;
    }

    public static function define_restore_log_rules() {
        $rules = [];
        $rules[] = new restore_log_rule('practicalassessment', 'add', 'view.php?id={course_module}', '{practicalassessment}');
        $rules[] = new restore_log_rule('practicalassessment', 'update', 'view.php?id={course_module}', '{practicalassessment}');
        $rules[] = new restore_log_rule('practicalassessment', 'view', 'view.php?id={course_module}', '{practicalassessment}');
        return $rules;
    }

    public static function define_restore_log_rules_for_course() {
        $rules = [];
        $rules[] = new restore_log_rule('practicalassessment', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
