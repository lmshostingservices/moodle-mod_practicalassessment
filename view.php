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
 * AI Practical Assessment - View page.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('practicalassessment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$assessment = $DB->get_record('practicalassessment', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$event = \mod_practicalassessment\event\course_module_viewed::create([
    'objectid' => $assessment->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('practicalassessment', $assessment);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/practicalassessment/view.php', ['id' => $id]);
$PAGE->set_title(format_string($assessment->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->css('/mod/practicalassessment/styles.css');

$manifest = [];
if (!empty($assessment->skills_json)) {
    $manifest['skillsChecklist'] = json_decode($assessment->skills_json, true) ?? [];
}
if (!empty($assessment->forms_json)) {
    $manifest['workplaceForms'] = json_decode($assessment->forms_json, true) ?? [];
}
if (!empty($assessment->mapping_json)) {
    $manifest['mappingMatrix'] = json_decode($assessment->mapping_json, true) ?? [];
}
$manifest['scenario'] = $assessment->scenario_text ?? '';
$manifest['scenario2'] = $assessment->scenario2_text ?? '';
$manifest['unitCode'] = $assessment->unitcode ?? '';
$manifest['unitTitle'] = $assessment->unitname ?? '';
$manifest['occasions'] = $assessment->occasions ?? 1;
$manifest['supervisorEvidence'] = [
    'required' => !empty($assessment->requiresupervisor),
    'declarationText' => get_string('supervisordeclaration', 'practicalassessment'),
    'verificationFields' => [
        ['label' => get_string('supervisorname', 'practicalassessment'), 'type' => 'text'],
        ['label' => get_string('supervisorposition', 'practicalassessment'), 'type' => 'text'],
        ['label' => get_string('signature', 'practicalassessment'), 'type' => 'signature']
    ]
];

$submission = $DB->get_record('practicalassessment_submission', [
    'practicalassessmentid' => $assessment->id,
    'userid' => $USER->id
]);

echo $OUTPUT->header();

echo '<div class="pa-container" data-cmid="' . $cm->id . '">';

if (!empty($assessment->intro)) {
    echo $OUTPUT->box(format_module_intro('practicalassessment', $assessment, $cm->id), 'generalbox', 'intro');
}

if (has_capability('mod/practicalassessment:submit', $context)) {
    echo \mod_practicalassessment\output\student::render($manifest, $submission, $cm->id);
    
    $skillsCount = count($manifest['skillsChecklist'] ?? []);
    $completedCount = 0;
    if ($submission && !empty($submission->skills_completed)) {
        $completedSkills = json_decode($submission->skills_completed, true) ?? [];
        $completedCount = count($completedSkills);
    }
    $progressPercent = $skillsCount > 0 ? round(($completedCount / $skillsCount) * 100) : 0;
    
    echo '<div class="pa-completion-ring" id="pa-progress-ring" title="' . $progressPercent . '% complete">';
    echo '<svg viewBox="0 0 60 60">';
    echo '<circle class="pa-completion-ring-bg" cx="30" cy="30" r="26"></circle>';
    $circumference = 2 * 3.14159 * 26;
    $offset = $circumference - ($progressPercent / 100) * $circumference;
    $ringClass = $progressPercent === 100 ? 'pa-completion-ring-progress complete' : 'pa-completion-ring-progress';
    echo '<circle class="' . $ringClass . '" cx="30" cy="30" r="26" stroke-dasharray="' . $circumference . '" stroke-dashoffset="' . $offset . '"></circle>';
    echo '<text class="pa-completion-ring-text" x="30" y="28">' . $progressPercent . '%</text>';
    echo '<text class="pa-completion-ring-label" x="30" y="38">' . get_string('progress', 'practicalassessment') . '</text>';
    echo '</svg>';
    echo '<div class="pa-completion-ring-tooltip">' . $completedCount . ' of ' . $skillsCount . ' skills completed</div>';
    echo '</div>';
}

if (has_capability('mod/practicalassessment:grade', $context)) {
    echo '<hr class="pa-divider">';
    echo \mod_practicalassessment\output\grader::render_submissions_list($assessment->id, $cm->id);
}

echo '</div>';

// Load required strings for JavaScript
$PAGE->requires->string_for_js('draftsaved', 'mod_practicalassessment');
$PAGE->requires->string_for_js('autosaving', 'mod_practicalassessment');
$PAGE->requires->string_for_js('savedraft', 'mod_practicalassessment');
$PAGE->requires->string_for_js('submit', 'mod_practicalassessment');

$PAGE->requires->js_call_amd('mod_practicalassessment/player', 'init', [$cm->id]);

echo $OUTPUT->footer();
