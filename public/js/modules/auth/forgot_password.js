$(document).ready(function() {
    $('.login-box').addClass('login-animation');

    $('#forgot-password-form').on('submit', function(e) {
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
    });
});
