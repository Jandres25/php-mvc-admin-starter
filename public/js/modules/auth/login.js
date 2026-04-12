$(document).ready(function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

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

    $('#login-form').on('submit', function(e) {
        e.preventDefault();

        const identifier = $('input[name="identifier"]').val().trim();
        const password = $('input[name="password"]').val().trim();
        let isValid = true;

        if (!identifier) {
            $('input[name="identifier"]').addClass('is-invalid');
            isValid = false;
        } else {
            $('input[name="identifier"]').removeClass('is-invalid');
        }

        if (!password) {
            $('input[name="password"]').addClass('is-invalid');
            isValid = false;
        } else {
            $('input[name="password"]').removeClass('is-invalid');
        }

        if (!isValid) {
            Toast.fire({
                icon: 'error',
                title: 'Please fill in all fields.'
            });
            return;
        }

        if (password.length < 6) {
            $('input[name="password"]').addClass('is-invalid');
            Toast.fire({
                icon: 'error',
                title: 'Password must be at least 6 characters.'
            });
            return;
        }

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
            this.submit();
        }, 1000);
    });

    $('input').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
