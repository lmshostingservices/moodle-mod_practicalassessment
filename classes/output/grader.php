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
 * AI Practical Assessment - Assessor grading renderer v3.2.0.
 * 
 * Features:
 * - Tabbed interface (Tab 1: Forms, Tab 2: Skills)
 * - S/NYS badges per criterion with mandatory NYS feedback
 * - Sticky grade summary bar with real-time progress
 * - Support for 1-3 occasions
 * - AI grading suggestions placeholder
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\output;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use moodle_url;

class grader {
    public static function render($submission, $cmid = 0, $assessment = null): string {
        $out = '';

        $occasions = $assessment->occasions ?? 1;
        if ($occasions < 1) $occasions = 1;
        if ($occasions > 3) $occasions = 3;

        $out .= self::render_sticky_summary_bar($submission, $occasions);

        $out .= self::render_tabs();

        $out .= self::render_grading_form($submission, $cmid, $occasions);

        return $out;
    }

    private static function render_sticky_summary_bar($submission, $occasions): string {
        $out = '';
        
        $out .= html_writer::start_div('pa-sticky-summary', ['id' => 'pa-summary-bar']);
        
        $out .= html_writer::start_div('pa-summary-stats');
        
        $out .= html_writer::div(
            html_writer::tag('span', '0', ['id' => 'pa-stat-s', 'class' => 'pa-stat-value pa-stat-s']) .
            html_writer::tag('span', ' S', ['class' => 'pa-stat-label']),
            'pa-stat-item'
        );
        
        $out .= html_writer::div(
            html_writer::tag('span', '0', ['id' => 'pa-stat-nys', 'class' => 'pa-stat-value pa-stat-nys']) .
            html_writer::tag('span', ' NYS', ['class' => 'pa-stat-label']),
            'pa-stat-item'
        );
        
        $out .= html_writer::div(
            html_writer::tag('span', '0', ['id' => 'pa-stat-pending', 'class' => 'pa-stat-value pa-stat-pending']) .
            html_writer::tag('span', ' Pending', ['class' => 'pa-stat-label']),
            'pa-stat-item'
        );
        
        $out .= html_writer::end_div();
        
        $out .= html_writer::start_div('pa-summary-progress');
        $out .= html_writer::div(
            html_writer::div('', 'pa-progress-bar', ['id' => 'pa-progress-fill', 'style' => 'width: 0%']),
            'pa-progress'
        );
        $out .= html_writer::tag('span', '0%', ['id' => 'pa-progress-percent', 'class' => 'pa-progress-text']);
        $out .= html_writer::end_div();
        
        $out .= html_writer::end_div();
        
        return $out;
    }

    private static function render_tabs(): string {
        $out = '';
        
        $out .= html_writer::start_div('pa-grader-tabs');
        
        $out .= html_writer::tag('button', 
            html_writer::tag('span', '', ['class' => 'pa-tab-icon pa-icon-forms']) .
            get_string('workplaceforms', 'practicalassessment'),
            [
                'class' => 'pa-tab pa-tab-active',
                'data-tab' => 'forms',
                'type' => 'button'
            ]
        );
        
        $out .= html_writer::tag('button',
            html_writer::tag('span', '', ['class' => 'pa-tab-icon pa-icon-skills']) .
            get_string('skillsassessment', 'practicalassessment'),
            [
                'class' => 'pa-tab',
                'data-tab' => 'skills',
                'type' => 'button'
            ]
        );
        
        $out .= html_writer::end_div();
        
        return $out;
    }

    private static function render_grading_form($submission, $cmid, $occasions): string {
        $out = '';

        $out .= html_writer::start_div('pa-card pa-grading-panel');
        $out .= html_writer::tag('h3', get_string('assessmentoutcome', 'practicalassessment'));

        $outcomes = [
            '' => get_string('select', 'form'),
            'C' => get_string('competent', 'practicalassessment'),
            'NYC' => get_string('notyetcompetent', 'practicalassessment')
        ];

        $out .= html_writer::div(
            html_writer::tag('label', get_string('assessmentoutcome', 'practicalassessment')) .
            html_writer::select($outcomes, 'outcome', '', null, ['id' => 'pa-outcome']),
            'pa-field'
        );

        $out .= html_writer::div(
            html_writer::tag('label', get_string('score', 'practicalassessment')) .
            html_writer::empty_tag('input', [
                'type' => 'number',
                'name' => 'score',
                'id' => 'pa-score',
                'min' => 0,
                'max' => 100,
                'value' => $submission->grade ?? ''
            ]),
            'pa-field'
        );

        $out .= html_writer::div(
            html_writer::tag('label', get_string('feedback', 'practicalassessment')) .
            html_writer::tag('textarea', s($submission->feedback ?? ''), [
                'name' => 'feedback',
                'id' => 'pa-feedback',
                'rows' => 6
            ]),
            'pa-field'
        );

        $out .= html_writer::div(
            html_writer::tag('span', '', ['id' => 'pa-nys-warning', 'class' => 'pa-nys-warning hidden']) .
            get_string('nysfeedbackrequired', 'practicalassessment'),
            'pa-nys-feedback-notice hidden',
            ['id' => 'pa-nys-notice']
        );

        $out .= html_writer::tag('button', get_string('savegrade', 'practicalassessment'), [
            'class' => 'pa-btn pa-btn-primary',
            'id' => 'pa-save-grade',
            'data-cmid' => $cmid,
            'data-submissionid' => $submission->id,
            'data-occasions' => $occasions
        ]);

        $out .= html_writer::end_div();

        return $out;
    }

