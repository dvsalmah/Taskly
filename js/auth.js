$(document).ready(function () {

    function initPasswordToggle() {
        $('input[type="password"]').each(function () {
            var $input = $(this);
            var $group = $input.closest('.input-group');

            if ($group.length && $group.find('.toggle-pw').length === 0) {

                var $btn = $('<button>', {
                    type: 'button',
                    class: 'toggle-pw',
                    'aria-label': 'Toggle password visibility',
                    title: 'Show / Hide password',
                    html: svgEyeOpen()
                });

                $group.addClass('has-toggle');
                $group.append($btn);
                $btn.on('click', function () {
                    var isHidden = $input.attr('type') === 'password';
                    $input.attr('type', isHidden ? 'text' : 'password');
                    $btn.html(isHidden ? svgEyeOff() : svgEyeOpen());
                    $input.trigger('focus');
                });
            }
        });
    }

    function svgEyeOpen() {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" ' +
            'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>' +
            '<circle cx="12" cy="12" r="3"/>' +
            '</svg>';
    }

    function svgEyeOff() {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" ' +
            'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>' +
            '<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>' +
            '<line x1="1" y1="1" x2="23" y2="23"/>' +
            '</svg>';
    }

    initPasswordToggle();

    var $form = $('form.auth-form');

    if ($('input[name="confirm_password"]').length) {
        function showError($input, errorMsg) {
            var $group = $input.closest('.input-group');
            $group.nextAll('.field-feedback').remove();

            if (errorMsg) {
                $group.addClass('is-invalid').removeClass('is-valid');
                $('<span class="field-feedback field-error">' + errorMsg + '</span>').insertAfter($group);
            } else {
                $group.removeClass('is-invalid is-valid');
            }
        }

        function clearFeedback($input) {
            var $group = $input.closest('.input-group');
            $group.removeClass('is-invalid is-valid');
            $group.nextAll('.field-feedback').remove();
        }

        function validateName($input) {
            var val = $.trim($input.val());
            if (!val) return showError($input, 'This field is required.');
            if (val.length < 2) return showError($input, 'Minimum 2 characters.');
            showError($input, '');
        }

        function validateUsername($input) {
            var val = $.trim($input.val());
            if (!val) return showError($input, 'Username is required.');
            if (val.length < 3) return showError($input, 'Minimum 3 characters.');
            if (!/^[a-zA-Z0-9_]+$/.test(val)) return showError($input, 'Only letters, numbers, and underscores.');
            showError($input, '');
        }

        function validateEmail($input) {
            var val = $.trim($input.val());
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!val) return showError($input, 'Email is required.');
            if (!re.test(val)) return showError($input, 'Enter a valid email address.');
            showError($input, '');
        }

        function validatePassword($input) {
            var val = $input.val();
            if (!val) return showError($input, 'Password is required.');
            if (val.length < 6) return showError($input, 'Minimum 6 characters.');

            var strength = 0;
            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            var labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            var colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#2ecc71'];

            var $group = $input.closest('.input-group');
            $group.nextAll('.field-feedback').remove();

            if (strength === 4) {
                $group.removeClass('is-invalid is-valid');
            } else {
                $group.removeClass('is-invalid').addClass('is-valid');
                $('<span class="field-feedback field-strength" style="color:' + colors[strength] + '">' +
                    '● Strength: ' + labels[strength] +
                    '</span>').insertAfter($group);
            }
        }

        function validateConfirm($input) {
            var val = $input.val();
            var pwVal = $('input[name="password"]').val();
            if (!val) return showError($input, 'Please confirm your password.');
            if (val !== pwVal) return showError($input, 'Passwords do not match.');
            showError($input, '');
        }

        $('input[name="first_name"]').on('input blur', function () { validateName($(this)); });
        $('input[name="last_name"]').on('input blur', function () { validateName($(this)); });
        $('input[name="username"]').on('input blur', function () { validateUsername($(this)); });
        $('input[name="email"]').on('input blur', function () { validateEmail($(this)); });
        $('input[name="password"]').on('input blur', function () {
            validatePassword($(this));
            if ($('input[name="confirm_password"]').val()) {
                validateConfirm($('input[name="confirm_password"]'));
            }
        });
        $('input[name="confirm_password"]').on('input blur', function () { validateConfirm($(this)); });

        $form.on('submit', function (e) {
            validateName($('input[name="first_name"]'));
            validateName($('input[name="last_name"]'));
            validateUsername($('input[name="username"]'));
            validateEmail($('input[name="email"]'));
            validatePassword($('input[name="password"]'));
            validateConfirm($('input[name="confirm_password"]'));

            if ($form.find('.input-group.is-invalid').length > 0) {
                e.preventDefault();
                var $firstError = $form.find('.input-group.is-invalid').first();
                $('html, body').animate({ scrollTop: $firstError.offset().top - 80 }, 300);
            }
        });

    }

});