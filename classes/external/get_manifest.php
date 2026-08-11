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
 * AI Practical Assessment - Get manifest external service.
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

class get_manifest extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID')
        ]);
    }

    public static function execute(int $cmid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['cmid' => $cmid]);

        $cm = get_coursemodule_from_id('practicalassessment', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        self::validate_context($context);
        require_capability('mod/practicalassessment:view', $context);

        $assessment = $DB->get_record('practicalassessment', ['id' => $cm->instance], '*', MUST_EXIST);

        $manifest = [
            'unitCode' => $assessment->unitcode ?? '',
            'unitTitle' => $assessment->unitname ?? '',
            'scenario' => $assessment->scenario_text ?? '',
            'skillsChecklist' => json_decode(\mod_practicalassessment\manifest_storage::decompress($assessment->skills_json), true) ?? [],
            'workplaceForms' => json_decode(\mod_practicalassessment\manifest_storage::decompress($assessment->forms_json), true) ?? [],
            'mappingMatrix' => json_decode(\mod_practicalassessment\manifest_storage::decompress($assessment->mapping_json), true) ?? []
        ];

        return [
            'success' => true,
            'manifest' => json_encode($manifest)
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'manifest' => new external_value(PARAM_RAW, 'Manifest JSON')
        ]);
    }
}
