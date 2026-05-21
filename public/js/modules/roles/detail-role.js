/**
 * Role detail — permission sync form.
 */
$(function () {
    const form = $('#formSyncPermissions');

    if (!form.length) {
        return; // system role — no form rendered
    }

    $('#select-all').on('click', function () {
        $('.perm-checkbox').prop('checked', true);
    });

    $('#deselect-all').on('click', function () {
        $('.perm-checkbox').prop('checked', false);
    });

    form.on('submit', function (e) {
        e.preventDefault();

        AlertUtils.confirm(
            'Save permissions',
            'Are you sure you want to update the permissions for this role? All users with this role will be affected.',
            function () {
                const data = form.serializeArray();
                $('#btnSavePermissions').prop('disabled', true);

                ToastUtils.loadingWithMinTime('Saving permissions...', function () {
                    $.post(baseUrl + 'roles/sync-permissions', $.param(data))
                        .done(function (res) {
                            if (res.success) {
                                location.reload();
                            } else {
                                Swal.close();
                                ToastUtils.error(res.message || 'Error saving permissions.');
                                $('#btnSavePermissions').prop('disabled', false);
                            }
                        })
                        .fail(function () {
                            Swal.close();
                            ToastUtils.error('Server error. Please try again.');
                            $('#btnSavePermissions').prop('disabled', false);
                        });
                }, 800);
            },
            {
                confirmText:  'Save',
                cancelText:   'Cancel',
                confirmColor: '#3085d6',
                cancelColor:  '#6c757d',
            }
        );
    });
});
