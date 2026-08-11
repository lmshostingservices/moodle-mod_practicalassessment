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
 * AI Practical Assessment - Grading helper.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment;

defined('MOODLE_INTERNAL') || die();

class grading {
    public static function calculate_auto_grade($submission, $assessment): float {
        $skillsCompleted = json_decode($submission->skills_completed, true) ?? [];
        $skillsJson = json_decode($assessment->skills_json, true) ?? [];

        if (empty($skillsJson)) {
            return 0;
        }

        $totalSkills = count($skillsJson);
        $completedCount = count($skillsCompleted);

        if ($totalSkills === 0) {
            return 0;
        }

        return round(($completedCount / $totalSkills) * 100, 2);
    }

    public static function is_supervisor_verified($submissionid): bool {
        global $DB;

        $supervisor = $DB->get_record('practicalassessment_supervisor', [
            'submissionid' => $submissionid
        ]);

        return $supervisor && $supervisor->decision === 'approved';
    }

    public static function get_completion_status($submission): string {
        if (empty($submission)) {
            return 'notstarted';
        }

        return $submission->status ?? 'draft';
    }

    public static function push_to_gradebook($assessment, $submission) {
        $grade = new \stdClass();
        $grade->userid = $submission->userid;
        $grade->rawgrade = $submission->grade;

        practicalassessment_grade_item_update($assessment, $grade);
    }
}
