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
 * AI Practical Assessment - AJAX handler.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
require_login();

$action = required_param('action', PARAM_ALPHA);

header('Content-Type: application/json');

switch ($action) {

    case 'save_draft':
        $cmid = required_param('cmid', PARAM_INT);
        $data = required_param('data', PARAM_RAW);
        $skills = optional_param('skills', '', PARAM_RAW);

        $cm = get_coursemodule_from_id('practicalassessment', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/practicalassessment:submit', $context);

        $record = $DB->get_record('practicalassessment_submission', [
            'practicalassessmentid' => $cm->instance,
            'userid' => $USER->id
        ]);

        if (!$record) {
            $record = (object)[
                'practicalassessmentid' => $cm->instance,
                'userid' => $USER->id,
                'status' => 'draft',
                'timecreated' => time()
            ];
            $record->id = $DB->insert_record('practicalassessment_submission', $record);
        }

        $record->forms_data = $data;
        $record->skills_completed = $skills;
        $record->timemodified = time();

        $DB->update_record('practicalassessment_submission', $record);

        echo json_encode(['success' => true, 'id' => $record->id]);
        break;

    case 'load_draft':
        $cmid = required_param('cmid', PARAM_INT);

        $cm = get_coursemodule_from_id('practicalassessment', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/practicalassessment:submit', $context);

        $record = $DB->get_record('practicalassessment_submission', [
            'practicalassessmentid' => $cm->instance,
            'userid' => $USER->id
        ]);

        echo json_encode([
            'data' => $record ? $record->forms_data : null,
            'skills' => $record ? $record->skills_completed : null,
            'status' => $record ? $record->status : null
        ]);
        break;

    case 'submit':
        $cmid = required_param('cmid', PARAM_INT);
        $data = required_param('data', PARAM_RAW);
        $skills = required_param('skills', PARAM_RAW);
        $declaration = required_param('declaration', PARAM_BOOL);
        $supervisorname = optional_param('supervisor_name', '', PARAM_TEXT);
        $supervisoremail = optional_param('supervisor_email', '', PARAM_EMAIL);

        $cm = get_coursemodule_from_id('practicalassessment', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/practicalassessment:submit', $context);

        $record = $DB->get_record('practicalassessment_submission', [
            'practicalassessmentid' => $cm->instance,
            'userid' => $USER->id
        ]);

        if (!$record) {
            $record = (object)[
                'practicalassessmentid' => $cm->instance,
                'userid' => $USER->id,
                'timecreated' => time()
            ];
            $record->id = $DB->insert_record('practicalassessment_submission', $record);
        }

        $record->forms_data = $data;
        $record->skills_completed = $skills;
        $record->declaration_agreed = $declaration ? 1 : 0;
        $record->supervisor_name = $supervisorname;
        $record->supervisor_email = $supervisoremail;
        $record->status = 'submitted';
        $record->timemodified = time();

        $DB->update_record('practicalassessment_submission', $record);

        $assessment = $DB->get_record('practicalassessment', ['id' => $cm->instance]);
        if (!empty($assessment->requiresupervisor) && !empty($supervisoremail)) {
            require_once(__DIR__ . '/classes/supervisor_mailer.php');
            \mod_practicalassessment\supervisor_mailer::send_verification_request($record, $assessment);
        }

        echo json_encode(['success' => true, 'status' => 'submitted']);
        break;

    case 'save_grade':
        $submissionid = required_param('submissionid', PARAM_INT);
        $outcome = required_param('outcome', PARAM_TEXT);
        $score = required_param('score', PARAM_INT);
        $feedback = required_param('feedback', PARAM_RAW);
        $gradingdata = optional_param('grading_data', '', PARAM_RAW);

        $submission = $DB->get_record('practicalassessment_submission', ['id' => $submissionid], '*', MUST_EXIST);
        $assessment = $DB->get_record('practicalassessment', ['id' => $submission->practicalassessmentid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('practicalassessment', $assessment->id, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/practicalassessment:grade', $context);

        $submission->grade = $score;
        $submission->feedback = $feedback;
        $submission->grader = $USER->id;
        $submission->status = 'graded';
        $submission->timegraded = time();
        
        if (!empty($gradingdata)) {
            $submission->grading_data = $gradingdata;
        }

        $DB->update_record('practicalassessment_submission', $submission);

        $grade = new \stdClass();
        $grade->userid = $submission->userid;
        $grade->rawgrade = $score;
        require_once(__DIR__ . '/lib.php');
        practicalassessment_grade_item_update($assessment, $grade);

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
