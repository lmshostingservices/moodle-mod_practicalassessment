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
 * AI Practical Assessment - TGA Training Component API.
 * 
 * Calls EssayGraderAI server-side API for TGA data.
 * TGA credentials are managed server-side - no plugin settings needed.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment\tga;

defined('MOODLE_INTERNAL') || die();

class training_component {
    private const API_BASE = 'https://lms-labs.com/api/tga/unit/';
    private const CACHE_TTL = 2592000; // 30 days

    private $siteid;
    private $apikey;

    public function __construct() {
        global $CFG;
        
        // Explicitly include aiconfig lib.php if available
        $aiconfiglib = $CFG->dirroot . '/local/aiconfig/lib.php';
        if (file_exists($aiconfiglib)) {
            require_once($aiconfiglib);
        }
        
        // Get credentials from AI Grader Central Config or plugin settings
        // Use priority: Central Config -> Plugin-specific settings
        if (function_exists('local_aiconfig_get_siteid')) {
            $this->siteid = local_aiconfig_get_siteid('mod_practicalassessment');
            $this->apikey = local_aiconfig_get_apikey('mod_practicalassessment');
        } else {
            // Fallback to plugin-specific config if central config not installed
            $this->siteid = get_config('mod_practicalassessment', 'siteid');
            $this->apikey = get_config('mod_practicalassessment', 'apikey');
        }
    }

    /**
     * Get unit of competency data from server-side API.
     * TGA credentials are handled server-side.
     */
    public function get_unit(string $code): ?array {
        $code = strtoupper(trim($code));
        $cache = \cache::make('mod_practicalassessment', 'tga_units');
        $cached = $cache->get($code);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $result = $this->fetch_from_api($code);
            if ($result) {
                $cache->set($code, $result);
            }
            return $result;
        } catch (\Exception $e) {
            debugging('TGA API error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }

    /**
     * Fetch unit data from EssayGraderAI server API.
     * Server handles TGA authentication.
     */
    private function fetch_from_api(string $code): ?array {
        if (empty($this->siteid) || empty($this->apikey)) {
            debugging('TGA API: Missing Site ID or API Key', DEBUG_DEVELOPER);
            return null;
        }

        $url = self::API_BASE . urlencode($code);
        
        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
        ]);
        $curl->setHeader([
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Site-ID: ' . $this->siteid,
            'X-API-Key: ' . $this->apikey
        ]);

        $response = $curl->get($url);
        $info = $curl->get_info();
        $httpcode = isset($info['http_code']) ? $info['http_code'] : 0;

        if ($httpcode < 200 || $httpcode >= 300) {
            debugging('TGA API error: HTTP ' . $httpcode, DEBUG_DEVELOPER);
            return null;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['success']) || !$data['success']) {
            debugging('TGA API error: ' . ($data['error'] ?? 'Unknown error'), DEBUG_DEVELOPER);
            return null;
        }

        // Normalize response to expected format
        return $this->normalize_api_response($data['unit'] ?? $data);
    }

    /**
     * Normalize API response to expected format.
     */
    private function normalize_api_response(array $data): array {
        return [
            'code' => $data['code'] ?? $data['unitCode'] ?? '',
            'title' => $data['title'] ?? $data['unitTitle'] ?? '',
            'description' => $data['description'] ?? $data['application'] ?? '',
            'elements' => $data['elements'] ?? [],
            'performanceEvidence' => $data['performanceEvidence'] ?? [],
            'knowledgeEvidence' => $data['knowledgeEvidence'] ?? [],
            'assessmentConditions' => $data['assessmentConditions'] ?? [],
            'occasions' => $data['occasions'] ?? 1
        ];
    }

    /**
     * Search units (not implemented - use API search endpoint if needed).
     */
    public function search_units(string $query): array {
        return [];
    }
}
