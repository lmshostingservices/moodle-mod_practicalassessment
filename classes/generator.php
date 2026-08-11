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
 * AI Practical Assessment - Content generator.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_practicalassessment;

defined('MOODLE_INTERNAL') || die();

class generator {
    public static function generate_from_unit(array $unit, array $context): array {
        $manifest = [
            'unitCode' => $unit['code'] ?? '',
            'unitTitle' => $unit['title'] ?? '',
            'occasions' => $unit['occasions'] ?? 1,
            'scenario' => self::generate_scenario($unit, $context),
            'skillsChecklist' => self::derive_skills_checklist($unit),
            'workplaceForms' => self::derive_workplace_forms($unit, $context),
            'mappingMatrix' => [],
            'supervisorEvidence' => self::derive_supervisor_requirement($unit),
            'metadata' => [
                'generatedAt' => date('c'),
                'engineVersion' => '1.0.0',
                'aiAssisted' => true
            ]
        ];

        $manifest['mappingMatrix'] = self::derive_mapping($unit, $manifest['workplaceForms'], $manifest['skillsChecklist']);

        return $manifest;
    }

    private static function generate_scenario(array $unit, array $context): string {
        // Use empty() to catch both null and empty strings
        $industry = !empty($context['industry']) ? $context['industry'] : 'your workplace';
        $jobrole = !empty($context['jobrole']) ? $context['jobrole'] : 'worker';
        $title = !empty($unit['title']) ? $unit['title'] : 'this unit';

        return "You are working as a {$jobrole} in the {$industry} industry. " .
               "As part of your role, you are required to demonstrate competency in {$title}. " .
               "Complete the following tasks and documentation to provide evidence of your skills and knowledge.";
    }

    private static function derive_skills_checklist(array $unit): array {
        $skills = [];
        $id = 1;

        // Log if no PE data available for debugging
        if (empty($unit['performanceEvidence']) && empty($unit['elements'])) {
            debugging('PA Generator: No performance evidence or elements found in unit data - skills checklist will be empty', DEBUG_DEVELOPER);
        }

        if (!empty($unit['performanceEvidence'])) {
            foreach ($unit['performanceEvidence'] as $pe) {
                $skills[] = [
                    'id' => 'skill_' . $id++,
                    'description' => $pe,
                    'criteria' => []
                ];
            }
        }

        if (!empty($unit['elements'])) {
            foreach ($unit['elements'] as $element) {
                if (!empty($element['performanceCriteria'])) {
                    foreach ($element['performanceCriteria'] as $pc) {
                        $skills[] = [
                            'id' => 'skill_' . $id++,
                            'description' => $pc,
                            'criteria' => [$element['code'] ?? '']
                        ];
                    }
                }
            }
        }

        return $skills;
    }

    private static function derive_workplace_forms(array $unit, array $context): array {
        $forms = [];
        $formId = 1;

        if (!empty($unit['performanceEvidence'])) {
            foreach ($unit['performanceEvidence'] as $pe) {
                if (preg_match('/record|document|complete|report|log|form|checklist/i', $pe)) {
                    $forms[] = [
                        'id' => 'form_' . $formId++,
                        'title' => self::derive_form_title($pe, $unit['title'] ?? ''),
                        'purpose' => $pe,
                        'relatedCriteria' => [],
                        'fields' => self::derive_form_fields($pe)
                    ];
                }
            }
        }

        if (empty($forms)) {
            $forms[] = [
                'id' => 'form_' . $formId++,
                'title' => 'Workplace Task Record',
                'purpose' => 'Document the completion of workplace tasks',
                'relatedCriteria' => [],
                'fields' => [
                    ['id' => 'date', 'label' => 'Date completed', 'type' => 'date', 'required' => true],
                    ['id' => 'tasks', 'label' => 'Tasks performed', 'type' => 'textarea', 'required' => true],
                    ['id' => 'outcomes', 'label' => 'Outcomes achieved', 'type' => 'textarea', 'required' => true],
                    ['id' => 'signature', 'label' => 'Worker signature', 'type' => 'signature', 'required' => true]
                ]
            ];
        }

        return $forms;
    }

