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
 * AI Practical Assessment - Save submission external service.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

class save_submission extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'forms_data' => new external_value(PARAM_RAW, 'Form data JSON'),
            'skills_completed' => new external_value(PARAM_RAW, 'Completed skills JSON'),
            'status' => new external_value(PARAM_TEXT, 'Submission status')
        ]);
    }

    public static function execute(int $cmid, string $forms_data, string $skills_completed, string $status): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'forms_data' => $forms_data,
            'skills_completed' => $skills_completed,
            'status' => $status
        ]);

        $cm = get_coursemodule_from_id('practicalassessment', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        self::validate_context($context);
        require_capability('mod/practicalassessment:submit', $context);

        $record = $DB->get_record('practicalassessment_submission', [
            'practicalassessmentid' => $cm->instance,
            'userid' => $USER->id
        ]);

        if (!$record) {
            $record = new \stdClass();
            $record->practicalassessmentid = $cm->instance;
            $record->userid = $USER->id;
            $record->timecreated = time();
            $record->id = $DB->insert_record('practicalassessment_submission', $record);
        }

        $record->forms_data = $params['forms_data'];
        $record->skills_completed = $params['skills_completed'];
        $record->status = $params['status'];
        $record->timemodified = time();

        $DB->update_record('practicalassessment_submission', $record);

        return [
            'success' => true,
            'submissionid' => $record->id
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'submissionid' => new external_value(PARAM_INT, 'Submission ID')
        ]);
    }
}
