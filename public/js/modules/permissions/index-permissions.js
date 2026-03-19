/**
 * index-permissions.js - Permission list page management
 *
 * Handles DataTables initialization, opening the modal in create mode,
 * and status toggling. Modal logic (edit, submit, reset) is centralized
 * in modal-permission.js.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permissions
 * @author Jandres25
 * @version 1.0
 */

const errorDeactivateMessage = 'Cannot deactivate the permission because there are users assigned to it.';

$(document).ready(function () {
    const config = createTableConfig('Permissions', [0, 1, 2, 3], {});
    const table  = $("#tablePermissions").DataTable(config);
    table.buttons().container().appendTo('#tablePermissions_wrapper .col-md-6:eq(0)');

    // Open modal in create mode
    $('#btnNewPermission').on('click', function () {
        $('#formPermission')[0].reset();
        $('#permissionAction').val('create');
        $('#permissionId').val('');
        $('#name').val('');

        $('#modalPermissionHeader').removeClass('bg-warning').addClass('bg-primary');
        $('#modalPermissionLabel').text('Create New Permission');
        $('#btnSavePermission').removeClass('btn-warning').addClass('btn-primary')
            .html('<i class="fas fa-save"></i> Save');

        $('#modalPermission').modal('show');
    });

    // Toggle permission status
    $(document).on('click', '.btn-toggle-status', function () {
        const id            = $(this).data('id');
        const currentStatus = $(this).data('current-status');
        const users         = $(this).data('users');

        if (currentStatus == 1 && users > 0) {
            showToast(errorDeactivateMessage, 'error');
            return;
        }

        const action            = currentStatus == 1 ? 'deactivate' : 'activate';
        const actionCapitalized = action.charAt(0).toUpperCase() + action.slice(1);

        Swal.fire({
            title: `${actionCapitalized} this permission?`,
            text: `The permission will be ${action}d.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: currentStatus == 1 ? '#d33' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action}`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}controllers/permissions/toggle_permission_status_ajax.php`,
                    type: 'POST',
                    dataType: 'json',
                    data: { id, current_status: currentStatus, csrf_token: csrfToken },
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
    });
});
