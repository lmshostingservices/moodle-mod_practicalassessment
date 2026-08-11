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
 * AI Practical Assessment - Generate assessment external service.
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
use mod_practicalassessment\tga\training_component;
use mod_practicalassessment\generator;

class generate_assessment extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'unitcode' => new external_value(PARAM_TEXT, 'Unit code'),
            'context_json' => new external_value(PARAM_RAW, 'Context JSON')
        ]);
    }

    public static function execute(int $cmid, string $unitcode, string $context_json): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'unitcode' => $unitcode,
            'context_json' => $context_json
        ]);

        $cm = get_coursemodule_from_id('practicalassessment', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        self::validate_context($context);
        require_capability('mod/practicalassessment:addinstance', $context);

        // SESSION LOCK: Release before TGA API + AI generation (external HTTP calls).
        \core\session\manager::write_close();

        $tga = new training_component();
        $unit = $tga->get_unit($params['unitcode']);

        if (!$unit) {
            return [
                'success' => false,
                'message' => 'Unit not found'
            ];
        }

        $workplaceContext = json_decode($params['context_json'], true) ?? [];
        $manifest = generator::generate_from_unit($unit, $workplaceContext);

        $assessment = $DB->get_record('practicalassessment', ['id' => $cm->instance], '*', MUST_EXIST);
        $assessment->unitcode = $unit['code'];
        $assessment->unitname = $unit['title'];
        $assessment->scenario_text = $manifest['scenario'];
        $assessment->skills_json = \mod_practicalassessment\manifest_storage::compress(json_encode($manifest['skillsChecklist']));
        $assessment->forms_json = \mod_practicalassessment\manifest_storage::compress(json_encode($manifest['workplaceForms']));
        $assessment->mapping_json = \mod_practicalassessment\manifest_storage::compress(json_encode($manifest['mappingMatrix']));
        $assessment->occasions = $manifest['occasions'];
        $assessment->timemodified = time();

        $DB->update_record('practicalassessment', $assessment);

        return [
            'success' => true,
            'message' => 'Assessment generated successfully'
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Message')
        ]);
    }
}
