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
 * AI Practical Assessment - Export mapping matrix v3.2.0.
 * 
 * 8-Column ASQA Audit Format:
 * 1. Criterion (Unit Element/PC code)
 * 2. Task (Description of task)
 * 3. Form Field (Evidence source)
 * 4. Evidence Type (Observation/Document/Third Party)
 * 5. Assessment Method (Practical demonstration/Written response/etc)
 * 6. Date Assessed
 * 7. Assessor Name
 * 8. Outcome (S/NYS/Pending)
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$format = optional_param('format', 'csv', PARAM_ALPHA);
$submissionid = optional_param('submission', 0, PARAM_INT);

$cm = get_coursemodule_from_id('practicalassessment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$assessment = $DB->get_record('practicalassessment', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/practicalassessment:grade', $context);

$mapping = [];
if (!empty($assessment->mapping_json)) {
    $mapping = json_decode($assessment->mapping_json, true) ?? [];
}

$submission = null;
$grader = null;
$gradingData = [];

if ($submissionid) {
    $submission = $DB->get_record('practicalassessment_submission', ['id' => $submissionid]);
    if ($submission && !empty($submission->grader)) {
        $grader = $DB->get_record('user', ['id' => $submission->grader]);
    }
    if ($submission && property_exists($submission, 'grading_data') && !empty($submission->grading_data)) {
        $gradingData = json_decode($submission->grading_data, true) ?? [];
    }
}

$filename = clean_filename($assessment->name . '_mapping_' . date('Y-m-d'));

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, [
        get_string('criterion', 'practicalassessment'),
        get_string('task', 'practicalassessment'),
        get_string('formfield', 'practicalassessment'),
        get_string('evidencetype', 'practicalassessment'),
        get_string('assessmentmethod', 'practicalassessment'),
        get_string('dateassessed', 'practicalassessment'),
        get_string('assessor', 'practicalassessment'),
        get_string('outcome', 'practicalassessment')
    ]);

    foreach ($mapping as $row) {
        $criterionCode = $row['criterion'] ?? '';
        $task = $row['task'] ?? $row['evidence'] ?? '';
        $formField = $row['formField'] ?? $row['evidence'] ?? '';
        $evidenceType = $row['evidenceType'] ?? $row['source'] ?? 'Document';
        $assessmentMethod = $row['assessmentMethod'] ?? get_assessment_method($evidenceType);
        
        $dateAssessed = '';
        $assessorName = '';
        $outcome = 'Pending';
        
        if ($submission) {
            $dateAssessed = $submission->timegraded ? date('Y-m-d', $submission->timegraded) : '';
            $assessorName = $grader ? fullname($grader) : '';
            
            if ($submission->grade !== null) {
                $outcome = $submission->grade >= 50 ? 'S' : 'NYS';
            }
            
            $skillKey = extract_skill_key($formField);
            if (isset($gradingData['skills'][$skillKey])) {
                $outcome = $gradingData['skills'][$skillKey]['result'] ?? 'Pending';
            }
        }

        fputcsv($output, [
            $criterionCode,
            $task,
            $formField,
            $evidenceType,
            $assessmentMethod,
            $dateAssessed,
            $assessorName,
            $outcome
        ]);
    }

    fclose($output);
    die();
}

if ($format === 'pdf') {
    redirect(new moodle_url('/mod/practicalassessment/view.php', ['id' => $id]));
}

redirect(new moodle_url('/mod/practicalassessment/view.php', ['id' => $id]));

function get_assessment_method($evidenceType) {
    switch (strtolower($evidenceType)) {
        case 'observation':
            return 'Direct observation of practical demonstration';
        case 'document':
            return 'Review of completed documentation';
        case 'third party':
        case 'third-party':
            return 'Third party verification report';
        case 'product':
            return 'Assessment of work product';
        case 'written':
            return 'Written response/knowledge questions';
        default:
            return 'Practical demonstration';
    }
}

function extract_skill_key($text) {
    if (preg_match('/skill_(\d+)/', $text, $m)) {
        return 'skill_' . $m[1];
    }
    return sanitize_string($text);
}

function sanitize_string($text) {
    return preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(substr($text, 0, 50)));
}
