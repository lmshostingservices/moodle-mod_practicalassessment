# Changelog - AI Practical Assessment Activity Module

All notable changes to this plugin will be documented in this file.

## [3.2.5] - 2025-12-25

### Fixed
- **Grading Ajax Error**: Fixed `stdClass` namespace error in ajax.php, added lib.php require
- **Grade Page JS Strings**: Added `$PAGE->requires->string_for_js()` for grader.js strings
- **Minified JS Rebuild**: Updated player.min.js and grader.min.js to match source files

## [3.2.4] - 2025-12-25

### Fixed
- **JavaScript Language Strings**: Added `$PAGE->requires->string_for_js()` calls to load language strings for JavaScript
- Resolves `[[draftsaved,mod_practicalassessment]]` raw string display issue
- Loads: draftsaved, autosaving, savedraft, submit strings for player.js

## [3.2.3] - 2025-12-25

### Fixed
- **Central Config Integration**: Fixed TGA API integration to properly use AI Grader Central Config (local_aiconfig)
- Now passes correct component name `mod_practicalassessment` to central config helper functions
- Resolves "Missing Site ID or API Key" error when creating practical assessments

## [3.2.2] - 2025-12-25

### Added
- **Country Selector**: Added 50-country dropdown matching Chirp 3 HD language support
- Countries include: Australia, United States, United Kingdom, Canada, New Zealand, EU countries, Asian countries, South American countries, and Middle Eastern countries

### Changed
- **State/Territory now optional**: Added empty default option so users can skip state selection for non-Australian contexts

## [3.2.1] - 2025-12-25

### Fixed
- TGA credentials moved server-side - no plugin settings needed
- Uses AI Grader Central Config or EssayGraderAI server API for TGA data
- Removed TGA username/password settings from plugin configuration

## [3.2.0] - 2025-12-23

### Added
- Tabbed grading interface (Tab 1: Workplace Forms, Tab 2: Skills Assessment)
- S/NYS result badges per criterion with mandatory feedback for NYS items
- Sticky grade summary bar with real-time progress tracking
- Support for 1-3 assessment occasions
- 8-column ASQA export format
- Student-facing progress ring visualization
- Database upgrade for grading_data storage

## [3.1.3] - 2025-12-22

### Fixed
- Fixed PHP 8.4 implicit nullable parameter deprecation warnings in add_instance and update_instance functions

## [3.1.2] - 2025-12-20

### Fixed
- Fixed upgrade function name from `xmldb_mod_practicalassessment_upgrade` to `xmldb_practicalassessment_upgrade`

## [3.1.1] - 2025-12-20

### Changed
- Migrated to centralized download architecture
- Updated versioned ZIP filename

## [3.1.0] - 2025-12-15

### Added
- Unit-driven evidence discovery
- TGA API integration
- Dynamic workplace form generation
- Skills checklist auto-generation
- 5x5 risk matrix fields
- Signature capture
- Supervisor tokenized verification
- AJAX autosave
- Mapping matrix auto-generation
- Moodle gradebook integration
- ASQA audit compliance

## [1.0.0] - 2025-06-01

### Added
- Initial release
- Workplace-based practical assessments
- Moodle 4.0+ compatibility
