/**
 * AI Practical Assessment - Student player module.
 *
 * @module     mod_practicalassessment/player
 * @copyright  2025 AI Grader
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    let cmid = 0;
    let autosaveTimer = null;
    const AUTOSAVE_DELAY = 10000;

    return {
        init: function(courseModuleId) {
            cmid = courseModuleId;
            this.setupEventListeners();
            this.loadDraft();
            this.startAutosave();
            this.initSignatureCanvases();
            this.initRiskMatrices();
        },

        setupEventListeners: function() {
            const self = this;

            $('#pa-save-draft').on('click', function(e) {
                e.preventDefault();
                self.saveDraft();
            });

            $('#pa-submit').on('click', function(e) {
                e.preventDefault();
                self.submitAssessment();
            });

            $('.pa-clear-signature').on('click', function() {
                const canvas = $(this).siblings('canvas')[0];
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                $(this).siblings('.pa-signature-data').val('');
            });
        },

        collectData: function() {
            const forms = {};
            const skills = [];

            $('.pa-form').each(function() {
                const formId = $(this).data('form-id');
                forms[formId] = {};

                $(this).find('input, textarea, select').each(function() {
                    const fieldId = $(this).data('field-id');
                    if (fieldId) {
                        if ($(this).attr('type') === 'checkbox') {
                            forms[formId][fieldId] = $(this).is(':checked') ? '1' : '';
                        } else {
                            forms[formId][fieldId] = $(this).val();
                        }
                    }
                });
            });

            $('input[name="skills[]"]:checked').each(function() {
                skills.push($(this).val());
            });

            return {
                forms: JSON.stringify(forms),
                skills: JSON.stringify(skills)
            };
        },

        saveDraft: function() {
            const data = this.collectData();
            this.saveSignatures();

            $.ajax({
                url: M.cfg.wwwroot + '/mod/practicalassessment/ajax.php',
                method: 'POST',
                data: {
                    action: 'save_draft',
                    cmid: cmid,
                    data: data.forms,
                    skills: data.skills
                },
                success: function() {
                    $('#pa-autosave').text(M.util.get_string('draftsaved', 'mod_practicalassessment') || 'Draft saved')
                        .addClass('visible');
                    setTimeout(function() {
                        $('#pa-autosave').removeClass('visible');
                    }, 2000);
                },
                error: function() {
                    Notification.addNotification({
                        message: 'Failed to save draft',
                        type: 'error'
                    });
                }
            });
        },

        loadDraft: function() {
            $.ajax({
                url: M.cfg.wwwroot + '/mod/practicalassessment/ajax.php',
                method: 'POST',
                data: {
                    action: 'load_draft',
                    cmid: cmid
                },
                success: function(response) {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;

                    if (result.data) {
                        const forms = JSON.parse(result.data);
                        Object.keys(forms).forEach(function(formId) {
                            const formData = forms[formId];
                            Object.keys(formData).forEach(function(fieldId) {
                                const $field = $('[data-field-id="' + fieldId + '"]');
                                if ($field.attr('type') === 'checkbox') {
                                    $field.prop('checked', !!formData[fieldId]);
                                } else {
                                    $field.val(formData[fieldId]);
                                }
                            });
                        });
                    }

                    if (result.skills) {
                        const skills = JSON.parse(result.skills);
                        skills.forEach(function(skillId) {
                            $('[data-skill-id="' + skillId + '"]').prop('checked', true);
                        });
                    }
                }
            });
        },

        startAutosave: function() {
            const self = this;
            autosaveTimer = setInterval(function() {
                self.saveDraft();
            }, AUTOSAVE_DELAY);
        },

        submitAssessment: function() {
            const self = this;

            if (!$('#pa-declaration').is(':checked')) {
                Notification.addNotification({
                    message: 'Please agree to the declaration before submitting.',
                    type: 'error'
                });
                return;
            }

            const data = this.collectData();
            this.saveSignatures();

            $.ajax({
                url: M.cfg.wwwroot + '/mod/practicalassessment/ajax.php',
                method: 'POST',
                data: {
                    action: 'submit',
                    cmid: cmid,
                    data: data.forms,
                    skills: data.skills,
                    declaration: 1,
                    supervisor_name: $('#supervisor_name').val() || '',
                    supervisor_email: $('#supervisor_email').val() || ''
                },
                success: function() {
                    clearInterval(autosaveTimer);
                    location.reload();
                },
                error: function() {
                    Notification.addNotification({
                        message: 'Failed to submit assessment',
                        type: 'error'
                    });
                }
            });
        },

        initSignatureCanvases: function() {
            $('.pa-signature').each(function() {
                const canvas = this;
                const ctx = canvas.getContext('2d');
                let drawing = false;

                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#000';

                canvas.addEventListener('mousedown', function(e) {
                    drawing = true;
                    ctx.beginPath();
                    ctx.moveTo(e.offsetX, e.offsetY);
                });

                canvas.addEventListener('mouseup', function() {
                    drawing = false;
                });

                canvas.addEventListener('mouseleave', function() {
                    drawing = false;
                });

                canvas.addEventListener('mousemove', function(e) {
                    if (!drawing) return;
                    ctx.lineTo(e.offsetX, e.offsetY);
                    ctx.stroke();
                });

                canvas.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    drawing = true;
                    const rect = canvas.getBoundingClientRect();
                    const touch = e.touches[0];
                    ctx.beginPath();
                    ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
                });

                canvas.addEventListener('touchend', function() {
                    drawing = false;
                });

                canvas.addEventListener('touchmove', function(e) {
                    if (!drawing) return;
                    e.preventDefault();
                    const rect = canvas.getBoundingClientRect();
                    const touch = e.touches[0];
                    ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                    ctx.stroke();
                });
            });
        },

        saveSignatures: function() {
            $('.pa-signature').each(function() {
                const dataUrl = this.toDataURL();
                $(this).siblings('.pa-signature-data').val(dataUrl);
            });
        },

        initRiskMatrices: function() {
            $(document).on('click', '.pa-risk td', function() {
                const val = $(this).data('value');
                $(this).closest('.pa-risk').find('td').removeClass('active');
                $(this).addClass('active');
                $(this).closest('div').find('.pa-risk-input').val(val);
            });
        }
    };
});
