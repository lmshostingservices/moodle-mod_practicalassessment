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
 * AI Practical Assessment - Supervisor email notifications.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment;

defined('MOODLE_INTERNAL') || die();

class supervisor_mailer {
    public static function send_verification_request($submission, $assessment) {
        global $DB, $CFG;

        if (empty($submission->supervisor_email)) {
            return false;
        }

        $token = self::generate_token();

        $supervisor = new \stdClass();
        $supervisor->submissionid = $submission->id;
        $supervisor->email = $submission->supervisor_email;
        $supervisor->name = $submission->supervisor_name ?? '';
        $supervisor->verification_token = $token;
        $supervisor->timecreated = time();

        $existingRecord = $DB->get_record('practicalassessment_supervisor', [
            'submissionid' => $submission->id
        ]);

        if ($existingRecord) {
            $supervisor->id = $existingRecord->id;
            $DB->update_record('practicalassessment_supervisor', $supervisor);
        } else {
            $supervisor->id = $DB->insert_record('practicalassessment_supervisor', $supervisor);
        }

        $student = $DB->get_record('user', ['id' => $submission->userid]);
        $verificationUrl = $CFG->wwwroot . '/mod/practicalassessment/supervisor.php?token=' . $token;

        $subject = get_string('supervisoremailsubject', 'practicalassessment', [
            'studentname' => fullname($student),
            'assessment' => $assessment->name
        ]);

        $message = get_string('supervisoremailbody', 'practicalassessment', [
            'supervisorname' => $submission->supervisor_name,
            'studentname' => fullname($student),
            'assessment' => $assessment->name,
            'unit' => $assessment->unitcode . ' - ' . $assessment->unitname,
            'link' => $verificationUrl
        ]);

        $messageHtml = nl2br($message);

        $emailuser = new \stdClass();
        $emailuser->email = $submission->supervisor_email;
        $emailuser->firstname = $submission->supervisor_name;
        $emailuser->lastname = '';
        $emailuser->maildisplay = true;
        $emailuser->mailformat = 1;
        $emailuser->id = -99;

        $supportuser = \core_user::get_support_user();

        return email_to_user($emailuser, $supportuser, $subject, $message, $messageHtml);
    }

    private static function generate_token(): string {
        return bin2hex(random_bytes(32));
    }
}