    public static function render_submissions_list($assessmentid, $cmid): string {
        global $DB;

        $out = '';

        $out .= html_writer::start_div('pa-card');
        $out .= html_writer::tag('h3', get_string('submissions', 'practicalassessment'));

        $submissions = $DB->get_records('practicalassessment_submission', [
            'practicalassessmentid' => $assessmentid
        ], 'timecreated DESC');

        if (empty($submissions)) {
            $out .= html_writer::tag('p', get_string('nosubmissions', 'practicalassessment'));
        } else {
            $out .= html_writer::start_tag('table', ['class' => 'pa-submissions-table']);
            $out .= html_writer::start_tag('thead');
            $out .= html_writer::start_tag('tr');
            $out .= html_writer::tag('th', get_string('student', 'grades'));
            $out .= html_writer::tag('th', get_string('status', 'practicalassessment'));
            $out .= html_writer::tag('th', get_string('grade', 'grades'));
            $out .= html_writer::tag('th', get_string('actions', 'moodle'));
            $out .= html_writer::end_tag('tr');
            $out .= html_writer::end_tag('thead');
            $out .= html_writer::start_tag('tbody');

            foreach ($submissions as $submission) {
                $student = $DB->get_record('user', ['id' => $submission->userid]);

                $out .= html_writer::start_tag('tr');
                $out .= html_writer::tag('td', fullname($student));
                $out .= html_writer::tag('td',
                    html_writer::span(
                        get_string('status_' . $submission->status, 'practicalassessment'),
                        'pa-badge pa-badge-' . self::get_status_class($submission->status)
                    )
                );
                $out .= html_writer::tag('td', $submission->grade !== null ? $submission->grade : '-');

                $gradeUrl = new moodle_url('/mod/practicalassessment/grade.php', [
                    'id' => $cmid,
                    'submission' => $submission->id
                ]);
                $out .= html_writer::tag('td',
                    html_writer::link($gradeUrl, get_string('gradesubmission', 'practicalassessment'), [
                        'class' => 'pa-btn pa-btn-primary pa-btn-sm'
                    ])
                );

                $out .= html_writer::end_tag('tr');
            }

            $out .= html_writer::end_tag('tbody');
            $out .= html_writer::end_tag('table');
        }

        $out .= html_writer::end_div();

        return $out;
    }

    public static function render_skills_review(array $skills, array $completed, int $occasions = 1): string {
        $out = '';
        
        $out .= html_writer::start_div('pa-tab-content', ['id' => 'pa-tab-skills', 'style' => 'display: none;']);
        
        $out .= html_writer::start_tag('table', ['class' => 'pa-skills-table']);
        $out .= html_writer::start_tag('thead');
        $out .= html_writer::start_tag('tr');
        $out .= html_writer::tag('th', get_string('skill', 'practicalassessment'), ['style' => 'width: 50%;']);
        
        for ($i = 1; $i <= $occasions; $i++) {
            $out .= html_writer::tag('th', get_string('occasion', 'practicalassessment') . ' ' . $i, ['class' => 'pa-occasion-col']);
        }
        
        $out .= html_writer::tag('th', get_string('result', 'practicalassessment'), ['class' => 'pa-result-col']);
        $out .= html_writer::tag('th', get_string('feedback', 'practicalassessment'), ['class' => 'pa-feedback-col']);
        $out .= html_writer::end_tag('tr');
        $out .= html_writer::end_tag('thead');
        $out .= html_writer::start_tag('tbody');

        foreach ($skills as $idx => $skill) {
            $isCompleted = in_array($skill['id'], $completed);
            
            $out .= html_writer::start_tag('tr', ['class' => 'pa-skill-row', 'data-skill-id' => $skill['id']]);
            
            $out .= html_writer::tag('td', format_text($skill['description']), ['class' => 'pa-skill-desc']);
            
            for ($i = 1; $i <= $occasions; $i++) {
                $out .= html_writer::tag('td', 
                    html_writer::span($isCompleted ? 'Yes' : 'No', 'pa-badge pa-badge-' . ($isCompleted ? 'success' : 'warning')),
                    ['class' => 'pa-occasion-cell']
                );
            }
            
            $out .= html_writer::tag('td', self::render_snys_selector($skill['id']), ['class' => 'pa-result-cell']);
            
            $out .= html_writer::tag('td',
                html_writer::empty_tag('input', [
                    'type' => 'text',
                    'class' => 'pa-skill-feedback',
                    'data-skill-id' => $skill['id'],
                    'placeholder' => get_string('feedbackplaceholder', 'practicalassessment')
                ]),
                ['class' => 'pa-feedback-cell']
            );
            
            $out .= html_writer::end_tag('tr');
        }

        $out .= html_writer::end_tag('tbody');
        $out .= html_writer::end_tag('table');
        
        $out .= html_writer::end_div();

        return $out;
    }

