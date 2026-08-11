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
 * AI Practical Assessment - Database upgrade script.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_practicalassessment_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025121901) {
        upgrade_mod_savepoint(true, 2025121901, 'practicalassessment');
    }

    if ($oldversion < 2025122301) {
        $table = new xmldb_table('practicalassessment_submission');
        $field = new xmldb_field('grading_data', XMLDB_TYPE_TEXT, null, null, null, null, null, 'feedback');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025122301, 'practicalassessment');
    }

    // v3.2.8: Add missing form fields that were being saved to context_json but not as columns
    if ($oldversion < 2025122509) {
        $table = new xmldb_table('practicalassessment');

        // Add country field
        $field = new xmldb_field('country', XMLDB_TYPE_CHAR, '100', null, null, null, 'Australia', 'industry');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add state field
        $field = new xmldb_field('state', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'country');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add jobrole field
        $field = new xmldb_field('jobrole', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'state');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add aqflevel field
        $field = new xmldb_field('aqflevel', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'jobrole');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add autogenerate field
        $field = new xmldb_field('autogenerate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'aqflevel');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025122509, 'practicalassessment');
    }

    // v3.2.14: DBWRITE AUDIT FIX — All write operations in ajax.php now enforce
    //   require_sesskey() and capability checks before modifying the DB.
    //   No DB schema changes.
    if ($oldversion < 2026032501) {
        upgrade_mod_savepoint(true, 2026032501, 'practicalassessment');
    }

    // v3.2.16: FIX — version number corrected to 13-digit YYYYMMDD00XXX format. No DB schema changes.
    if ($oldversion < 2026041000316) {
        upgrade_mod_savepoint(true, 2026041000316, 'practicalassessment');
    }

    // v3.2.18: SAVEPOINT-BUMP — no-op marker for clean upgrade path. No DB schema changes.
    if ($oldversion < 2026060400318) {
        upgrade_mod_savepoint(true, 2026060400318, 'practicalassessment');
    }

    // v3.2.19: ADD-BACKUP-RESTORE — Added full Moodle backup/restore support.
    //   Fixes stuck progress when a teacher copies or deletes the activity.
    //   Supervisor verification tokens are reset on restore. No DB schema changes.
    if ($oldversion < 2026072200319) {
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php', 'backup/moodle2/backup_practicalassessment_activity_task.class.php', 'backup/moodle2/restore_practicalassessment_activity_task.class.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) {
                    opcache_invalidate($_full, true);
                }
            }
        } elseif (function_exists('opcache_reset')) {
            opcache_reset();
        }
        upgrade_mod_savepoint(true, 2026072200319, 'practicalassessment');
    }

    if ($oldversion < 2026072300229) {
        // FIX-API-DOMAIN: Updated all API endpoint URLs from lms-labs.com to lms-labs.com.
        // lms-labs.com has no DNS resolution from Moodle server side; lms-labs.com is the
        // correct working domain. All ajax.php, api_client, unlock_verifier, lib.php calls updated.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) {
                    opcache_invalidate($_full, true);
                }
            }
        } elseif (function_exists('opcache_reset')) {
            opcache_reset();
        }
        upgrade_mod_savepoint(true, 2026072300229, 'practicalassessment');
    }

    if ($oldversion < 2026072300230) {
        // FIX-API-DOMAIN: Reverted API endpoint to lms-labs.com (correct domain).
        // lms-labs.com was the original single-plugin domain; lms-labs.com is correct.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) { opcache_invalidate($_full, true); }
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_mod_savepoint(true, 2026072300230, 'practicalassessment');
    }

    if ($oldversion < 2026072300231) {
        // FIX-DOMAIN: CSS/template references updated from old brand to lms-labs.com.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) { opcache_invalidate($_full, true); }
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_mod_savepoint(true, 2026072300231, 'practicalassessment');
    }

    if ($oldversion < 2026072300232) {
        // Domain update: lms-labs.com → lms-labs.com
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'lib.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) { opcache_invalidate($_full, true); }
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_mod_savepoint(true, 2026072300232, 'practicalassessment');
    }

    if ($oldversion < 2026072300233) {
        // CSS/template domain update: lms-labs.com → lms-labs.com
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'db/upgrade.php'] as $_f) {
                if (file_exists($_pluginDir . '/' . $_f)) opcache_invalidate($_pluginDir . '/' . $_f, true);
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_mod_savepoint(true, 2026072300233, 'practicalassessment');
    }

    return true;
}