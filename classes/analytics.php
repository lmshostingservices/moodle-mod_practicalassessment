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
 * AI Practical Assessment - Analytics.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment;

defined('MOODLE_INTERNAL') || die();

class analytics {
    public static function get_assessment_stats($assessmentid): array {
        global $DB;

        $submissions = $DB->get_records('practicalassessment_submission', [
            'practicalassessmentid' => $assessmentid
        ]);

        $stats = [
            'total' => count($submissions),
            'draft' => 0,
            'submitted' => 0,
            'supervisor_verified' => 0,
            'graded' => 0,
            'competent' => 0,
            'not_yet_competent' => 0,
            'average_grade' => 0
        ];

        $grades = [];

        foreach ($submissions as $submission) {
            if (isset($stats[$submission->status])) {
                $stats[$submission->status]++;
            }

            if ($submission->grade !== null) {
                $grades[] = $submission->grade;
                if ($submission->grade >= 50) {
                    $stats['competent']++;
                } else {
                    $stats['not_yet_competent']++;
                }
            }
        }

        if (!empty($grades)) {
            $stats['average_grade'] = round(array_sum($grades) / count($grades), 1);
        }

        return $stats;
    }

    public static function get_skills_completion_rate($assessmentid): array {
        global $DB;

        $assessment = $DB->get_record('practicalassessment', ['id' => $assessmentid]);
        $skills = json_decode($assessment->skills_json, true) ?? [];

        $submissions = $DB->get_records('practicalassessment_submission', [
            'practicalassessmentid' => $assessmentid
        ]);

        $skillStats = [];
        foreach ($skills as $skill) {
            $skillStats[$skill['id']] = [
                'description' => $skill['description'],
                'completed' => 0,
                'total' => count($submissions)
            ];
        }

        foreach ($submissions as $submission) {
            $completed = json_decode($submission->skills_completed, true) ?? [];
            foreach ($completed as $skillId) {
                if (isset($skillStats[$skillId])) {
                    $skillStats[$skillId]['completed']++;
                }
            }
        }

        return $skillStats;
    }
}
