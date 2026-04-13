$(document).ready(function() {
    $('.login-box').addClass('login-animation');

    // ============= JQUERY VALIDATE =============

    $('#forgot-password-form').validate({
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            email: {
                required: "Please enter your email address",
                email: "Please enter a valid email address"
            }
        },
        submitHandler: function(form) {
            // Disable button and show spinner
            $('#btn-request').prop('disabled', true);
            $('#btn-icon').removeClass('fa-envelope').addClass('fa-spinner fa-spin');
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                title: 'Sending request...',
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
