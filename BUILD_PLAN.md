# AI Practical Assessment v3.2.0 - Complete Build Plan

## Overview
AI Practical Assessment is a workplace-based practical assessment plugin for Moodle that enables RTOs to assess competency through workplace forms, skills checklists, supervisor verification, and comprehensive mapping to training.gov.au units of competency.

---

## FEATURE SPECIFICATIONS

### FEATURE 1: Unit-Driven Evidence Discovery
**Spec:** TGA API integration to automatically fetch unit of competency data from training.gov.au
**Requirements:**
- Hybrid API approach (XML + REST + SOAP fallback)
- Extract elements, performance criteria, performance evidence, knowledge evidence
- 30-day caching for performance
- Support for WS-Security authentication (SOAP fallback)

**Expected Files:**
- `classes/tga/training_component.php` - TGA API integration class
- `db/caches.php` - Cache definitions for TGA data

---

### FEATURE 2: Dynamic Workplace Form Generation
**Spec:** Auto-generate industry-standard workplace forms based on performance evidence
**Requirements:**
- Form types: SWMS, JSA, Hazard Report, Incident Report, Toolbox Talk, Equipment Inspection, Permit to Work
- Dynamic field generation based on evidence requirements
- Support for field types: text, textarea, date, number, select, checkbox, signature, matrix_5x5

**Expected Files:**
- `classes/generator.php` - Content generation from unit data
- `classes/output/student.php` - Student form rendering

---

### FEATURE 3: Skills Checklist Auto-Generation
**Spec:** Generate skills checklist from performance criteria
**Requirements:**
- Extract skills from performance evidence
- Extract skills from element performance criteria
- Map each skill to unit criteria for audit trail

**Expected Files:**
- `classes/generator.php::derive_skills_checklist()` - Skills extraction
- `classes/output/student.php::render_skills()` - Skills rendering

---

### FEATURE 4: 5x5 Risk Matrix Fields
**Spec:** Interactive risk matrix for hazard/risk assessment forms
**Requirements:**
- 5x5 grid for likelihood × consequence
- Click to select risk level
- Visual feedback with active state
- Store value as "row-col" format (e.g., "3-4")
- Mobile-responsive with horizontal scroll

**Expected Files:**
- `classes/output/student.php::render_risk_matrix()` - Risk matrix HTML
- `amd/src/player.js::initRiskMatrices()` - Click handler
- `styles.css` - .pa-risk styling

---

### FEATURE 5: Signature Capture Canvas
**Spec:** HTML5 canvas-based signature capture
**Requirements:**
- Touch and mouse support
- 300×120 default size (responsive)
- Clear button functionality
- Store as base64 data URL
- Render captured signatures in grader view

**Expected Files:**
- `classes/output/student.php::render_field()` (signature type)
- `amd/src/player.js::initSignatureCanvases()` - Canvas handling
- `amd/src/supervisor.js::initSignature()` - Supervisor signature

---

### FEATURE 6: Supervisor Tokenized Verification
**Spec:** Third-party supervisor verification without Moodle login
**Requirements:**
- Generate unique 64-character verification token
- Email link to supervisor
- Token-based access (no authentication required)
- Skills verification checkboxes
- Signature capture
- Approve/Request resubmission decision
- Update submission status on verification

**Expected Files:**
- `supervisor.php` - Supervisor verification page
- `classes/supervisor_mailer.php` - Email sending
- `amd/src/supervisor.js` - Supervisor UI
- `db/install.xml` - practicalassessment_supervisor table

---

### FEATURE 7: AJAX Autosave
**Spec:** Automatic draft saving every 10 seconds
**Requirements:**
- 10-second interval autosave
- Collect all form data and skills
- Show "Draft saved" indicator
- Load draft on page load
- Clear autosave on submit

**Expected Files:**
- `amd/src/player.js::startAutosave()` - Autosave timer
- `amd/src/player.js::saveDraft()` - Save logic
- `amd/src/player.js::loadDraft()` - Load logic
- `ajax.php` - save_draft/load_draft actions

---

### FEATURE 8: Mapping Matrix Auto-Generation
**Spec:** Auto-generate ASQA-compliant mapping matrix
**Requirements:**
- Map unit elements → performance criteria → evidence sources
- Include form fields as document evidence
- Include skills as observation evidence
- CSV export functionality

**Expected Files:**
- `classes/generator.php::derive_mapping()` - Mapping generation
- `export.php` - CSV export

---

### FEATURE 9: Moodle Gradebook Integration
**Spec:** Grades saved to Moodle gradebook
**Requirements:**
- 0-100 scale (competency = 100, NYC = 0)
- Grade item creation on activity add
- Grade update on grading
- Support for grade reset