    private static function render_snys_selector(string $skillId): string {
        $out = html_writer::start_div('pa-snys-selector', ['data-skill-id' => $skillId]);
        
        $out .= html_writer::tag('button', 'S', [
            'type' => 'button',
            'class' => 'pa-snys-btn pa-snys-s',
            'data-value' => 'S',
            'title' => get_string('satisfactory', 'practicalassessment')
        ]);
        
        $out .= html_writer::tag('button', 'NYS', [
            'type' => 'button',
            'class' => 'pa-snys-btn pa-snys-nys',
            'data-value' => 'NYS',
            'title' => get_string('notyetsatisfactory', 'practicalassessment')
        ]);
        
        $out .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'skill_result[' . $skillId . ']',
            'class' => 'pa-snys-value',
            'value' => ''
        ]);
        
        $out .= html_writer::end_div();
        
        return $out;
    }

    public static function render_forms_review(array $forms, array $formData): string {
        $out = '';
        
        $out .= html_writer::start_div('pa-tab-content pa-forms-review', ['id' => 'pa-tab-forms']);

        foreach ($forms as $form) {
            $out .= html_writer::start_div('pa-form-review-card');
            
            $out .= html_writer::start_div('pa-form-review-split');
            
            $out .= html_writer::start_div('pa-form-student-work');
            $out .= html_writer::tag('h4', $form['title']);

            $data = $formData[$form['id']] ?? [];

            foreach ($form['fields'] as $field) {
                $value = $data[$field['id']] ?? '';
                $displayValue = self::format_field_value($field['type'], $value);

                $out .= html_writer::div(
                    html_writer::tag('strong', $field['label'] . ': ') . $displayValue,
                    'pa-field-review'
                );
            }
            $out .= html_writer::end_div();
            
            $out .= html_writer::start_div('pa-form-grading-column');
            $out .= html_writer::tag('h5', get_string('gradingpanel', 'practicalassessment'));
            
            $out .= self::render_snys_selector('form_' . $form['id']);
            
            $out .= html_writer::tag('textarea', '', [
                'class' => 'pa-form-feedback',
                'data-form-id' => $form['id'],
                'placeholder' => get_string('feedbackplaceholder', 'practicalassessment'),
                'rows' => 3
            ]);
            
            $out .= html_writer::start_div('pa-ai-suggestion', ['data-form-id' => $form['id']]);
            $out .= html_writer::tag('button', 
                html_writer::tag('span', '', ['class' => 'pa-ai-icon']) . ' ' . get_string('getaisuggestion', 'practicalassessment'),
                [
                    'type' => 'button',
                    'class' => 'pa-btn pa-btn-secondary pa-btn-sm pa-ai-suggest-btn',
                    'data-form-id' => $form['id']
                ]
            );
            $out .= html_writer::div('', 'pa-ai-suggestion-text', ['id' => 'pa-ai-text-' . $form['id']]);
            $out .= html_writer::end_div();
            
            $out .= html_writer::end_div();
            
            $out .= html_writer::end_div();
            
            $out .= html_writer::end_div();
        }
        
        $out .= html_writer::end_div();

        return $out;
    }

    private static function get_status_class($status): string {
        switch ($status) {
            case 'graded':
            case 'supervisor_verified':
                return 'success';
            case 'submitted':
                return 'warning';
            default:
                return 'secondary';
        }
    }

    private static function format_field_value($type, $value): string {
        if (empty($value)) {
            return '<em class="pa-not-provided">' . get_string('notprovided', 'practicalassessment') . '</em>';
        }

        switch ($type) {
            case 'signature':
                if (strpos($value, 'data:image') === 0) {
                    return '<img src="' . $value . '" alt="Signature" class="pa-signature-preview">';
                }
                return s($value);

            case 'matrix_5x5':
                $parts = explode('-', $value);
                $likelihood = $parts[0] ?? '?';
                $consequence = $parts[1] ?? '?';
                $score = (int)$likelihood * (int)$consequence;
                $riskClass = $score >= 15 ? 'pa-risk-high' : ($score >= 8 ? 'pa-risk-medium' : 'pa-risk-low');
                return '<span class="pa-risk-badge ' . $riskClass . '">' . 
                       get_string('risklevel', 'practicalassessment') . ': ' . $score . 
                       ' (L' . $likelihood . ' x C' . $consequence . ')</span>';

            case 'checkbox':
                return $value ? 
                    '<span class="pa-badge pa-badge-success">Yes</span>' : 
                    '<span class="pa-badge pa-badge-warning">No</span>';

            default:
                return nl2br(s($value));
        }
    }
}
