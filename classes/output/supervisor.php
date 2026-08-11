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
 * AI Practical Assessment - Supervisor verification renderer.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\output;

defined('MOODLE_INTERNAL') || die();

use html_writer;

class supervisor {
    public static function render(array $manifest): string {
        if (empty($manifest['supervisorEvidence']['required'])) {
            return '';
        }

        $out = html_writer::tag(
            'p',
            $manifest['supervisorEvidence']['declarationText']
        );

        foreach ($manifest['supervisorEvidence']['verificationFields'] as $field) {
            $out .= self::render_field($field);
        }

        $out .= html_writer::tag(
            'button',
            get_string('approve', 'practicalassessment'),
            ['class' => 'btn btn-success', 'type' => 'submit', 'name' => 'decision', 'value' => 'approved']
        );

        return html_writer::div($out, 'pa-supervisor');
    }

    private static function render_field(array $field): string {
        $name = 'supervisor_' . strtolower(str_replace(' ', '_', $field['label']));

        switch ($field['type']) {
            case 'signature':
                return html_writer::div(
                    html_writer::tag('label', $field['label']) .
                    html_writer::tag('canvas', '', [
                        'class' => 'pa-signature',
                        'width' => 400,
                        'height' => 150,
                        'id' => 'signature-canvas'
                    ]) .
                    html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'signature',
                        'id' => 'signature-data'
                    ]),
                    'pa-field pa-signature-wrap'
                );

            case 'checkbox':
                return html_writer::div(
                    html_writer::checkbox($name, 1, false) .
                    ' ' . $field['label'],
                    'pa-field'
                );

            default:
                return html_writer::div(
                    html_writer::tag('label', $field['label']) .
                    html_writer::empty_tag('input', [
                        'type' => 'text',
                        'name' => $name,
                        'placeholder' => $field['label']
                    ]),
                    'pa-field'
                );
        }
    }
}
