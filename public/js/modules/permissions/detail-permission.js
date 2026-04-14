/**
 * detail-permission.js - Permission detail page
 *
 * Initializes the DataTable of users with this permission and manages
 * user assignment/revocation from this view.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permissions
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('Users', [0, 1, 2, 3], {
        "pageLength": 10,
        "language": {
            "sEmptyTable": "No users have been assigned this permission."
        }
    });

    const table = $("#tablePermissionDetail").DataTable(config);
    table.buttons().container().appendTo('#tablePermissionDetail_wrapper .col-md-6:eq(0)');

    // Open assign modal
    $('#btnAssignUser').on('click', function () {
        $('#selectUser').val(null).trigger('change');
        $('#modalAssignUser').modal('show');
    });

    // Confirm assignment
    $('#btnConfirmAssign').on('click', function () {
        const userId = $('#selectUser').val();
        if (!userId) {
            Swal.fire({ icon: 'warning', title: 'Select a user', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Assigning...');

        $.ajax({
            url: `${baseUrl}app/controllers/permissions/assign_user_permission_ajax.php`,
            type: 'POST',
            dataType: 'json',
            data: { user_id: userId, permission_id: permissionId, csrf_token: csrfToken },
            success: function (response) {
                if (response.success) {
                    $('#modalAssignUser').modal('hide');
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    $btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Assign');
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'A communication error occurred with the server.' });
                $btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Assign');
            }
        });
    });

    // Reset on modal close
    $('#modalAssignUser').on('hidden.bs.modal', function () {
        $('#btnConfirmAssign').prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Assign');
    });

    // Revoke user
    $(document).on('click', '.btn-revoke', function () {
        const userId = $(this).data('user-id');
        const name = $(this).data('name');
        const $btn = $(this);

        Swal.fire({
            title: 'Revoke permission?',
            html: `This permission will be removed from <b>${name}</b>.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, revoke',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true);

            $.ajax({
                url: `${baseUrl}app/controllers/permissions/revoke_user_permission_ajax.php`,
                type: 'POST',
                dataType: 'json',
                data: { user_id: userId, permission_id: permissionId, csrf_token: csrfToken },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        $btn.prop('disabled', false);
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'A communication error occurred with the server.' });
                    $btn.prop('disabled', false);
                }
            });
        });
    });
});
