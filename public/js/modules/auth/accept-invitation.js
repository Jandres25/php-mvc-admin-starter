/**
 * accept-invitation.js — Standalone page to accept an invitation and set password.
 */

$(document).ready(function () {
    $('#accept-invitation-form').validate({
        rules: {
            password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                minlength: 8,
                equalTo: '#password'
            }
        },
        messages: {
            password: {
                required: 'Password is required.',
                minlength: 'Password must be at least 8 characters.'
            },
            confirm_password: {
                required: 'Please confirm your password.',
                minlength: 'Password must be at least 8 characters.',
                equalTo: 'Passwords do not match.'
            }
        },
        submitHandler: function (form) {
            const $btn = $('#btn-accept');
            $btn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Activating...');
            form.submit();
        }
    });

    $('#togglePassword').on('click', function () {
        const field    = $('#password');
        const icon     = $(this).find('i');
        const isHidden = field.attr('type') === 'password';
        field.attr('type', isHidden ? 'text' : 'password');
        icon.toggleClass('fa-eye', !isHidden).toggleClass('fa-eye-slash', isHidden);
    });

    $('#toggleConfirmPassword').on('click', function () {
        const field    = $('#confirm_password');
        const icon     = $(this).find('i');
        const isHidden = field.attr('type') === 'password';
        field.attr('type', isHidden ? 'text' : 'password');
        icon.toggleClass('fa-eye', !isHidden).toggleClass('fa-eye-slash', isHidden);
    });
});
