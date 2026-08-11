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
 * AI Practical Assessment - Base renderer.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use html_writer;

class renderer extends plugin_renderer_base {
    public function card(string $title, string $content): string {
        return html_writer::div(
            html_writer::tag('h3', $title, ['class' => 'pa-card-title']) .
            html_writer::div($content, 'pa-card-body'),
            'pa-card'
        );
    }

    public function badge(string $text, string $type = ''): string {
        $class = 'pa-badge';
        if ($type) {
            $class .= ' pa-badge-' . $type;
        }
        return html_writer::span($text, $class);
    }

    public function status_badge(string $status): string {
        $types = [
            'draft' => 'secondary',
            'submitted' => 'warning',
            'supervisor_verified' => 'success',
            'graded' => 'success'
        ];

        $type = $types[$status] ?? 'secondary';
        $label = get_string('status_' . $status, 'practicalassessment');

        return $this->badge($label, $type);
    }
}
