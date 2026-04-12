$(document).ready(function() {
    $('.login-box').addClass('login-animation');

    $('#reset-password-form').on('submit', function(e) {
        const password = $('#password').val();
        const confirm = $('#confirm_password').val();

        if (password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Passwords do not match!'
            });
            return;
        }

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
    });
});
