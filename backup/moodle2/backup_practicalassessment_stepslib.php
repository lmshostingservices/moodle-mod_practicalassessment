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

class backup_practicalassessment_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $practicalassessment = new backup_nested_element('practicalassessment', ['id'], [
            'course', 'name', 'intro', 'introformat',
            'unitcode', 'unitname', 'industry', 'country', 'state',
            'jobrole', 'aqflevel', 'autogenerate',
            'context_json', 'scenario_text', 'scenario2_text',
            'skills_json', 'forms_json', 'mapping_json', 'checklist_json',
            'occasions', 'requiresupervisor', 'grade',
            'timecreated', 'timemodified',
        ]);

        $submissions = new backup_nested_element('submissions');
        $submission  = new backup_nested_element('submission', ['id'], [
            'practicalassessmentid', 'userid', 'status',
            'skills_completed', 'forms_data', 'evidence_files',
            'declaration_agreed', 'supervisor_email', 'supervisor_name',
            'grade', 'feedback', 'grading_data', 'grader',
            'timegraded', 'timecreated', 'timemodified',
        ]);

        $supervisors = new backup_nested_element('supervisors');
        $supervisor  = new backup_nested_element('supervisor', ['id'], [
            'submissionid', 'email', 'name', 'phone',
            'verification_token', 'skills_verified', 'comments', 'signature',
        ]);

        $practicalassessment->add_child($submissions);
        $submissions->add_child($submission);
        $submission->add_child($supervisors);
        $supervisors->add_child($supervisor);

        $practicalassessment->set_source_table('practicalassessment', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $submission->set_source_table('practicalassessment_submission', ['practicalassessmentid' => backup::VAR_PARENTID], 'id ASC');
            $supervisor->set_source_table('practicalassessment_supervisor', ['submissionid' => backup::VAR_PARENTID], 'id ASC');
            $submission->annotate_ids('user', 'userid');
            $submission->annotate_ids('user', 'grader');
        }

        return $this->prepare_activity_structure($practicalassessment);
    }
}
