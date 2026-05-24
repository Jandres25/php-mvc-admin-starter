/**
 * show-user.js - User detail page module
 */

// Restore last active tab
var lastTab = localStorage.getItem('lastUserDetailTab');
if (lastTab && $('a[href="' + lastTab + '"]').length) {
    $('a[href="' + lastTab + '"]').tab('show');
}

// Save active tab on change
$('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
    localStorage.setItem('lastUserDetailTab', $(e.target).attr('href'));
});

// Show enlarged image on click
$('.profile-user-img').on('click', function () {
    AlertUtils.image($(this).attr('src'), 'Profile image');
});

// Unlock login — manual admin action
$('#btn-unlock-login').on('click', function () {
    const $btn = $(this);
    const url = $btn.data('url');
    const csrf = $btn.data('csrf');

    AlertUtils.confirm(
        '¿Desbloquear acceso?',
        'El usuario podrá iniciar sesión inmediatamente.',
        function () {
            ToastUtils.loadingWithMinTime('Desbloqueando usuario...', () => {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { csrf_token: csrf },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            Swal.close();
                            ToastUtils.error(res.message);
                        }
                    },
                    error: function () {
                        Swal.close();
                        ToastUtils.error('Error al comunicarse con el servidor.');
                    }
                });
            });
        }
    );
});