    private static function derive_form_title(string $evidence, string $unitTitle): string {
        if (preg_match('/hazard/i', $evidence)) {
            return 'Hazard Report';
        }
        if (preg_match('/incident/i', $evidence)) {
            return 'Incident Report';
        }
        if (preg_match('/risk|assessment/i', $evidence)) {
            return 'Risk Assessment Form';
        }
        if (preg_match('/inspection/i', $evidence)) {
            return 'Inspection Checklist';
        }
        if (preg_match('/maintenance/i', $evidence)) {
            return 'Maintenance Log';
        }
        if (preg_match('/quality/i', $evidence)) {
            return 'Quality Checklist';
        }
        if (preg_match('/client|customer/i', $evidence)) {
            return 'Client Sign-off Form';
        }

        return 'Work Record';
    }

    private static function derive_form_fields(string $evidence): array {
        $fields = [
            ['id' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true]
        ];

        if (preg_match('/hazard/i', $evidence)) {
            $fields[] = ['id' => 'location', 'label' => 'Location', 'type' => 'text', 'required' => true];
            $fields[] = ['id' => 'hazard_desc', 'label' => 'Hazard description', 'type' => 'textarea', 'required' => true];
            $fields[] = ['id' => 'risk_level', 'label' => 'Risk level', 'type' => 'matrix_5x5', 'required' => true];
            $fields[] = ['id' => 'controls', 'label' => 'Control measures', 'type' => 'textarea', 'required' => true];
        } elseif (preg_match('/incident/i', $evidence)) {
            $fields[] = ['id' => 'time', 'label' => 'Time of incident', 'type' => 'text', 'required' => true];
            $fields[] = ['id' => 'location', 'label' => 'Location', 'type' => 'text', 'required' => true];
            $fields[] = ['id' => 'description', 'label' => 'Incident description', 'type' => 'textarea', 'required' => true];
            $fields[] = ['id' => 'injuries', 'label' => 'Injuries/damage', 'type' => 'textarea', 'required' => false];
            $fields[] = ['id' => 'actions', 'label' => 'Corrective actions', 'type' => 'textarea', 'required' => true];
        } elseif (preg_match('/risk|assessment/i', $evidence)) {
            $fields[] = ['id' => 'activity', 'label' => 'Activity/task', 'type' => 'text', 'required' => true];
            $fields[] = ['id' => 'hazards', 'label' => 'Identified hazards', 'type' => 'textarea', 'required' => true];
            $fields[] = ['id' => 'risk_rating', 'label' => 'Risk rating', 'type' => 'matrix_5x5', 'required' => true];
            $fields[] = ['id' => 'controls', 'label' => 'Control measures', 'type' => 'textarea', 'required' => true];
            $fields[] = ['id' => 'residual_risk', 'label' => 'Residual risk', 'type' => 'matrix_5x5', 'required' => true];
        } else {
            $fields[] = ['id' => 'details', 'label' => 'Work details', 'type' => 'textarea', 'required' => true];
            $fields[] = ['id' => 'outcomes', 'label' => 'Outcomes', 'type' => 'textarea', 'required' => false];
        }

        $fields[] = ['id' => 'signature', 'label' => 'Worker signature', 'type' => 'signature', 'required' => true];

        return $fields;
    }

    private static function derive_supervisor_requirement(array $unit): array {
        $requires = false;

        if (!empty($unit['assessmentConditions'])) {
            $conditions = implode(' ', $unit['assessmentConditions']);
            $requires = preg_match('/workplace|supervisor|third.party/i', $conditions);
        }

        return [
            'required' => $requires,
            'declarationText' => 'I confirm that the student completed the above tasks in the workplace and the information provided is accurate.',
            'verificationFields' => [
                ['label' => 'Supervisor name', 'type' => 'text'],
                ['label' => 'Supervisor position', 'type' => 'text'],
                ['label' => 'Signature', 'type' => 'signature']
            ]
        ];
    }

    private static function derive_mapping(array $unit, array $forms, array $skills): array {
        $mappings = [];

        if (!empty($unit['elements'])) {
            foreach ($unit['elements'] as $element) {
                if (!empty($element['performanceCriteria'])) {
                    foreach ($element['performanceCriteria'] as $pc) {
                        foreach ($skills as $skill) {
                            $mappings[] = [
                                'criterion' => ($element['code'] ?? '') . ' - ' . $pc,
                                'evidence' => $skill['description'],
                                'source' => 'Observation'
                            ];
                        }

                        foreach ($forms as $form) {
                            foreach ($form['fields'] as $field) {
                                $mappings[] = [
                                    'criterion' => ($element['code'] ?? '') . ' - ' . $pc,
                                    'evidence' => $form['title'] . ' - ' . $field['label'],
                                    'source' => 'Document'
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $mappings;
    }
}
