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
 * AI Practical Assessment - Privacy provider.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'practicalassessment_submission',
            [
                'userid' => 'privacy:metadata:practicalassessment_submission:userid',
                'forms_data' => 'privacy:metadata:practicalassessment_submission:forms_data',
                'skills_completed' => 'privacy:metadata:practicalassessment_submission:skills_completed',
            ],
            'privacy:metadata:practicalassessment_submission'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {practicalassessment} pa ON pa.id = cm.instance
            INNER JOIN {practicalassessment_submission} ps ON ps.practicalassessmentid = pa.id
                 WHERE ps.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'practicalassessment',
            'userid' => $userid
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $sql = "SELECT ps.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {practicalassessment} pa ON pa.id = cm.instance
                  JOIN {practicalassessment_submission} ps ON ps.practicalassessmentid = pa.id
                 WHERE cm.id = :cmid";

        $params = [
            'modname' => 'practicalassessment',
            'cmid' => $context->instanceid
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $cm = get_coursemodule_from_id('practicalassessment', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $submissions = $DB->get_records('practicalassessment_submission', [
                'practicalassessmentid' => $cm->instance,
                'userid' => $user->id
            ]);

            foreach ($submissions as $submission) {
                $data = [
                    'status' => $submission->status,
                    'skills_completed' => $submission->skills_completed,
                    'forms_data' => $submission->forms_data,
                    'grade' => $submission->grade,
                    'feedback' => $submission->feedback,
                    'timecreated' => transform::datetime($submission->timecreated)
                ];

                writer::with_context($context)->export_data(['submissions'], (object) $data);
            }
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('practicalassessment', $context->instanceid);
        if (!$cm) {
            return;
        }

        $submissions = $DB->get_records('practicalassessment_submission', [
            'practicalassessmentid' => $cm->instance
        ]);

        foreach ($submissions as $submission) {
            $DB->delete_records('practicalassessment_supervisor', ['submissionid' => $submission->id]);
        }

        $DB->delete_records('practicalassessment_submission', ['practicalassessmentid' => $cm->instance]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $cm = get_coursemodule_from_id('practicalassessment', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $submissions = $DB->get_records('practicalassessment_submission', [
                'practicalassessmentid' => $cm->instance,
                'userid' => $user->id
            ]);

            foreach ($submissions as $submission) {
                $DB->delete_records('practicalassessment_supervisor', ['submissionid' => $submission->id]);
            }

            $DB->delete_records('practicalassessment_submission', [
                'practicalassessmentid' => $cm->instance,
                'userid' => $user->id
            ]);
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('practicalassessment', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();

        foreach ($userids as $userid) {
            $submissions = $DB->get_records('practicalassessment_submission', [
                'practicalassessmentid' => $cm->instance,
                'userid' => $userid
            ]);

            foreach ($submissions as $submission) {
                $DB->delete_records('practicalassessment_supervisor', ['submissionid' => $submission->id]);
            }

            $DB->delete_records('practicalassessment_submission', [
                'practicalassessmentid' => $cm->instance,
                'userid' => $userid
            ]);
        }
    }
}
