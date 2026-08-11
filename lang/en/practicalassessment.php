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
 * AI Practical Assessment - Language strings.
 *
 * @package    mod_practicalassessment
 * @copyright  2025 AI Grader <support@lmshostingservices.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'AI Practical Assessment';
$string['modulenameplural'] = 'AI Practical Assessments';
$string['modulename_help'] = 'AI Practical Assessment manages workplace-based competency assessments aligned to Australian Units of Competency, generating industry-specific forms and skills checklists from unit criteria.

Teachers enter a unit code and the plugin fetches Performance Evidence and Performance Criteria from training.gov.au (TGA). The AI then generates a complete assessment package tailored to the selected industry: Construction (SWMS, JSA, Permits to Work, Equipment Inspections), Healthcare (Incident Reports, Medication Records, Risk Assessments), Hospitality (Food Safety Logs, Hazard Reports, Toolbox Talks), Manufacturing, Retail, Transport, Education, Mining, Agriculture, and more.

The student view has two tabs. The Workplace Forms tab shows AI-generated industry-standard workplace forms occupying 60% of the screen, with teacher grading columns in the remaining 40% — teachers mark each form section Satisfactory or Not Yet Satisfactory, with mandatory written feedback required for any NYS item. The Skills Assessment tab displays a matrix of Performance Criteria against the configured number of assessment occasions (1–3), with S/NYS outcome badges per criterion and a sticky real-time grade summary bar showing overall progress.

Supervisor email verification sends a unique verification link to an external workplace supervisor who reviews the student\'s submitted evidence and confirms skill demonstration. Teachers can request AI grading suggestions for any form section. AQF level and state/territory are configurable for jurisdiction-specific legislation references. An 8-column evidence matrix export supports ASQA compliance documentation and audit requirements.';
$string['pluginname'] = 'AI Practical Assessment';
$string['pluginadministration'] = 'Practical Assessment Administration';

$string['practicalassessment:addinstance'] = 'Add a new practical assessment';
$string['practicalassessment:view'] = 'View practical assessment';
$string['practicalassessment:submit'] = 'Submit practical assessment';
$string['practicalassessment:grade'] = 'Grade practical assessment';
$string['practicalassessment:supervise'] = 'Supervise practical assessment';

$string['unitcontext'] = 'Unit & Context';
$string['unitcode'] = 'Unit Code';
$string['unitcode_help'] = 'Enter the national unit of competency code (e.g., BSBWHS411). This will be used to fetch unit details from training.gov.au.';
$string['unitname'] = 'Unit Name';
$string['unitname_help'] = 'The full title of the unit of competency. This will be auto-populated if you look up the unit code.';
$string['industry'] = 'Industry';
$string['industry_help'] = 'Select the industry context for this practical assessment. This helps generate realistic workplace scenarios and appropriate WHS considerations.';
$string['selectindustry'] = 'Select industry...';
$string['industry_construction'] = 'Construction';
$string['industry_healthcare'] = 'Healthcare';
$string['industry_hospitality'] = 'Hospitality';
$string['industry_manufacturing'] = 'Manufacturing';
$string['industry_retail'] = 'Retail';
$string['industry_transport'] = 'Transport & Logistics';
$string['industry_education'] = 'Education & Training';
$string['industry_mining'] = 'Mining';
$string['industry_agriculture'] = 'Agriculture';
$string['industry_other'] = 'Other';

$string['country'] = 'Country';
$string['country_help'] = 'Select the country where the workplace assessment takes place. This determines legislation references and spelling conventions.';
$string['state'] = 'State/Territory (optional)';
$string['state_help'] = 'Select the state or territory for Australian-specific regulatory requirements and WHS legislation references.';
$string['selectstate'] = 'Select state/territory...';
$string['jobrole'] = 'Job Role';
$string['jobrole_help'] = 'Enter the job role or position the student is training for (e.g., WHS Officer, Site Supervisor, Team Leader). This contextualises the workplace scenarios.';
$string['aqflevel'] = 'AQF Level';
$string['aqflevel_help'] = 'Select the Australian Qualifications Framework level. This determines the complexity and autonomy expected in the practical tasks.';
$string['selectaqf'] = 'Select AQF level...';
$string['occasions'] = 'Assessment Occasions';
$string['occasions_help'] = 'Number of times the student must demonstrate competency. This is determined by the unit requirements.';

$string['assessmentcontrols'] = 'Assessment Controls';
$string['autogenerate'] = 'Auto-generate scenarios and forms';
$string['autogenerate_help'] = 'When enabled, the system will automatically generate workplace scenarios, skills checklists, and workplace forms based on the unit requirements.';
$string['performanceevidence'] = 'Performance Evidence Criteria';
$string['performanceevidence_help'] = 'Enter the performance evidence criteria from the unit of competency. Each criterion should be on a new line. These will be used to generate skills checklists and workplace forms. This field is only shown when auto-generation is disabled.';
$string['requiresupervisor'] = 'Require supervisor verification';
$string['requiresupervisor_help'] = 'When enabled, students must provide workplace supervisor details. The supervisor will receive an email link to verify the student\'s work.';

$string['unitrequired'] = 'Either unit code or unit name is required';
$string['noassessments'] = 'No practical assessments in this course';
$string['status'] = 'Status';
$string['notstarted'] = 'Not started';
$string['status_draft'] = 'Draft';
$string['status_submitted'] = 'Submitted';
$string['status_supervisor_verified'] = 'Supervisor Verified';
$string['status_graded'] = 'Graded';

