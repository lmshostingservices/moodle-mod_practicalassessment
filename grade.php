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
 * AI Practical Assessment - Assessor grading page v3.2.0.
 * 
 * Features:
 * - Tabbed interface (Tab 1: Forms, Tab 2: Skills)
 * - S/NYS badges per criterion
 * - Sticky grade summary bar
 * - 1-3 occasion support
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$submissionid = required_param('submission', PARAM_INT);

$cm = get_coursemodule_from_id('practicalassessment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$assessment = $DB->get_record('practicalassessment', ['id' => $cm->instance], '*', MUST_EXIST);
$submission = $DB->get_record('practicalassessment_submission', ['id' => $submissionid], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/practicalassessment:grade', $context);

$student = $DB->get_record('user', ['id' => $submission->userid], '*', MUST_EXIST);

$PAGE->set_url('/mod/practicalassessment/grade.php', ['id' => $id, 'submission' => $submissionid]);
$PAGE->set_title(format_string($assessment->name) . ' - ' . get_string('grading', 'practicalassessment'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->css('/mod/practicalassessment/styles.css');

echo $OUTPUT->header();

echo '<div class="pa-container pa-grader-container">';

echo '<div class="pa-grader-header">';
echo '<h2>' . get_string('gradingfor', 'practicalassessment', fullname($student)) . '</h2>';
echo '<span class="pa-badge pa-badge-' . ($submission->status === 'graded' ? 'success' : 'warning') . '">';
echo get_string('status_' . $submission->status, 'practicalassessment');
echo '</span>';
echo '</div>';

$manifest = [];
if (!empty($assessment->skills_json)) {
    $manifest['skillsChecklist'] = json_decode($assessment->skills_json, true) ?? [];
}
if (!empty($assessment->forms_json)) {
    $manifest['workplaceForms'] = json_decode($assessment->forms_json, true) ?? [];
}

$submissiondata = [
    'skills' => json_decode($submission->skills_completed, true) ?? [],
    'forms' => json_decode($submission->forms_data, true) ?? [],
    'declaration' => $submission->declaration_agreed,
    'status' => $submission->status
];

$occasions = $assessment->occasions ?? 1;

echo '<div class="pa-card">';
echo '<h3>' . get_string('scenario', 'practicalassessment') . '</h3>';
echo '<div class="pa-scenario-text">' . format_text($assessment->scenario_text) . '</div>';
echo '</div>';

echo \mod_practicalassessment\output\grader::render($submission, $cm->id, $assessment);

echo \mod_practicalassessment\output\grader::render_forms_review($manifest['workplaceForms'] ?? [], $submissiondata['forms']);

echo \mod_practicalassessment\output\grader::render_skills_review(
    $manifest['skillsChecklist'] ?? [], 
    $submissiondata['skills'],
    $occasions
);

$supervisor = $DB->get_record('practicalassessment_supervisor', ['submissionid' => $submissionid]);
if ($supervisor) {
    echo '<div class="pa-card pa-supervisor-verified">';
    echo '<div class="pa-card-header">';
    echo '<h3>' . get_string('supervisorverification', 'practicalassessment') . '</h3>';
    echo '<span class="pa-badge pa-badge-success">' . get_string('verified', 'practicalassessment') . '</span>';
    echo '</div>';
    echo '<div class="pa-supervisor-details">';
    echo '<p><strong>' . get_string('verifiedby', 'practicalassessment') . ':</strong> ' . s($supervisor->name) . '</p>';
    echo '<p><strong>' . get_string('decision', 'practicalassessment') . ':</strong> ';
    echo '<span class="pa-badge pa-badge-' . ($supervisor->decision === 'approved' ? 'success' : 'warning') . '">';
    echo s(ucfirst($supervisor->decision));
    echo '</span></p>';
    if (!empty($supervisor->comments)) {
        echo '<p><strong>' . get_string('comments', 'practicalassessment') . ':</strong></p>';
        echo '<div class="pa-supervisor-comments">' . format_text($supervisor->comments) . '</div>';
    }
    if (!empty($supervisor->signature) && strpos($supervisor->signature, 'data:image') === 0) {
        echo '<p><strong>' . get_string('signature', 'practicalassessment') . ':</strong></p>';
        echo '<img src="' . $supervisor->signature . '" alt="Supervisor Signature" class="pa-signature-preview">';
    }
    echo '</div>';
    echo '</div>';
}

echo '</div>';

// Load required strings for JavaScript
$PAGE->requires->string_for_js('savegrade', 'mod_practicalassessment');
$PAGE->requires->string_for_js('feedback', 'mod_practicalassessment');
$PAGE->requires->string_for_js('nysfeedbackrequired', 'mod_practicalassessment');
$PAGE->requires->string_for_js('satisfactory', 'mod_practicalassessment');
$PAGE->requires->string_for_js('notyetsatisfactory', 'mod_practicalassessment');

$PAGE->requires->js_call_amd('mod_practicalassessment/grader', 'init', [$cm->id, $submissionid, $occasions]);

echo $OUTPUT->footer();
