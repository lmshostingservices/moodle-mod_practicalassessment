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
 * AI Practical Assessment - Version information.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_practicalassessment';
$plugin->version   = 2026072300;   // 2026-07-22, v3.2.19
$plugin->requires = 2022112800;
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '3.2.25'; // ADD-BACKUP-RESTORE: Added full Moodle backup/restore support (backup/moodle2/). Fixes stuck progress and missing activity data when a teacher copies or deletes the activity. Backs up instance settings; optionally backs up submissions and supervisor records when "Include user data" is selected. Supervisor verification tokens are reset on restore (students must re-verify). No DB schema changes. Savepoint 2026072200319. no-op savepoint marker for clean upgrade path. No DB schema changes.; // FIX: version number corrected to 13-digit YYYYMMDD00XXX format. No DB schema changes.
$plugin->supported = [401, 500];