**Expected Files:**
- `lib.php::practicalassessment_grade_item_update()` - Grade item
- `lib.php::practicalassessment_update_grades()` - Grade sync
- `ajax.php::save_grade` - Grade save action

---

### FEATURE 10: Custom Tabbed Grading Interface
**Spec:** Two-tab grading interface for assessors
**Requirements:**
- Tab 1: Workplace Forms (student work 60% + grading columns 40%)
- Tab 2: Skills Assessment with occasion columns (1-3 occasions)
- S/NYS result badges per criterion
- Mandatory feedback for NYS items
- Sticky grade summary bar with real-time progress
- AI grading suggestions for workplace forms

**Expected Files:**
- `grade.php` - Grading page
- `classes/output/grader.php` - Grading renderer
- `amd/src/grader.js` - Grading UI

---

### FEATURE 11: ASQA Audit Compliance
**Spec:** 8-column mapping export for ASQA audits
**Requirements:**
- Columns: Criterion, Task, Form Field, Evidence Type, Assessment Method, Date, Assessor, Outcome
- CSV export with proper formatting
- Include all unit criteria
- Link evidence to criteria

**Expected Files:**
- `export.php` - Export functionality

---

### FEATURE 12: Premium SaaS CSS Design
**Spec:** Modern design aligned with lms-labs.com design system
**Requirements:**
- HSL-based color system
- Dark mode support
- Inter/JetBrains Mono fonts
- Responsive breakpoints (640px)
- 44px touch targets (mobile)
- Entry animations
- Progress ring (fixed position)
- Reduced motion support

**Expected Files:**
- `styles.css` - Complete styling (~800+ lines)

---

### FEATURE 13: Activity Completion Tracking
**Spec:** Moodle completion API integration
**Requirements:**
- View completion tracking
- Custom completion rules support
- Completion on submission
- Completion on grading

**Expected Files:**
- `lib.php::practicalassessment_supports()` - Completion feature flags
- `view.php` - Module viewed completion

---

### FEATURE 14: Privacy API (GDPR)
**Spec:** GDPR-compliant data handling
**Requirements:**
- Export user data
- Delete user data
- Describe stored data

**Expected Files:**
- `classes/privacy/provider.php` - Privacy provider

---

### FEATURE 15: Backup/Restore
**Spec:** Course backup and restore support
**Requirements:**
- Backup activity settings
- Backup submissions
- Restore to new course

**Expected Files:**
- `lib.php` - FEATURE_BACKUP_MOODLE2 support

---

## DATABASE SCHEMA

### Table: practicalassessment (Main activity)
| Field | Type | Description |
|-------|------|-------------|
| id | int(10) | Primary key |
| course | int(10) | Course ID |
| name | char(255) | Activity name |
| intro | text | Introduction |
| introformat | int(4) | Intro format |
| unitcode | char(50) | TGA unit code |
| unitname | char(255) | Unit title |
| industry | char(100) | Industry context |
| context_json | text | Context data |
| scenario_text | text | Assessment scenario |
| scenario2_text | text | Additional scenario |
| skills_json | text | Skills checklist JSON |
| forms_json | text | Workplace forms JSON |
| mapping_json | text | Mapping matrix JSON |
| checklist_json | text | Additional checklist |
| occasions | int(2) | Number of occasions (1-3) |
| requiresupervisor | int(1) | Supervisor required flag |
| grade | int(10) | Max grade (100) |
| timecreated | int(10) | Created timestamp |
| timemodified | int(10) | Modified timestamp |

### Table: practicalassessment_submission
| Field | Type | Description |
|-------|------|-------------|
| id | int(10) | Primary key |
| practicalassessmentid | int(10) | FK to activity |
| userid | int(10) | FK to user |
| status | char(20) | draft/submitted/supervisor_verified/graded |
| skills_completed | text | Completed skills JSON |
| forms_data | text | Form responses JSON |
| evidence_files | text | Evidence file references |
| declaration_agreed | int(1) | Declaration checkbox |
| supervisor_email | char(255) | Supervisor email |
| supervisor_name | char(255) | Supervisor name |
| grade | number(10,5) | Assigned grade |
| feedback | text | Assessor feedback |
| grader | int(10) | FK to grading user |
| timegraded | int(10) | Graded timestamp |
| timecreated | int(10) | Created timestamp |
| timemodified | int(10) | Modified timestamp |

### Table: practicalassessment_supervisor
| Field | Type | Description |
|-------|------|-------------|
| id | int(10) | Primary key |
| submissionid | int(10) | FK to submission |
| email | char(255) | Supervisor email |
| name | char(255) | Supervisor name |
| phone | char(50) | Phone number |
| verification_token | char(64) | Unique token |
| skills_verified | text | Verified skills JSON |
| comments | text | Supervisor comments |
| signature | text | Base64 signature |
| decision | char(20) | approved/resubmit |
| timeverified | int(10) | Verification timestamp |
| timecreated | int(10) | Created timestamp |

