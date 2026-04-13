/**
 * index-users.js - User list page management
 *
 * Handles DataTables initialization and user status toggle functions.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Users
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('Users', [0, 1, 2, 3, 4, 6, 7], {
        pageLength: 5
    });

    const table = $("#tableUsers").DataTable(config);
    table.buttons().container().appendTo('#tableUsers_wrapper .col-md-6:eq(0)');
});

document.addEventListener('DOMContentLoaded', function () {
    function handleStatusToggle(button) {
        const userId = button.dataset.id;
        const currentStatus = button.dataset.status;
        const userName = button.dataset.name;

        const alertTitle = currentStatus == 1 ? `Deactivate ${userName}?` : `Activate ${userName}?`;
        const alertText = currentStatus == 1 ? 'The user will not be able to access the system.' : 'The user will be able to access the system again.';
        const confirmText = currentStatus == 1 ? 'Yes, deactivate' : 'Yes, activate';

        Swal.fire({
            title: alertTitle,
            text: alertText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: currentStatus == 1 ? '#d33' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            allowEscapeKey: true,
            buttonsStyling: true,
            reverseButtons: false,
            focusConfirm: false,
            focusCancel: true,
            customClass: {
                container: 'swal-mobile-container'
            },
            didOpen: () => {
                const swalContainer = document.querySelector('.swal2-container');
                if (swalContainer) {
                    swalContainer.style.zIndex = '9999';
                    swalContainer.style.position = 'fixed';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}controllers/users/toggle_user_status.php`,
                    type: 'POST',
                    dataType: 'json',
                    data: { id: userId, current_status: currentStatus, csrf_token: csrfToken },
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'A communication error occurred with the server.' });
                    }
                });
            }
        });
    }

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-toggle-status') || e.target.closest('.btn-toggle-status')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.classList.contains('btn-toggle-status') ? e.target : e.target.closest('.btn-toggle-status');
            handleStatusToggle(button);
        }
    }, { capture: true, passive: false });

    document.body.addEventListener('touchend', function (e) {
        if (e.target.classList.contains('btn-toggle-status') || e.target.closest('.btn-toggle-status')) {
            e.preventDefault();
            const button = e.target.classList.contains('btn-toggle-status') ? e.target : e.target.closest('.btn-toggle-status');
            setTimeout(() => { handleStatusToggle(button); }, 100);
        }
    }, { passive: false });

    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        document.body.classList.add('touch-device');
    }
});
