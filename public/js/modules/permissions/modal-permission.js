/**
 * modal-permission.js - Shared permission modal logic
 *
 * Handles opening in edit mode, form submission via AJAX,
 * and reset on close. Used by index-permissions.js and detail-permission.js.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permissions
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {

    // Open modal in edit mode
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description') || '';

        $('#permissionAction').val('edit');
        $('#permissionId').val(id);
        $('#name').val(name);
        $('#description').val(description);

        $('#modalPermissionHeader').removeClass('bg-primary').addClass('bg-warning');
        $('#modalPermissionLabel').text('Edit Permission');
        $('#btnSavePermission').removeClass('btn-primary').addClass('btn-warning')
            .html('<i class="fas fa-save"></i> Update');

        $('#modalPermission').modal('show');
    });

    // Form submission (create or edit based on #permissionAction)
    $('#formPermission').on('submit', function (e) {
        e.preventDefault();

        const action = $('#permissionAction').val();
        const formData = $(this).serialize();

        const url = action === 'create' ? `${baseUrl}permissions/create` : `${baseUrl}permissions/update`;
        const toastMsg = action === 'create' ? 'Creating permission...' : 'Updating permission...';

        $('#modalPermission').modal('hide');

        ToastUtils.loadingWithMinTime(toastMsg, () => {
            $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.close();
                        ToastUtils.error('Error', response.message);
                    }
                },
                error: function () {
                    Swal.close();
                    ToastUtils.error('Error', 'A communication error occurred with the server.');
                }
            });
        }, 800);
    });

    // Reset on modal close
    $('#modalPermission').on('hidden.bs.modal', function () {
        $('#formPermission')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });
});