---

## FILE STRUCTURE

```
mod_practicalassessment/
├── amd/
│   ├── build/               # Minified AMD modules
│   └── src/
│       ├── builder.js       # Activity creation UI
│       ├── grader.js        # Assessor grading UI
│       ├── player.js        # Student assessment UI
│       └── supervisor.js    # Supervisor verification UI
├── classes/
│   ├── event/              # Moodle events
│   ├── external/           # Web service functions
│   ├── output/
│   │   ├── grader.php      # Grading renderer
│   │   ├── renderer.php    # General renderer
│   │   ├── student.php     # Student view renderer
│   │   └── supervisor.php  # Supervisor renderer
│   ├── privacy/
│   │   └── provider.php    # GDPR compliance
│   ├── tga/
│   │   └── training_component.php  # TGA API
│   ├── analytics.php       # Analytics
│   ├── generator.php       # Content generation
│   ├── grading.php         # Grading logic
│   └── supervisor_mailer.php  # Email notifications
├── db/
│   ├── access.php          # Capabilities
│   ├── caches.php          # Cache definitions
│   ├── install.xml         # Database schema
│   ├── services.php        # Web services
│   └── upgrade.php         # Upgrade steps
├── lang/en/
│   └── practicalassessment.php  # Language strings
├── pix/
│   └── icon.svg            # Activity icon
├── ajax.php                # AJAX handler
├── export.php              # Mapping export
├── grade.php               # Grading page
├── index.php               # Course listing
├── lib.php                 # Library functions
├── mod_form.php            # Activity form
├── settings.php            # Admin settings
├── styles.css              # Premium CSS
├── supervisor.php          # Supervisor verification
├── version.php             # Version info
└── view.php                # Student view
```

---

## CONNECTIONS REQUIRED

### 1. Builder → TGA API
- `builder.js` unit lookup → `ajax.php::lookup_unit` → `training_component.php::get_unit()`

### 2. Form Creation → Content Generation
- `lib.php::add_instance()` → `generator.php::generate_from_unit()` → DB storage

### 3. Student View → Form Rendering
- `view.php` → `student.php::render()` → HTML output with data attributes

### 4. Player → AJAX Autosave
- `player.js::saveDraft()` → `ajax.php::save_draft` → DB update

### 5. Submit → Supervisor Email
- `ajax.php::submit` → `supervisor_mailer.php::send_verification_request()`

### 6. Supervisor Token → Verification Page
- Email link → `supervisor.php?token=xxx` → DB update

### 7. Grading → Gradebook
- `ajax.php::save_grade` → `lib.php::practicalassessment_grade_item_update()`

### 8. Export → CSV Download
- `export.php` → `mapping_json` → CSV output

---

## VERSION HISTORY

| Version | Date | Changes |
|---------|------|---------|
| 3.2.0 | 2025-12-23 | Tabbed grading interface, S/NYS badges, sticky progress bar, 8-column ASQA export, progress ring |
| 3.1.3 | 2025-12-22 | PHP 8.4 nullable parameter fix |
| 3.1.2 | 2025-12-20 | Upgrade function name fix |
| 3.1.1 | 2025-12-20 | Centralized download architecture |
| 3.1.0 | 2025-12-15 | Complete rebuild with TGA integration |
| 1.0.0 | 2025-06-01 | Initial release |

---

## v3.2.0 IMPLEMENTATION STATUS

All HIGH and MEDIUM priority items have been implemented:

### ✅ COMPLETED (HIGH PRIORITY)
1. **Tabbed Grading Interface** - Tab 1: Workplace Forms (60/40 split), Tab 2: Skills Assessment
2. **S/NYS Badges per Criterion** - Per-criterion S/NYS buttons with visual feedback
3. **Mandatory NYS Feedback** - Validation with shake animation on missing feedback
4. **Sticky Grade Summary Bar** - Shows S/NYS/Pending counts, activates after 200px scroll
5. **AI Grading Suggestions** - Placeholder buttons (requires AI engine connection)
6. **Occasion Columns** - Now supports 1-3 occasions

### ✅ COMPLETED (MEDIUM PRIORITY)
7. **8-Column ASQA Export** - Criterion, Task, Form Field, Evidence Type, Assessment Method, Date, Assessor, Outcome
8. **Progress Ring** - Fixed position SVG indicator with tooltip in student view
9. **Success Celebration** - CSS ready (pa-success-overlay, pa-success-modal)

### 📋 FUTURE IMPROVEMENTS (LOW PRIORITY)
10. **builder.js Enhancement** - Unit lookup button UX improvements
11. **Backup/Restore Classes** - Full backup/restore implementation
