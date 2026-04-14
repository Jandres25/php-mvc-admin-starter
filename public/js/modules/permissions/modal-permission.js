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

        let url, loadingMsg, successBtn;

        if (action === 'create') {
            url = `${baseUrl}app/controllers/permissions/create_permission_ajax.php`;
            loadingMsg = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            successBtn = '<i class="fas fa-save"></i> Save';
        } else {
            url = `${baseUrl}app/controllers/permissions/update_permission_ajax.php`;
            loadingMsg = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            successBtn = '<i class="fas fa-save"></i> Update';
        }

        $.ajax({
            url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            beforeSend: function () {
                $('#btnSavePermission').prop('disabled', true).html(loadingMsg);
            },
            success: function (response) {
                if (response.success) {
                    $('#modalPermission').modal('hide');
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    $('#btnSavePermission').prop('disabled', false).html(successBtn);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'A communication error occurred with the server.' });
                $('#btnSavePermission').prop('disabled', false).html(successBtn);
            }
        });
    });

    // Reset on modal close
    $('#modalPermission').on('hidden.bs.modal', function () {
        $('#formPermission')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });
});
