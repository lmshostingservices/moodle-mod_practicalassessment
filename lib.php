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
 * AI Practical Assessment - Library functions.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function practicalassessment_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

function practicalassessment_add_instance($data, ?object $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    $context = [
        'industry' => $data->industry ?? '',
        'state' => $data->state ?? 'WA',
        'country' => $data->country ?? 'Australia',
        'jobrole' => $data->jobrole ?? '',
        'aqflevel' => $data->aqflevel ?? ''
    ];

    if (empty($data->context_json)) {
        $data->context_json = json_encode($context);
    }

    $data->id = $DB->insert_record('practicalassessment', $data);

    // Auto-generate assessment content if enabled and unit code is provided
    if (!empty($data->autogenerate) && !empty($data->unitcode)) {
        require_once(__DIR__ . '/classes/tga/training_component.php');
        require_once(__DIR__ . '/classes/generator.php');

        $tga = new \mod_practicalassessment\tga\training_component();
        $unit = $tga->get_unit($data->unitcode);

        if ($unit) {
            $unit['occasions'] = $data->occasions ?? 1;
            $manifest = \mod_practicalassessment\generator::generate_from_unit($unit, $context);

            $update = new \stdClass();
            $update->id = $data->id;
            $update->unitcode = $unit['code'];
            $update->unitname = $unit['title'];
            $update->scenario_text = $manifest['scenario'];
            $update->skills_json = json_encode($manifest['skillsChecklist']);
            $update->forms_json = json_encode($manifest['workplaceForms']);
            $update->mapping_json = json_encode($manifest['mappingMatrix']);
            $update->timemodified = time();

            $DB->update_record('practicalassessment', $update);
        }
    }

    practicalassessment_grade_item_update($data);

    return $data->id;
}

function practicalassessment_update_instance($data, ?object $mform = null) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    if (isset($data->industry) || isset($data->state)) {
        $data->context_json = json_encode([
            'industry' => $data->industry ?? '',
            'state' => $data->state ?? 'WA',
            'country' => $data->country ?? 'Australia',
            'jobrole' => $data->jobrole ?? '',
            'aqflevel' => $data->aqflevel ?? ''
        ]);
    }

    $DB->update_record('practicalassessment', $data);

    practicalassessment_grade_item_update($data);

    return true;
}

function practicalassessment_delete_instance($id) {
    global $DB;

    if (!$assessment = $DB->get_record('practicalassessment', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('practicalassessment_submission', ['practicalassessmentid' => $id]);
    $DB->delete_records('practicalassessment', ['id' => $id]);

    practicalassessment_grade_item_delete($assessment);

    return true;
}

function practicalassessment_grade_item_update($instance, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = [
        'itemname' => clean_param($instance->name, PARAM_TEXT),
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => 100,
        'grademin' => 0
    ];

    if ($grades === 'reset') {
        $item['reset'] = true;
        $grades = null;
    }

    return grade_update(
        'mod/practicalassessment',
        $instance->course,
        'mod',
        'practicalassessment',
        $instance->id,
        0,
        $grades,
        $item
    );
}

function practicalassessment_grade_item_delete($instance) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        'mod/practicalassessment',
        $instance->course,
        'mod',
        'practicalassessment',
        $instance->id,
        0,
        null,
        ['deleted' => 1]
    );
}

function practicalassessment_update_grades($instance, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($userid) {
        $submission = $DB->get_record('practicalassessment_submission', [
            'practicalassessmentid' => $instance->id,
            'userid' => $userid
        ]);

        if ($submission && $submission->grade !== null) {
            $grade = new stdClass();
            $grade->userid = $userid;
            $grade->rawgrade = $submission->grade;
            practicalassessment_grade_item_update($instance, $grade);
        } else if ($nullifnone) {
            $grade = new stdClass();
            $grade->userid = $userid;
            $grade->rawgrade = null;
            practicalassessment_grade_item_update($instance, $grade);
        }
    } else {
        practicalassessment_grade_item_update($instance);
    }
}

function practicalassessment_extend_navigation(navigation_node $navnode, $course, $module, $cm) {
}

function practicalassessment_extend_settings_navigation($settings, $navref) {
}

function mod_practicalassessment_get_fontawesome_icon_map() {
    return [
        'mod_practicalassessment:icon' => 'fa-clipboard-check'
    ];
}
