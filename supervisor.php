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
 * AI Practical Assessment - Supervisor verification page (no login required).
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

$token = required_param('token', PARAM_ALPHANUM);

$supervisor = $DB->get_record('practicalassessment_supervisor', ['verification_token' => $token]);

if (!$supervisor) {
    $PAGE->set_url('/mod/practicalassessment/supervisor.php', ['token' => $token]);
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('invalidtoken', 'practicalassessment'));
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('invalidtoken', 'practicalassessment'), 'error');
    echo $OUTPUT->footer();
    die();
}

$submission = $DB->get_record('practicalassessment_submission', ['id' => $supervisor->submissionid], '*', MUST_EXIST);
$assessment = $DB->get_record('practicalassessment', ['id' => $submission->practicalassessmentid], '*', MUST_EXIST);
$student = $DB->get_record('user', ['id' => $submission->userid], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('practicalassessment', $assessment->id, 0, false, MUST_EXIST);

$PAGE->set_url('/mod/practicalassessment/supervisor.php', ['token' => $token]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');
$PAGE->set_title(get_string('supervisorverification', 'practicalassessment'));

$PAGE->requires->css('/mod/practicalassessment/styles.css');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = required_param('decision', PARAM_TEXT);
    $comments = optional_param('comments', '', PARAM_RAW);
    $signature = optional_param('signature', '', PARAM_RAW);
    $skillsverified = optional_param_array('skills_verified', [], PARAM_INT);

    $supervisor->decision = $decision;
    $supervisor->comments = $comments;
    $supervisor->signature = $signature;
    $supervisor->skills_verified = json_encode($skillsverified);
    $supervisor->timeverified = time();

    $DB->update_record('practicalassessment_supervisor', $supervisor);

    if ($decision === 'approved') {
        $submission->status = 'supervisor_verified';
        $DB->update_record('practicalassessment_submission', $submission);
    }

    echo $OUTPUT->header();
    echo '<div class="pa-supervisor-thanks">';
    echo '<h2>' . get_string('thankyou', 'practicalassessment') . '</h2>';
    echo '<p>' . get_string('verificationcomplete', 'practicalassessment') . '</p>';
    echo '</div>';
    echo $OUTPUT->footer();
    die();
}

echo $OUTPUT->header();

$manifest = [];
if (!empty($assessment->skills_json)) {
    $manifest['skillsChecklist'] = json_decode(\mod_practicalassessment\manifest_storage::decompress($assessment->skills_json), true) ?? [];
}

$submissiondata = [
    'skills' => json_decode($submission->skills_completed, true) ?? [],
    'forms' => json_decode($submission->forms_data, true) ?? []
];

echo '<div class="pa-supervisor-container">';

echo '<h1>' . get_string('supervisorverification', 'practicalassessment') . '</h1>';
echo '<p class="pa-supervisor-intro">' . get_string('supervisorintro', 'practicalassessment', fullname($student)) . '</p>';

echo '<div class="pa-card">';
echo '<h3>' . get_string('assessmentdetails', 'practicalassessment') . '</h3>';
echo '<p><strong>' . get_string('unitcode', 'practicalassessment') . ':</strong> ' . s($assessment->unitcode) . '</p>';
echo '<p><strong>' . get_string('unitname', 'practicalassessment') . ':</strong> ' . s($assessment->unitname) . '</p>';
echo '</div>';

echo '<form method="post" id="supervisor-form">';

echo '<div class="pa-card">';
echo '<h3>' . get_string('skillsverification', 'practicalassessment') . '</h3>';
echo '<p>' . get_string('skillsverificationhelp', 'practicalassessment') . '</p>';

if (!empty($manifest['skillsChecklist'])) {
    foreach ($manifest['skillsChecklist'] as $skill) {
        $completed = in_array($skill['id'], $submissiondata['skills'] ?? []);
        $class = $completed ? 'pa-skill-completed' : 'pa-skill-incomplete';
        echo '<div class="pa-skill ' . $class . '">';
        echo '<label>';
        echo '<input type="checkbox" name="skills_verified[]" value="' . s($skill['id']) . '">';
        echo ' ' . format_text($skill['description']);
        echo '</label>';
        if ($completed) {
            echo ' <span class="pa-badge pa-badge-success">' . get_string('studentcompleted', 'practicalassessment') . '</span>';
        }
        echo '</div>';
    }
}
echo '</div>';

echo '<div class="pa-card">';
echo '<h3>' . get_string('supervisorcomments', 'practicalassessment') . '</h3>';
echo '<textarea name="comments" rows="4" class="pa-textarea"></textarea>';
echo '</div>';

echo '<div class="pa-card">';
echo '<h3>' . get_string('signature', 'practicalassessment') . '</h3>';
echo '<canvas id="signature-canvas" class="pa-signature" width="400" height="150"></canvas>';
echo '<input type="hidden" name="signature" id="signature-data">';
echo '<button type="button" id="clear-signature" class="btn btn-secondary">' . get_string('clearsignature', 'practicalassessment') . '</button>';
echo '</div>';

echo '<div class="pa-card">';
echo '<h3>' . get_string('decision', 'practicalassessment') . '</h3>';
echo '<div class="pa-decision-buttons">';
echo '<button type="submit" name="decision" value="approved" class="btn btn-success">' . get_string('approve', 'practicalassessment') . '</button>';
echo '<button type="submit" name="decision" value="resubmit" class="btn btn-warning">' . get_string('requestresubmission', 'practicalassessment') . '</button>';
echo '</div>';
echo '</div>';

echo '</form>';

echo '</div>';

$PAGE->requires->js_call_amd('mod_practicalassessment/supervisor', 'init');

echo $OUTPUT->footer();
