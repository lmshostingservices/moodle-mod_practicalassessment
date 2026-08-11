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
 * AI Practical Assessment - Activity creation form.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_practicalassessment_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'unitcontext', get_string('unitcontext', 'practicalassessment'));

        $mform->addElement('text', 'unitcode', get_string('unitcode', 'practicalassessment'), ['size' => '20']);
        $mform->setType('unitcode', PARAM_TEXT);
        $mform->addHelpButton('unitcode', 'unitcode', 'practicalassessment');

        $mform->addElement('text', 'unitname', get_string('unitname', 'practicalassessment'), ['size' => '64']);
        $mform->setType('unitname', PARAM_TEXT);
        $mform->addHelpButton('unitname', 'unitname', 'practicalassessment');

        $industries = [
            '' => get_string('selectindustry', 'practicalassessment'),
            'construction' => get_string('industry_construction', 'practicalassessment'),
            'healthcare' => get_string('industry_healthcare', 'practicalassessment'),
            'hospitality' => get_string('industry_hospitality', 'practicalassessment'),
            'manufacturing' => get_string('industry_manufacturing', 'practicalassessment'),
            'retail' => get_string('industry_retail', 'practicalassessment'),
            'transport' => get_string('industry_transport', 'practicalassessment'),
            'education' => get_string('industry_education', 'practicalassessment'),
            'mining' => get_string('industry_mining', 'practicalassessment'),
            'agriculture' => get_string('industry_agriculture', 'practicalassessment'),
            'other' => get_string('industry_other', 'practicalassessment')
        ];
        $mform->addElement('select', 'industry', get_string('industry', 'practicalassessment'), $industries);
        $mform->setType('industry', PARAM_TEXT);
        $mform->addHelpButton('industry', 'industry', 'practicalassessment');

        // Country selector - 50 countries matching Chirp 3 HD language support
        $countries = [
            'Australia' => 'Australia',
            'United States' => 'United States',
            'United Kingdom' => 'United Kingdom',
            'Canada' => 'Canada',
            'New Zealand' => 'New Zealand',
            'Ireland' => 'Ireland',
            'South Africa' => 'South Africa',
            'India' => 'India',
            'Singapore' => 'Singapore',
            'Malaysia' => 'Malaysia',
            'Philippines' => 'Philippines',
            'Germany' => 'Germany',
            'France' => 'France',
            'Spain' => 'Spain',
            'Italy' => 'Italy',
            'Netherlands' => 'Netherlands',
            'Belgium' => 'Belgium',
            'Switzerland' => 'Switzerland',
            'Austria' => 'Austria',
            'Sweden' => 'Sweden',
            'Norway' => 'Norway',
            'Denmark' => 'Denmark',
            'Finland' => 'Finland',
            'Poland' => 'Poland',
            'Czech Republic' => 'Czech Republic',
            'Portugal' => 'Portugal',
            'Greece' => 'Greece',
            'Romania' => 'Romania',
            'Hungary' => 'Hungary',
            'Ukraine' => 'Ukraine',
            'Russia' => 'Russia',
            'Turkey' => 'Turkey',
            'Japan' => 'Japan',
            'South Korea' => 'South Korea',
            'China' => 'China',
            'Taiwan' => 'Taiwan',
            'Hong Kong' => 'Hong Kong',
            'Thailand' => 'Thailand',
            'Vietnam' => 'Vietnam',
            'Indonesia' => 'Indonesia',
            'Brazil' => 'Brazil',
            'Mexico' => 'Mexico',
            'Argentina' => 'Argentina',
            'Chile' => 'Chile',
            'Colombia' => 'Colombia',
            'Peru' => 'Peru',
            'Egypt' => 'Egypt',
            'Saudi Arabia' => 'Saudi Arabia',
            'United Arab Emirates' => 'United Arab Emirates',
            'Israel' => 'Israel'
        ];
        $mform->addElement('select', 'country', get_string('country', 'practicalassessment'), $countries);
        $mform->setDefault('country', 'Australia');
        $mform->addHelpButton('country', 'country', 'practicalassessment');

        // State/Territory selector (optional)
        $states = [
            '' => get_string('selectstate', 'practicalassessment'),
            'WA' => 'Western Australia',
            'NSW' => 'New South Wales',
            'VIC' => 'Victoria',
            'QLD' => 'Queensland',
            'SA' => 'South Australia',
            'TAS' => 'Tasmania',
            'NT' => 'Northern Territory',
            'ACT' => 'Australian Capital Territory'
        ];
        $mform->addElement('select', 'state', get_string('state', 'practicalassessment'), $states);
        $mform->setDefault('state', '');
        $mform->addHelpButton('state', 'state', 'practicalassessment');

        $mform->addElement('text', 'jobrole', get_string('jobrole', 'practicalassessment'), ['size' => '64']);
        $mform->setType('jobrole', PARAM_TEXT);
        $mform->addHelpButton('jobrole', 'jobrole', 'practicalassessment');

        $aqflevels = [
            '' => get_string('selectaqf', 'practicalassessment'),
            '1' => 'Certificate I',
            '2' => 'Certificate II',
            '3' => 'Certificate III',
            '4' => 'Certificate IV',
            '5' => 'Diploma',
            '6' => 'Advanced Diploma'
        ];
        $mform->addElement('select', 'aqflevel', get_string('aqflevel', 'practicalassessment'), $aqflevels);
        $mform->addHelpButton('aqflevel', 'aqflevel', 'practicalassessment');

        $occasions = [1 => '1', 2 => '2', 3 => '3'];
        $mform->addElement('select', 'occasions', get_string('occasions', 'practicalassessment'), $occasions);
        $mform->setDefault('occasions', 1);
        $mform->addHelpButton('occasions', 'occasions', 'practicalassessment');

        $mform->addElement('header', 'assessmentcontrols', get_string('assessmentcontrols', 'practicalassessment'));

        $mform->addElement('advcheckbox', 'autogenerate', get_string('autogenerate', 'practicalassessment'));
        $mform->setDefault('autogenerate', 1);
        $mform->addHelpButton('autogenerate', 'autogenerate', 'practicalassessment');

        $mform->addElement('textarea', 'performance_evidence', get_string('performanceevidence', 'practicalassessment'),
            ['rows' => 6, 'cols' => 60]);
        $mform->setType('performance_evidence', PARAM_RAW);
        $mform->addHelpButton('performance_evidence', 'performanceevidence', 'practicalassessment');
        $mform->hideIf('performance_evidence', 'autogenerate', 'eq', 1);

        $mform->addElement('advcheckbox', 'requiresupervisor', get_string('requiresupervisor', 'practicalassessment'));
        $mform->setDefault('requiresupervisor', 1);
        $mform->addHelpButton('requiresupervisor', 'requiresupervisor', 'practicalassessment');

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['unitcode']) && empty($data['unitname'])) {
            $errors['unitcode'] = get_string('unitrequired', 'practicalassessment');
        }

        return $errors;
    }
}
