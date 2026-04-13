$(document).ready(function() {
    $('.login-box').addClass('login-animation');

    // ============= JQUERY VALIDATE =============

    $('#reset-password-form').validate({
        rules: {
            password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                minlength: 8,
                equalTo: "#password"
            }
        },
        messages: {
            password: {
                required: "Please enter a new password",
                minlength: "Password must be at least 8 characters"
            },
            confirm_password: {
                required: "Please confirm your password",
                minlength: "Password must be at least 8 characters",
                equalTo: "Passwords do not match"
            }
        },
        submitHandler: function(form) {
            // Disable button and show spinner
            $('#btn-reset').prop('disabled', true);
            $('#btn-icon').removeClass('fa-lock').addClass('fa-spinner fa-spin');

            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: 'Updating password...',
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
