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
 * AI Practical Assessment - Comprehensive Plugin Diagnostics
 * 
 * Tests every component of the plugin and reports results in a table.
 * Run via: Site Admin > Plugins > Activity Modules > AI Practical Assessment > Run Diagnostics
 * Or direct URL: /mod/practicalassessment/tests/plugin_diagnostics.php
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require site admin
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url('/mod/practicalassessment/tests/plugin_diagnostics.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('AI Practical Assessment - Plugin Diagnostics');
$PAGE->set_heading('AI Practical Assessment - Plugin Diagnostics v3.2.7');

echo $OUTPUT->header();

class pa_diagnostic_engine {
    
    private $results = [];
    private $db;
    
    public function __construct() {
        global $DB;
        $this->db = $DB;
    }
    
    public function run_all_tests(): array {
        $this->results = [];
        
        // Category 1: Database Schema Tests
        $this->test_database_tables();
        $this->test_database_columns();
        
        // Category 2: Configuration Tests
        $this->test_plugin_settings();
        $this->test_central_config_integration();
        
        // Category 3: TGA API Tests
        $this->test_tga_api_connection();
        $this->test_tga_unit_lookup();
        
        // Category 4: Generator Tests
        $this->test_generator_scenario();
        $this->test_generator_skills();
        $this->test_generator_forms();
        
        // Category 5: Form Save/Load Tests
        $this->test_mod_form_fields();
        $this->test_context_json_save();
        
        // Category 6: External Services Tests
        $this->test_external_services_defined();
        $this->test_ajax_endpoints();
        
        // Category 7: Language String Tests
        $this->test_language_strings();
        $this->test_help_strings();
        
        // Category 8: Capability Tests
        $this->test_capabilities();
        
        // Category 9: File Structure Tests
        $this->test_required_files();
        $this->test_amd_modules();
        
        return $this->results;
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // DATABASE TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_database_tables(): void {
        $tables = ['practicalassessment', 'practicalassessment_submission'];
        
        foreach ($tables as $table) {
            $exists = $this->db->get_manager()->table_exists($table);
            $this->add_result(
                'Database',
                "Table: {$table}",
                $exists ? 'EXISTS' : 'MISSING',
                $exists ? 'pass' : 'fail',
                $exists ? "Table {$table} exists in database" : "Table {$table} is missing - run upgrade.php"
            );
        }
    }
    
    private function test_database_columns(): void {
        $expected_columns = [
            'practicalassessment' => [
                'id', 'course', 'name', 'intro', 'introformat', 'unitcode', 'unitname',
                'industry', 'country', 'state', 'jobrole', 'aqflevel', 'occasions',
                'autogenerate', 'requiresupervisor', 'scenario_text', 'skills_json',
                'forms_json', 'mapping_json', 'context_json', 'timecreated', 'timemodified'
            ],
            'practicalassessment_submission' => [
                'id', 'practicalassessmentid', 'userid', 'status', 'forms_data',
                'skills_completed', 'supervisor_name', 'supervisor_email', 'supervisor_token',
                'supervisor_verified', 'declaration_agreed', 'grade', 'feedback',
                'timecreated', 'timemodified'
            ]
        ];
        
        foreach ($expected_columns as $table => $columns) {
            if (!$this->db->get_manager()->table_exists($table)) {
                continue;
            }
            
            $dbcolumns = $this->db->get_columns($table);
            $dbcolnames = array_keys($dbcolumns);
            
            foreach ($columns as $col) {
                $exists = in_array($col, $dbcolnames);
                $this->add_result(
                    'Database',
                    "Column: {$table}.{$col}",
                    $exists ? 'EXISTS' : 'MISSING',
                    $exists ? 'pass' : 'fail',
                    $exists ? "Column exists" : "Column missing - check install.xml/upgrade.php"
                );
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // CONFIGURATION TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_plugin_settings(): void {
        $config = get_config('mod_practicalassessment');
        
        $this->add_result(
            'Configuration',
            'Plugin config object',
            is_object($config) ? 'EXISTS' : 'MISSING',
            'info',
            'Plugin configuration retrieved: ' . (is_object($config) ? count((array)$config) . ' settings' : 'none')
        );
    }
    
    private function test_central_config_integration(): void {
        $has_central = function_exists('local_aiconfig_get_siteid');
        
        $this->add_result(
            'Configuration',
            'AI Grader Central Config',
            $has_central ? 'INSTALLED' : 'NOT INSTALLED',
            $has_central ? 'pass' : 'warn',
            $has_central 
                ? 'Central config functions available - Site ID/API Key managed centrally'
                : 'Central config not installed - using plugin-specific settings (optional)'
        );
        
        if ($has_central) {
            $siteid = local_aiconfig_get_siteid('mod_practicalassessment');
            $apikey = local_aiconfig_get_apikey('mod_practicalassessment');
            
            $this->add_result(
                'Configuration',
                'Site ID (via Central Config)',
                !empty($siteid) ? 'SET (' . substr($siteid, 0, 8) . '...)' : 'NOT SET',
                !empty($siteid) ? 'pass' : 'fail',
                !empty($siteid) ? 'Site ID configured' : 'Site ID not configured - TGA API will fail'
            );
            
            $this->add_result(
                'Configuration',
                'API Key (via Central Config)',
                !empty($apikey) ? 'SET (hidden)' : 'NOT SET',
                !empty($apikey) ? 'pass' : 'fail',
                !empty($apikey) ? 'API Key configured' : 'API Key not configured - TGA API will fail'
            );
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // TGA API TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_tga_api_connection(): void {
        global $CFG;
        
        $tga_file = $CFG->dirroot . '/mod/practicalassessment/classes/tga/training_component.php';
        $exists = file_exists($tga_file);
        
        $this->add_result(
            'TGA API',
            'TGA class file',
            $exists ? 'EXISTS' : 'MISSING',
            $exists ? 'pass' : 'fail',
            $exists ? 'TGA training_component.php found' : 'TGA class file missing'
        );
        
        if ($exists) {
            require_once($tga_file);
            $tga = new \mod_practicalassessment\tga\training_component();
            
            $this->add_result(
                'TGA API',
                'TGA class instantiation',
                'SUCCESS',
                'pass',
                'TGA training_component class instantiated successfully'
            );
        }
    }
    
    private function test_tga_unit_lookup(): void {
        global $CFG;
        
        $tga_file = $CFG->dirroot . '/mod/practicalassessment/classes/tga/training_component.php';
        if (!file_exists($tga_file)) {
            return;
        }
        
        require_once($tga_file);
        $tga = new \mod_practicalassessment\tga\training_component();
        
        // Test with a known unit code
        $test_code = 'BSBWHS411';
        $unit = $tga->get_unit($test_code);
        
        if ($unit === null) {
            $this->add_result(
                'TGA API',
                "Unit lookup: {$test_code}",
                'FAILED',
                'fail',
                'TGA API returned null - check Site ID/API Key configuration or network connectivity'
            );
            return;
        }
        
        $this->add_result(
            'TGA API',
            "Unit lookup: {$test_code}",
            'SUCCESS',
            'pass',
            'Unit title: ' . ($unit['title'] ?? 'No title')
        );
        
        // Check returned data structure
        $required_keys = ['code', 'title', 'performanceEvidence', 'elements'];
        foreach ($required_keys as $key) {
            $has_key = isset($unit[$key]);
            $value_info = '';
            
            if ($has_key) {
                if (is_array($unit[$key])) {
                    $value_info = count($unit[$key]) . ' items';
                } else {
                    $value_info = strlen($unit[$key]) . ' chars';
                }
            }
            
            $this->add_result(
                'TGA API',
                "Unit data: {$key}",
                $has_key ? "HAS DATA ({$value_info})" : 'MISSING/EMPTY',
                $has_key && !empty($unit[$key]) ? 'pass' : 'warn',
                $has_key ? "Field '{$key}' present in TGA response" : "Field '{$key}' missing - skills checklist may be empty"
            );
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // GENERATOR TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_generator_scenario(): void {
        global $CFG;
        
        require_once($CFG->dirroot . '/mod/practicalassessment/classes/generator.php');
        
        // Test with empty context (should default properly)
        $unit = ['title' => 'Test Unit', 'performanceEvidence' => [], 'elements' => []];
        $context = ['industry' => '', 'jobrole' => '']; // Empty strings
        
        $manifest = \mod_practicalassessment\generator::generate_from_unit($unit, $context);
        $scenario = $manifest['scenario'];
        
        // Check for placeholder issues
        $has_empty_role = strpos($scenario, 'as a in') !== false;
        $has_worker_default = strpos($scenario, 'as a worker') !== false;
        
        $this->add_result(
            'Generator',
            'Scenario with empty job role',
            $has_worker_default ? 'DEFAULTS TO "worker"' : ($has_empty_role ? 'EMPTY PLACEHOLDER' : 'UNKNOWN'),
            $has_worker_default ? 'pass' : 'fail',
            'Scenario: "' . substr($scenario, 0, 80) . '..."'
        );
        
        // Test with filled context
        $context2 = ['industry' => 'construction', 'jobrole' => 'Site Supervisor'];
        $manifest2 = \mod_practicalassessment\generator::generate_from_unit($unit, $context2);
        $scenario2 = $manifest2['scenario'];
        
        $has_role = strpos($scenario2, 'Site Supervisor') !== false;
        $has_industry = strpos($scenario2, 'construction') !== false;
        
        $this->add_result(
            'Generator',
            'Scenario with filled context',
            ($has_role && $has_industry) ? 'CORRECT' : 'MISSING DATA',
            ($has_role && $has_industry) ? 'pass' : 'fail',
            'Contains job role: ' . ($has_role ? 'YES' : 'NO') . ', Contains industry: ' . ($has_industry ? 'YES' : 'NO')
        );
    }
    
    private function test_generator_skills(): void {
        global $CFG;
        
        require_once($CFG->dirroot . '/mod/practicalassessment/classes/generator.php');
        
        // Test with no PE data
        $unit_empty = ['title' => 'Test', 'performanceEvidence' => [], 'elements' => []];
        $manifest = \mod_practicalassessment\generator::generate_from_unit($unit_empty, []);
        
        $this->add_result(
            'Generator',
            'Skills from empty unit',
            count($manifest['skillsChecklist']) . ' skills',
            count($manifest['skillsChecklist']) === 0 ? 'warn' : 'pass',
            'Empty unit data produces ' . count($manifest['skillsChecklist']) . ' skills (expected: 0)'
        );
        
        // Test with PE data
        $unit_with_pe = [
            'title' => 'Test',
            'performanceEvidence' => [
                'Complete hazard identification',
                'Document risk assessment',
                'Implement control measures'
            ],
            'elements' => []
        ];
        $manifest2 = \mod_practicalassessment\generator::generate_from_unit($unit_with_pe, []);
        
        $this->add_result(
            'Generator',
            'Skills from PE data',
            count($manifest2['skillsChecklist']) . ' skills',
            count($manifest2['skillsChecklist']) === 3 ? 'pass' : 'fail',
            '3 PE items should produce 3 skills, got: ' . count($manifest2['skillsChecklist'])
        );
    }
    
    private function test_generator_forms(): void {
        global $CFG;
        
        require_once($CFG->dirroot . '/mod/practicalassessment/classes/generator.php');
        
        // Test fallback form
        $unit_empty = ['title' => 'Test', 'performanceEvidence' => [], 'elements' => []];
        $manifest = \mod_practicalassessment\generator::generate_from_unit($unit_empty, []);
        
        $has_fallback = false;
        foreach ($manifest['workplaceForms'] as $form) {
            if ($form['title'] === 'Workplace Task Record') {
                $has_fallback = true;
            }
        }
        
        $this->add_result(
            'Generator',
            'Fallback form generation',
            $has_fallback ? 'GENERATED' : 'MISSING',
            $has_fallback ? 'pass' : 'fail',
            'Generic "Workplace Task Record" form should be created when no PE matches form patterns'
        );
        
        // Test specific form detection
        $unit_hazard = [
            'title' => 'Test',
            'performanceEvidence' => ['Complete hazard report documentation'],
            'elements' => []
        ];
        $manifest2 = \mod_practicalassessment\generator::generate_from_unit($unit_hazard, []);
        
        $has_hazard_form = false;
        foreach ($manifest2['workplaceForms'] as $form) {
            if (strpos($form['title'], 'Hazard') !== false) {
                $has_hazard_form = true;
            }
        }
        
        $this->add_result(
            'Generator',
            'Hazard form detection',
            $has_hazard_form ? 'DETECTED' : 'NOT DETECTED',
            $has_hazard_form ? 'pass' : 'warn',
            'PE containing "hazard" should generate Hazard Report form'
        );
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // FORM SAVE/LOAD TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_mod_form_fields(): void {
        global $CFG;
        
        $form_file = $CFG->dirroot . '/mod/practicalassessment/mod_form.php';
        $content = file_get_contents($form_file);
        
        $expected_fields = [
            'name' => 'addElement.*name',
            'unitcode' => 'addElement.*unitcode',
            'unitname' => 'addElement.*unitname',
            'industry' => 'addElement.*industry',
            'country' => 'addElement.*country',
            'state' => 'addElement.*state',
            'jobrole' => 'addElement.*jobrole',
            'aqflevel' => 'addElement.*aqflevel',
            'occasions' => 'addElement.*occasions',
            'autogenerate' => 'addElement.*autogenerate',
            'requiresupervisor' => 'addElement.*requiresupervisor'
        ];
        
        foreach ($expected_fields as $field => $pattern) {
            $found = preg_match('/' . $pattern . '/i', $content);
            $this->add_result(
                'Form Fields',
                "mod_form: {$field}",
                $found ? 'DEFINED' : 'MISSING',
                $found ? 'pass' : 'fail',
                $found ? "Form field '{$field}' is defined in mod_form.php" : "Form field '{$field}' missing from mod_form.php"
            );
        }
    }
    
    private function test_context_json_save(): void {
        global $CFG;
        
        $lib_file = $CFG->dirroot . '/mod/practicalassessment/lib.php';
        $content = file_get_contents($lib_file);
        
        // Check if context_json is being saved in add_instance
        $saves_context = strpos($content, 'context_json') !== false;
        $encodes_json = strpos($content, 'json_encode') !== false && strpos($content, 'context') !== false;
        
        $this->add_result(
            'Form Save',
            'context_json field usage',
            $saves_context ? 'USED' : 'NOT USED',
            $saves_context ? 'pass' : 'warn',
            $saves_context ? 'lib.php references context_json' : 'lib.php does not reference context_json'
        );
        
        // Check if industry/jobrole/etc are being read from form data
        $reads_industry = preg_match('/\$data->industry/', $content);
        $reads_jobrole = preg_match('/\$data->jobrole/', $content);
        
        $this->add_result(
            'Form Save',
            'Reads form data (industry)',
            $reads_industry ? 'YES' : 'NO',
            $reads_industry ? 'pass' : 'fail',
            $reads_industry ? 'lib.php reads $data->industry' : 'lib.php does NOT read $data->industry - form data may not save!'
        );
        
        $this->add_result(
            'Form Save',
            'Reads form data (jobrole)',
            $reads_jobrole ? 'YES' : 'NO',
            $reads_jobrole ? 'pass' : 'fail',
            $reads_jobrole ? 'lib.php reads $data->jobrole' : 'lib.php does NOT read $data->jobrole - form data may not save!'
        );
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // EXTERNAL SERVICES TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_external_services_defined(): void {
        global $CFG;
        
        $services_file = $CFG->dirroot . '/mod/practicalassessment/db/services.php';
        
        if (!file_exists($services_file)) {
            $this->add_result('External Services', 'services.php', 'MISSING', 'fail', 'db/services.php file not found');
            return;
        }
        
        $content = file_get_contents($services_file);
        
        $expected_services = [
            'save_submission' => 'mod_practicalassessment_save_submission',
            'get_manifest' => 'mod_practicalassessment_get_manifest',
            'generate_assessment' => 'mod_practicalassessment_generate_assessment',
            'lookup_unit' => 'mod_practicalassessment_lookup_unit'
        ];
        
        foreach ($expected_services as $name => $function) {
            $found = strpos($content, $function) !== false;
            $this->add_result(
                'External Services',
                "Service: {$name}",
                $found ? 'DEFINED' : 'MISSING',
                $found ? 'pass' : 'warn',
                $found ? "External function '{$function}' defined" : "External function '{$function}' not found in services.php"
            );
        }
    }
    
    private function test_ajax_endpoints(): void {
        global $CFG;
        
        $ajax_file = $CFG->dirroot . '/mod/practicalassessment/ajax.php';
        
        if (!file_exists($ajax_file)) {
            $this->add_result('AJAX', 'ajax.php', 'MISSING', 'fail', 'ajax.php file not found');
            return;
        }
        
        $content = file_get_contents($ajax_file);
        
        $expected_actions = ['save_draft', 'submit', 'get_manifest', 'grade'];
        
        foreach ($expected_actions as $action) {
            $found = strpos($content, "'{$action}'") !== false || strpos($content, "\"{$action}\"") !== false;
            $this->add_result(
                'AJAX',
                "Action: {$action}",
                $found ? 'HANDLED' : 'NOT FOUND',
                $found ? 'pass' : 'warn',
                $found ? "AJAX action '{$action}' is handled" : "AJAX action '{$action}' not found in ajax.php"
            );
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // LANGUAGE STRING TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_language_strings(): void {
        $required_strings = [
            'modulename', 'pluginname', 'unitcode', 'unitname', 'industry',
            'country', 'state', 'jobrole', 'aqflevel', 'occasions',
            'autogenerate', 'requiresupervisor', 'scenario', 'skillschecklist',
            'workplaceforms', 'submit', 'savedraft'
        ];
        
        foreach ($required_strings as $string) {
            try {
                $value = get_string($string, 'practicalassessment');
                $this->add_result(
                    'Language Strings',
                    $string,
                    'DEFINED',
                    'pass',
                    "Value: \"{$value}\""
                );
            } catch (Exception $e) {
                $this->add_result(
                    'Language Strings',
                    $string,
                    'MISSING',
                    'fail',
                    'String not defined in lang/en/practicalassessment.php'
                );
            }
        }
    }
    
    private function test_help_strings(): void {
        $help_strings = [
            'unitcode_help', 'unitname_help', 'industry_help', 'country_help',
            'state_help', 'jobrole_help', 'aqflevel_help', 'occasions_help',
            'autogenerate_help', 'requiresupervisor_help'
        ];
        
        foreach ($help_strings as $string) {
            try {
                $value = get_string($string, 'practicalassessment');
                $this->add_result(
                    'Help Strings',
                    str_replace('_help', '', $string),
                    'DEFINED',
                    'pass',
                    'Help tooltip will display: "' . substr($value, 0, 50) . '..."'
                );
            } catch (Exception $e) {
                $this->add_result(
                    'Help Strings',
                    str_replace('_help', '', $string),
                    'MISSING',
                    'fail',
                    'Help string missing - tooltip will not show'
                );
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // CAPABILITY TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_capabilities(): void {
        global $CFG;
        
        $access_file = $CFG->dirroot . '/mod/practicalassessment/db/access.php';
        
        if (!file_exists($access_file)) {
            $this->add_result('Capabilities', 'access.php', 'MISSING', 'fail', 'db/access.php file not found');
            return;
        }
        
        $content = file_get_contents($access_file);
        
        $expected_caps = ['addinstance', 'view', 'submit', 'grade', 'supervise'];
        
        foreach ($expected_caps as $cap) {
            $found = strpos($content, "mod/practicalassessment:{$cap}") !== false;
            $this->add_result(
                'Capabilities',
                "mod/practicalassessment:{$cap}",
                $found ? 'DEFINED' : 'MISSING',
                $found ? 'pass' : 'warn',
                $found ? "Capability defined in access.php" : "Capability not found in access.php"
            );
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // FILE STRUCTURE TESTS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function test_required_files(): void {
        global $CFG;
        
        $base = $CFG->dirroot . '/mod/practicalassessment';
        
        $required_files = [
            'version.php' => 'Plugin version information',
            'lib.php' => 'Core library functions',
            'mod_form.php' => 'Activity creation form',
            'view.php' => 'Student view page',
            'index.php' => 'Course module listing',
            'settings.php' => 'Admin settings',
            'styles.css' => 'Plugin styles',
            'db/install.xml' => 'Database schema',
            'db/access.php' => 'Capability definitions',
            'db/services.php' => 'External service definitions',
            'lang/en/practicalassessment.php' => 'English language strings',
            'classes/generator.php' => 'Content generator',
            'classes/output/student.php' => 'Student view renderer',
            'classes/output/grader.php' => 'Grader view renderer',
            'classes/tga/training_component.php' => 'TGA API client'
        ];
        
        foreach ($required_files as $file => $purpose) {
            $path = $base . '/' . $file;
            $exists = file_exists($path);
            $this->add_result(
                'File Structure',
                $file,
                $exists ? 'EXISTS' : 'MISSING',
                $exists ? 'pass' : 'fail',
                $purpose
            );
        }
    }
    
    private function test_amd_modules(): void {
        global $CFG;
        
        $amd_base = $CFG->dirroot . '/mod/practicalassessment/amd';
        
        $modules = ['player', 'grader', 'builder', 'supervisor'];
        
        foreach ($modules as $module) {
            $src_exists = file_exists($amd_base . "/src/{$module}.js");
            $min_exists = file_exists($amd_base . "/build/{$module}.min.js");
            
            $this->add_result(
                'AMD Modules',
                "{$module}.js (source)",
                $src_exists ? 'EXISTS' : 'MISSING',
                $src_exists ? 'pass' : 'fail',
                "amd/src/{$module}.js"
            );
            
            $this->add_result(
                'AMD Modules',
                "{$module}.min.js (minified)",
                $min_exists ? 'EXISTS' : 'MISSING',
                $min_exists ? 'pass' : 'warn',
                $min_exists ? "Minified version available" : "Run grunt to generate minified version"
            );
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════════════════
    
    private function add_result(string $category, string $test, string $result, string $status, string $explanation): void {
        $this->results[] = [
            'category' => $category,
            'test' => $test,
            'result' => $result,
            'status' => $status,
            'explanation' => $explanation
        ];
    }
    
    public function render_results(): string {
        $html = '<style>
            .pa-diag-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
            .pa-diag-table th, .pa-diag-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            .pa-diag-table th { background: #333; color: #fff; }
            .pa-diag-table tr:nth-child(even) { background: #f9f9f9; }
            .pa-diag-pass { background: #d4edda !important; }
            .pa-diag-fail { background: #f8d7da !important; }
            .pa-diag-warn { background: #fff3cd !important; }
            .pa-diag-info { background: #d1ecf1 !important; }
            .pa-diag-category { background: #e9ecef !important; font-weight: bold; }
            .pa-diag-badge { padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
            .pa-diag-badge-pass { background: #28a745; color: #fff; }
            .pa-diag-badge-fail { background: #dc3545; color: #fff; }
            .pa-diag-badge-warn { background: #ffc107; color: #000; }
            .pa-diag-badge-info { background: #17a2b8; color: #fff; }
            .pa-diag-summary { padding: 20px; margin: 20px 0; border-radius: 8px; }
        </style>';
        
        // Summary
        $pass_count = count(array_filter($this->results, fn($r) => $r['status'] === 'pass'));
        $fail_count = count(array_filter($this->results, fn($r) => $r['status'] === 'fail'));
        $warn_count = count(array_filter($this->results, fn($r) => $r['status'] === 'warn'));
        $total = count($this->results);
        
        $summary_class = $fail_count > 0 ? 'pa-diag-fail' : ($warn_count > 0 ? 'pa-diag-warn' : 'pa-diag-pass');
        
        $html .= "<div class='pa-diag-summary {$summary_class}'>";
        $html .= "<h3>Diagnostic Summary</h3>";
        $html .= "<p><strong>Total Tests:</strong> {$total} | ";
        $html .= "<span style='color: #28a745;'>Pass: {$pass_count}</span> | ";
        $html .= "<span style='color: #dc3545;'>Fail: {$fail_count}</span> | ";
        $html .= "<span style='color: #ffc107;'>Warn: {$warn_count}</span></p>";
        $html .= "</div>";
        
        // Results table
        $html .= '<table class="pa-diag-table">';
        $html .= '<thead><tr><th>Category</th><th>Test</th><th>Result</th><th>Status</th><th>Explanation</th></tr></thead>';
        $html .= '<tbody>';
        
        $current_category = '';
        foreach ($this->results as $row) {
            $row_class = 'pa-diag-' . $row['status'];
            $badge_class = 'pa-diag-badge-' . $row['status'];
            
            $category_display = ($row['category'] !== $current_category) ? $row['category'] : '';
            $current_category = $row['category'];
            
            $html .= "<tr class='{$row_class}'>";
            $html .= "<td>{$category_display}</td>";
            $html .= "<td>{$row['test']}</td>";
            $html .= "<td>{$row['result']}</td>";
            $html .= "<td><span class='pa-diag-badge {$badge_class}'>" . strtoupper($row['status']) . "</span></td>";
            $html .= "<td>{$row['explanation']}</td>";
            $html .= "</tr>";
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }
}

// Run diagnostics
$engine = new pa_diagnostic_engine();
$engine->run_all_tests();
echo $engine->render_results();

echo $OUTPUT->footer();
