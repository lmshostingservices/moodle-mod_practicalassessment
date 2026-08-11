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
 * AI Practical Assessment - List all instances in course.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_login($course);

$PAGE->set_url('/mod/practicalassessment/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

$assessments = get_all_instances_in_course('practicalassessment', $course);

if (!$assessments) {
    notice(get_string('noassessments', 'practicalassessment'), new moodle_url('/course/view.php', ['id' => $course->id]));
    die();
}

$table = new html_table();
$table->head = [
    get_string('name'),
    get_string('unitcode', 'practicalassessment'),
    get_string('status', 'practicalassessment')
];

foreach ($assessments as $assessment) {
    $link = html_writer::link(
        new moodle_url('/mod/practicalassessment/view.php', ['id' => $assessment->coursemodule]),
        format_string($assessment->name)
    );

    $submission = $DB->get_record('practicalassessment_submission', [
        'practicalassessmentid' => $assessment->id,
        'userid' => $USER->id
    ]);

    $status = get_string('notstarted', 'practicalassessment');
    if ($submission) {
        $status = get_string('status_' . $submission->status, 'practicalassessment');
    }

    $table->data[] = [
        $link,
        $assessment->unitcode ?? '-',
        $status
    ];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
