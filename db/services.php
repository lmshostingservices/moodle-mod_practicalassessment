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
 * AI Practical Assessment - External services.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [

    'mod_practicalassessment_get_manifest' => [
        'classname' => 'mod_practicalassessment\external\get_manifest',
        'methodname' => 'execute',
        'description' => 'Get the assessment manifest for an activity',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true
    ],

    'mod_practicalassessment_save_submission' => [
        'classname' => 'mod_practicalassessment\external\save_submission',
        'methodname' => 'execute',
        'description' => 'Save student submission data',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true
    ],

    'mod_practicalassessment_generate_assessment' => [
        'classname' => 'mod_practicalassessment\external\generate_assessment',
        'methodname' => 'execute',
        'description' => 'Generate assessment from unit code',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true
    ],

    'mod_practicalassessment_lookup_unit' => [
        'classname' => 'mod_practicalassessment\external\lookup_unit',
        'methodname' => 'execute',
        'description' => 'Lookup unit from TGA',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true
    ]

];

$services = [
    'Practical Assessment Service' => [
        'functions' => [
            'mod_practicalassessment_get_manifest',
            'mod_practicalassessment_save_submission',
            'mod_practicalassessment_generate_assessment',
            'mod_practicalassessment_lookup_unit'
        ],
        'restrictedusers' => 0,
        'enabled' => 1
    ]
];
