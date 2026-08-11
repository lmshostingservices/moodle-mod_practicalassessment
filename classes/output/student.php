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
 * AI Practical Assessment - Student view renderer.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\output;

defined('MOODLE_INTERNAL') || die();

use html_writer;

class student {
    public static function render(array $manifest, $submission = null, $cmid = 0): string {
        $out = '';

        if (!empty($manifest['scenario'])) {
            $out .= html_writer::div(
                html_writer::tag('h3', get_string('scenario', 'practicalassessment')) .
                format_text($manifest['scenario']),
                'pa-card pa-scenario'
            );
        }

        $completedSkills = [];
        if ($submission && !empty($submission->skills_completed)) {
            $completedSkills = json_decode($submission->skills_completed, true) ?? [];
        }

        if (!empty($manifest['skillsChecklist'])) {
            $out .= html_writer::start_div('pa-card');
            $out .= html_writer::tag('h3', get_string('skillschecklist', 'practicalassessment'));
            $out .= self::render_skills($manifest['skillsChecklist'], $completedSkills);
            $out .= html_writer::end_div();
        }

        $formsData = [];
        if ($submission && !empty($submission->forms_data)) {
            $formsData = json_decode($submission->forms_data, true) ?? [];
        }

        if (!empty($manifest['workplaceForms'])) {
            $out .= html_writer::start_div('pa-card');
            $out .= html_writer::tag('h3', get_string('workplaceforms', 'practicalassessment'));
            foreach ($manifest['workplaceForms'] as $form) {
                $out .= self::render_form($form, $formsData[$form['id']] ?? []);
            }
            $out .= html_writer::end_div();
        }

        if (!empty($manifest['supervisorEvidence']['required'])) {
            $out .= html_writer::start_div('pa-card');
            $out .= html_writer::tag('h3', get_string('supervisordetails', 'practicalassessment'));
            $out .= self::render_supervisor_fields($submission);
            $out .= html_writer::end_div();
        }

        $out .= html_writer::start_div('pa-card pa-declaration');
        $out .= html_writer::tag('label',
            html_writer::checkbox('declaration', 1, !empty($submission->declaration_agreed), '', ['id' => 'pa-declaration']) .
            ' ' . get_string('declarationtext', 'practicalassessment')
        );
        $out .= html_writer::end_div();

        $isSubmitted = $submission && in_array($submission->status, ['submitted', 'supervisor_verified', 'graded']);

        $out .= html_writer::start_div('pa-actions');
        if (!$isSubmitted) {
            $out .= html_writer::tag('button', get_string('savedraft', 'practicalassessment'), [
                'class' => 'btn btn-secondary',
                'id' => 'pa-save-draft',
                'data-cmid' => $cmid
            ]);
            $out .= html_writer::tag('button', get_string('submit', 'practicalassessment'), [
                'class' => 'btn btn-primary',
                'id' => 'pa-submit',
                'data-cmid' => $cmid
            ]);
        } else {
            $out .= html_writer::div(
                html_writer::span(get_string('status_' . $submission->status, 'practicalassessment'), 'pa-badge pa-badge-success'),
                'pa-submitted-status'
            );
        }
        $out .= html_writer::end_div();

        $out .= html_writer::div('', 'pa-autosave-indicator', ['id' => 'pa-autosave']);

        return $out;
    }

    private static function render_skills(array $skills, array $completed = []): string {
        $items = '';

        foreach ($skills as $skill) {
            $isChecked = in_array($skill['id'], $completed);
            $items .= html_writer::div(
                html_writer::tag('label',
                    html_writer::checkbox(
                        'skills[]',
                        $skill['id'],
                        $isChecked,
                        '',
                        ['data-skill-id' => $skill['id']]
                    ) .
                    ' ' . format_text($skill['description'])
                ),
                'pa-skill'
            );
        }

        return html_writer::div($items, 'pa-skills');
    }

    private static function render_form(array $form, array $savedData = []): string {
        $fields = '';

        foreach ($form['fields'] as $field) {
            $fields .= self::render_field($form['id'], $field, $savedData[$field['id']] ?? '');
        }

        return html_writer::div(
            html_writer::tag('h4', $form['title']) .
            html_writer::div(
                format_text($form['purpose']),
                'pa-form-purpose'
            ) .
            $fields,
            'pa-form',
            ['data-form-id' => $form['id']]
        );
    }

