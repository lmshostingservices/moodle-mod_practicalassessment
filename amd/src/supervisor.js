/**
 * AI Practical Assessment - Supervisor verification module.
 *
 * @module     mod_practicalassessment/supervisor
 * @copyright  2025 AI Grader
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    return {
        init: function() {
            this.initSignature();
            this.setupFormValidation();
        },

        initSignature: function() {
            const canvas = document.getElementById('signature-canvas');
            if (!canvas) return;

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

            $('#clear-signature').on('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                $('#signature-data').val('');
            });
        },

        setupFormValidation: function() {
            $('#supervisor-form').on('submit', function() {
                const canvas = document.getElementById('signature-canvas');
                if (canvas) {
                    $('#signature-data').val(canvas.toDataURL());
                }
            });
        }
    };
});
