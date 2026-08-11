/**
 * AI Practical Assessment - Grader module v3.2.0.
 * 
 * Features:
 * - Tabbed interface (Forms / Skills)
 * - S/NYS badges with real-time stats
 * - Mandatory NYS feedback validation
 * - Sticky summary bar progress tracking
 * - AI grading suggestions
 *
 * @module     mod_practicalassessment/grader
 * @copyright  2025 AI Grader
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('mod_practicalassessment/grader', ['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    let cmid = 0;
    let submissionId = 0;
    let occasions = 1;
    let gradingData = {
        skills: {},
        forms: {}
    };

    return {
        init: function(courseModuleId, subId, occasionCount) {
            cmid = courseModuleId;
            submissionId = subId;
            occasions = occasionCount || 1;
            this.setupEventListeners();
            this.updateStats();
        },

        setupEventListeners: function() {
            const self = this;

            $('.pa-tab').on('click', function() {
                self.switchTab($(this).data('tab'));
            });

            $(document).on('click', '.pa-snys-btn', function() {
                self.handleSnysClick($(this));
            });

            $(document).on('input', '.pa-skill-feedback, .pa-form-feedback', function() {
                self.handleFeedbackChange($(this));
            });

            $(document).on('click', '.pa-ai-suggest-btn', function() {
                self.requestAiSuggestion($(this).data('form-id'));
            });

            $('#pa-outcome').on('change', function() {
                const outcome = $(this).val();
                if (outcome === 'C') {
                    $('#pa-score').val(100);
                } else if (outcome === 'NYC') {
                    $('#pa-score').val(0);
                }
            });

            $('#pa-save-grade').on('click', function(e) {
                e.preventDefault();
                self.saveGrade();
            });

            $(window).on('scroll', function() {
                self.handleStickyBar();
            });
        },

        switchTab: function(tabId) {
            $('.pa-tab').removeClass('pa-tab-active');
            $('.pa-tab[data-tab="' + tabId + '"]').addClass('pa-tab-active');

            $('.pa-tab-content').hide();
            $('#pa-tab-' + tabId).show();
        },

        handleSnysClick: function($btn) {
            const $container = $btn.closest('.pa-snys-selector');
            const value = $btn.data('value');
            const skillId = $container.data('skill-id');

            $container.find('.pa-snys-btn').removeClass('pa-snys-selected');
            $btn.addClass('pa-snys-selected');
            $container.find('.pa-snys-value').val(value);

            if (skillId.startsWith('form_')) {
                gradingData.forms[skillId] = {
                    result: value,
                    feedback: $container.closest('.pa-form-grading-column').find('.pa-form-feedback').val()
                };
            } else {
                gradingData.skills[skillId] = {
                    result: value,
                    feedback: $container.closest('tr').find('.pa-skill-feedback').val()
                };
            }

            if (value === 'NYS') {
                this.showNysFeedbackRequired($container);
            } else {
                this.hideNysFeedbackRequired($container);
            }

            this.updateStats();
        },

        handleFeedbackChange: function($input) {
            const skillId = $input.data('skill-id') || $input.data('form-id');
            const isForm = !!$input.data('form-id');
            const $row = $input.closest(isForm ? '.pa-form-grading-column' : 'tr');
            const result = $row.find('.pa-snys-value').val();

            if (isForm) {
                gradingData.forms['form_' + skillId] = {
                    result: result,
                    feedback: $input.val()
                };
            } else {
                gradingData.skills[skillId] = {
                    result: result,
                    feedback: $input.val()
                };
            }
        },

        showNysFeedbackRequired: function($container) {
            const $row = $container.closest('tr, .pa-form-grading-column');
            const $feedback = $row.find('.pa-skill-feedback, .pa-form-feedback');
            $feedback.addClass('pa-feedback-required');
            $feedback.attr('placeholder', 'Feedback required for NYS');
        },

        hideNysFeedbackRequired: function($container) {
            const $row = $container.closest('tr, .pa-form-grading-column');
            const $feedback = $row.find('.pa-skill-feedback, .pa-form-feedback');
            $feedback.removeClass('pa-feedback-required');
        },

        updateStats: function() {
            let sCount = 0;
            let nysCount = 0;
            let pendingCount = 0;
            let total = 0;

            $('.pa-snys-value').each(function() {
                total++;
                const val = $(this).val();
                if (val === 'S') {
                    sCount++;
                } else if (val === 'NYS') {
                    nysCount++;
                } else {
                    pendingCount++;
                }
            });

            $('#pa-stat-s').text(sCount);
            $('#pa-stat-nys').text(nysCount);
            $('#pa-stat-pending').text(pendingCount);

            const graded = sCount + nysCount;
            const percent = total > 0 ? Math.round((graded / total) * 100) : 0;
            
            $('#pa-progress-fill').css('width', percent + '%');
            if (percent === 100) {
                $('#pa-progress-fill').addClass('success');
            } else {
                $('#pa-progress-fill').removeClass('success');
            }
            $('#pa-progress-percent').text(percent + '%');
        },

        handleStickyBar: function() {
            const $bar = $('#pa-summary-bar');
            const scrollTop = $(window).scrollTop();
            
            if (scrollTop > 200) {
                $bar.addClass('pa-sticky-active');
            } else {
                $bar.removeClass('pa-sticky-active');
            }
        },

        requestAiSuggestion: function(formId) {
            const $btn = $('.pa-ai-suggest-btn[data-form-id="' + formId + '"]');
            const $text = $('#pa-ai-text-' + formId);
            
            $btn.prop('disabled', true).text('Generating...');
            
            setTimeout(function() {
                $text.html('<em>AI suggestions require credits. Connect to AI engine to enable.</em>');
                $btn.prop('disabled', false).html('<span class="pa-ai-icon"></span> Get AI Suggestion');
            }, 1000);
        },

        validateNysFeedback: function() {
            let valid = true;
            let missingFeedback = [];

            $('.pa-snys-value').each(function() {
                if ($(this).val() === 'NYS') {
                    const $row = $(this).closest('tr, .pa-form-grading-column');
                    const feedback = $row.find('.pa-skill-feedback, .pa-form-feedback').val();
                    
                    if (!feedback || feedback.trim() === '') {
                        valid = false;
                        $row.find('.pa-skill-feedback, .pa-form-feedback').addClass('pa-feedback-error');
                        missingFeedback.push($row.find('.pa-skill-desc').text() || 'Form item');
                    }
                }
            });

            if (!valid) {
                $('#pa-nys-notice').removeClass('hidden').addClass('pa-show');
                $('#pa-nys-warning').text(missingFeedback.length + ' NYS items require feedback');
            } else {
                $('#pa-nys-notice').addClass('hidden').removeClass('pa-show');
            }

            return valid;
        },

        saveGrade: function() {
            const self = this;
            const outcome = $('#pa-outcome').val();
            const score = $('#pa-score').val();
            const feedback = $('#pa-feedback').val();

            if (!outcome) {
                Notification.addNotification({
                    message: 'Please select an outcome',
                    type: 'error'
                });
                return;
            }

            if (!this.validateNysFeedback()) {
                Notification.addNotification({
                    message: 'Please provide feedback for all NYS items',
                    type: 'error'
                });
                return;
            }

            const $btn = $('#pa-save-grade');
            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: M.cfg.wwwroot + '/mod/practicalassessment/ajax.php',
                method: 'POST',
                data: {
                    action: 'save_grade',
                    submissionid: submissionId,
                    outcome: outcome,
                    score: score,
                    feedback: feedback,
                    grading_data: JSON.stringify(gradingData)
                },
                success: function(response) {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        Notification.addNotification({
                            message: 'Grade saved successfully',
                            type: 'success'
                        });
                        setTimeout(function() {
                            window.location.href = M.cfg.wwwroot + '/mod/practicalassessment/view.php?id=' + cmid;
                        }, 1500);
                    } else {
                        $btn.prop('disabled', false).text('Save Grade');
                        Notification.addNotification({
                            message: result.error || 'Failed to save grade',
                            type: 'error'
                        });
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Save Grade');
                    Notification.addNotification({
                        message: 'Failed to save grade',
                        type: 'error'
                    });
                }
            });
        }
    };
});
