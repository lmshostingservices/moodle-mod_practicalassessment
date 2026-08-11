/**
 * AI Practical Assessment - Builder module for activity creation.
 *
 * @module     mod_practicalassessment/builder
 * @copyright  2025 AI Grader
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('mod_practicalassessment/builder', ['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return {
        init: function(cmid) {
            this.cmid = cmid;
            this.setupEventListeners();
        },

        setupEventListeners: function() {
            const self = this;

            $('#id_unitcode').on('blur', function() {
                const code = $(this).val().trim().toUpperCase();
                if (code.length >= 6) {
                    self.lookupUnit(code);
                }
            });

            $('#lookup-unit-btn').on('click', function(e) {
                e.preventDefault();
                const code = $('#id_unitcode').val().trim().toUpperCase();
                if (code) {
                    self.lookupUnit(code);
                }
            });
        },

        lookupUnit: function(code) {
            const self = this;

            $('#id_unitcode').parent().addClass('loading');

            $.ajax({
                url: M.cfg.wwwroot + '/mod/practicalassessment/ajax.php',
                method: 'POST',
                data: {
                    action: 'lookup_unit',
                    code: code
                },
                success: function(response) {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;

                    if (result.success && result.unit) {
                        $('#id_unitname').val(result.unit.title);

                        Notification.addNotification({
                            message: 'Unit found: ' + result.unit.title,
                            type: 'success'
                        });
                    } else {
                        Notification.addNotification({
                            message: 'Unit not found. Please enter details manually.',
                            type: 'warning'
                        });
                    }
                },
                error: function() {
                    Notification.addNotification({
                        message: 'Failed to lookup unit. Please enter details manually.',
                        type: 'error'
                    });
                },
                complete: function() {
                    $('#id_unitcode').parent().removeClass('loading');
                }
            });
        }
    };
});
