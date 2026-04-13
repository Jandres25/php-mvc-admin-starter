$(document).ready(function() {
    // ============= UI INTERACTION =============
    
    $('#toggle-password').click(function() {
        const passwordField = $('#password-field');
        const passwordFieldType = passwordField.attr('type');

        if (passwordFieldType === 'password') {
            passwordField.attr('type', 'text');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    $('.login-box').addClass('login-animation');

    // ============= JQUERY VALIDATE =============

    $('#login-form').validate({
        rules: {
            identifier: {
                required: true,
                minlength: 3
            },
            password: {
                required: true,
                minlength: 6
            }
        },
        messages: {
            identifier: {
                required: "Please enter your email or document number",
                minlength: "Identifier must be at least 3 characters"
            },
            password: {
                required: "Please enter your password",
                minlength: "Password must be at least 6 characters"
            }
        },
        submitHandler: function(form) {
            // Disable button and show spinner
            $('#btn-login').prop('disabled', true);
            $('#btn-icon').removeClass('fa-sign-in-alt').addClass('fa-spinner fa-spin');

            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: 'Signing in...',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            setTimeout(() => {
                form.submit();
            }, 800);
        }
    });
});