$string['scenario'] = 'Workplace Scenario';
$string['skillschecklist'] = 'Skills Checklist';
$string['workplaceforms'] = 'Workplace Forms';
$string['declaration'] = 'Declaration';
$string['declarationtext'] = 'I declare that this is my own work and the information provided is accurate and true.';
$string['submit'] = 'Submit Assessment';
$string['savedraft'] = 'Save Draft';
$string['draftsaved'] = 'Draft saved';
$string['autosaving'] = 'Auto-saving...';

$string['supervisordetails'] = 'Supervisor Details';
$string['supervisorname'] = 'Supervisor Name';
$string['supervisorposition'] = 'Supervisor Position';
$string['supervisoremail'] = 'Supervisor Email';
$string['supervisorphone'] = 'Supervisor Phone';
$string['signature'] = 'Signature';
$string['clearsignature'] = 'Clear Signature';

$string['supervisordeclaration'] = 'I confirm that the student completed the above tasks in the workplace and the information provided is accurate.';
$string['supervisorverification'] = 'Supervisor Verification';
$string['supervisorintro'] = 'You have been invited to verify the practical assessment submitted by {$a}.';
$string['skillsverification'] = 'Skills Verification';
$string['skillsverificationhelp'] = 'Please tick the skills you have observed the student demonstrate in the workplace.';
$string['studentcompleted'] = 'Completed by student';
$string['supervisorcomments'] = 'Comments';
$string['decision'] = 'Decision';
$string['approve'] = 'Approve';
$string['requestresubmission'] = 'Request Resubmission';
$string['thankyou'] = 'Thank You';
$string['verificationcomplete'] = 'Your verification has been recorded. The assessor will be notified.';
$string['invalidtoken'] = 'Invalid or expired verification link';

$string['grading'] = 'Grading';
$string['gradingfor'] = 'Grading submission for {$a}';
$string['assessmentoutcome'] = 'Assessment Outcome';
$string['competent'] = 'Competent';
$string['notyetcompetent'] = 'Not Yet Competent';
$string['score'] = 'Score';
$string['feedback'] = 'Feedback';
$string['savegrade'] = 'Save Grade';
$string['verifiedby'] = 'Verified by';
$string['comments'] = 'Comments';
$string['assessmentdetails'] = 'Assessment Details';

$string['submissions'] = 'Submissions';
$string['nosubmissions'] = 'No submissions yet';
$string['viewsubmission'] = 'View Submission';
$string['gradesubmission'] = 'Grade Submission';

$string['mappingmatrix'] = 'Mapping Matrix';
$string['exportmapping'] = 'Export Mapping';
$string['criterion'] = 'Criterion';
$string['task'] = 'Task';
$string['formfield'] = 'Form / Field';
$string['evidencetype'] = 'Evidence Type';
$string['assessmentmethod'] = 'Assessment Method';

$string['riskmatrix'] = 'Risk Matrix';
$string['likelihood'] = 'Likelihood';
$string['consequence'] = 'Consequence';
$string['low'] = 'Low';
$string['medium'] = 'Medium';
$string['high'] = 'High';
$string['extreme'] = 'Extreme';

$string['eventcourse_module_viewed'] = 'Practical assessment viewed';
$string['eventsubmission_created'] = 'Submission created';
$string['eventsubmission_submitted'] = 'Submission submitted';
$string['eventsubmission_graded'] = 'Submission graded';

$string['privacy:metadata:practicalassessment_submission'] = 'Student submission data for practical assessments';
$string['privacy:metadata:practicalassessment_submission:userid'] = 'The ID of the user who made the submission';
$string['privacy:metadata:practicalassessment_submission:forms_data'] = 'The form data submitted by the user';
$string['privacy:metadata:practicalassessment_submission:skills_completed'] = 'The skills marked as completed by the user';


$string['supervisoremailsubject'] = 'Practical Assessment Verification Request - {$a->studentname}';
$string['supervisoremailbody'] = 'Dear {$a->supervisorname},

{$a->studentname} has submitted a practical assessment for verification.

Assessment: {$a->assessment}
Unit: {$a->unit}

Please click the following link to verify their work:
{$a->link}

This link will allow you to review the student\'s submitted evidence and confirm whether they have demonstrated the required skills in the workplace.

Thank you for your time.

Best regards,
The Assessment Team';

$string['cachedef_tga_units'] = 'Cache for TGA unit data';

$string['select'] = 'Select...';
$string['student'] = 'Student';
$string['actions'] = 'Actions';

$string['skillsassessment'] = 'Skills Assessment';
$string['skill'] = 'Skill';
$string['occasion'] = 'Occasion';
$string['result'] = 'Result';
$string['satisfactory'] = 'Satisfactory';
$string['notyetsatisfactory'] = 'Not Yet Satisfactory';
$string['gradingpanel'] = 'Grading';
$string['feedbackplaceholder'] = 'Enter feedback...';
$string['nysfeedbackrequired'] = 'Feedback is required for all NYS items';
$string['getaisuggestion'] = 'Get AI Suggestion';
$string['notprovided'] = 'Not provided';
$string['risklevel'] = 'Risk Level';
$string['verified'] = 'Verified';
$string['pending'] = 'Pending';
$string['dateassessed'] = 'Date Assessed';
$string['assessor'] = 'Assessor';
$string['outcome'] = 'Outcome';
$string['progress'] = 'Progress';
$string['complete'] = 'Complete';
