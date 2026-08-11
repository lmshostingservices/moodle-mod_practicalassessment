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

class restore_practicalassessment_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('practicalassessment', '/activity/practicalassessment');

        if ($userinfo) {
            $paths[] = new restore_path_element('practicalassessment_submission', '/activity/practicalassessment/submissions/submission');
            $paths[] = new restore_path_element('practicalassessment_supervisor', '/activity/practicalassessment/submissions/submission/supervisors/supervisor');
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_practicalassessment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course       = $this->get_courseid();
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('practicalassessment', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_practicalassessment_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->practicalassessmentid = $this->get_new_parentid('practicalassessment');
        $data->userid  = $this->get_mappingid('user', $data->userid);
        $data->grader  = !empty($data->grader) ? $this->get_mappingid('user', $data->grader) : null;
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (!empty($data->timegraded)) {
            $data->timegraded = $this->apply_date_offset($data->timegraded);
        }

        $newitemid = $DB->insert_record('practicalassessment_submission', $data);
        $this->set_mapping('practicalassessment_submission', $oldid, $newitemid, true);
    }

    protected function process_practicalassessment_supervisor($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->submissionid = $this->get_new_parentid('practicalassessment_submission');
        // Reset token — supervisor must re-verify in the restored instance.
        $data->verification_token = '';

        $newitemid = $DB->insert_record('practicalassessment_supervisor', $data);
        $this->set_mapping('practicalassessment_supervisor', $oldid, $newitemid);
    }

    protected function after_execute() {
        $this->add_related_files('mod_practicalassessment', 'intro', null);
        $this->add_related_files('mod_practicalassessment', 'submission', 'practicalassessment_submission');
    }
}