    private static function render_field(string $formid, array $field, $savedValue = ''): string {
        $name = "forms[$formid][{$field['id']}]";
        $labelClass = !empty($field['required']) ? 'pa-required' : '';

        $input = '';

        switch ($field['type']) {
            case 'textarea':
                $input = html_writer::tag('textarea', s($savedValue), [
                    'name' => $name,
                    'rows' => 4,
                    'data-field-id' => $field['id']
                ]);
                break;

            case 'date':
                $input = html_writer::empty_tag('input', [
                    'type' => 'date',
                    'name' => $name,
                    'value' => $savedValue,
                    'data-field-id' => $field['id']
                ]);
                break;

            case 'number':
                $input = html_writer::empty_tag('input', [
                    'type' => 'number',
                    'name' => $name,
                    'value' => $savedValue,
                    'data-field-id' => $field['id']
                ]);
                break;

            case 'checkbox':
                $input = html_writer::checkbox($name, 1, !empty($savedValue), '', [
                    'data-field-id' => $field['id']
                ]);
                break;

            case 'select':
                $options = $field['options'] ?? [];
                $input = html_writer::select($options, $name, $savedValue, null, [
                    'data-field-id' => $field['id']
                ]);
                break;

            case 'signature':
                $input = html_writer::div(
                    html_writer::tag('canvas', '', [
                        'class' => 'pa-signature',
                        'width' => 300,
                        'height' => 120,
                        'data-field-id' => $field['id']
                    ]) .
                    html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => $name,
                        'value' => $savedValue,
                        'class' => 'pa-signature-data'
                    ]) .
                    html_writer::tag('button', get_string('clearsignature', 'practicalassessment'), [
                        'type' => 'button',
                        'class' => 'btn btn-secondary btn-sm pa-clear-signature'
                    ]),
                    'pa-signature-wrap'
                );
                break;

            case 'matrix_5x5':
                $input = self::render_risk_matrix($name, $savedValue);
                break;

            default:
                $input = html_writer::empty_tag('input', [
                    'type' => 'text',
                    'name' => $name,
                    'value' => $savedValue,
                    'data-field-id' => $field['id']
                ]);
                break;
        }

        return html_writer::div(
            html_writer::tag('label', $field['label'], ['class' => $labelClass]) .
            $input,
            'pa-field'
        );
    }

    private static function render_risk_matrix(string $name, $savedValue = ''): string {
        $html = '<table class="pa-risk">';
        for ($r = 5; $r >= 1; $r--) {
            $html .= '<tr>';
            for ($c = 1; $c <= 5; $c++) {
                $value = "$r-$c";
                $active = ($value === $savedValue) ? ' active' : '';
                $html .= '<td data-value="' . $value . '" class="' . $active . '"></td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $name,
            'value' => $savedValue,
            'class' => 'pa-risk-input'
        ]);
        $html .= html_writer::div(
            html_writer::span(get_string('likelihood', 'practicalassessment'), '') .
            ' → ' .
            html_writer::span(get_string('consequence', 'practicalassessment'), ''),
            'pa-risk-labels'
        );
        return $html;
    }

    private static function render_supervisor_fields($submission = null): string {
        $out = '';

        $out .= html_writer::div(
            html_writer::tag('label', get_string('supervisorname', 'practicalassessment')) .
            html_writer::empty_tag('input', [
                'type' => 'text',
                'name' => 'supervisor_name',
                'id' => 'supervisor_name',
                'value' => $submission->supervisor_name ?? ''
            ]),
            'pa-field'
        );

        $out .= html_writer::div(
            html_writer::tag('label', get_string('supervisoremail', 'practicalassessment')) .
            html_writer::empty_tag('input', [
                'type' => 'email',
                'name' => 'supervisor_email',
                'id' => 'supervisor_email',
                'value' => $submission->supervisor_email ?? ''
            ]),
            'pa-field'
        );

        return $out;
    }
}
