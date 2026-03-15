/**
 * modal-permiso.js - Lógica compartida del modal de permisos
 *
 * Maneja la apertura en modo edición, el envío del formulario por AJAX
 * y el reset al cerrar. Utilizado por index-permisos.js y detalle-permiso.js.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permisos
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {

    // Abrir modal en modo edición
    $(document).on('click', '.btn-editar', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        $('#permisoAction').val('edit');
        $('#idPermiso').val(id);
        $('#nombre').val(nombre);

        $('#modalPermisoHeader').removeClass('bg-primary').addClass('bg-warning');
        $('#modalPermisoLabel').text('Editar Permiso');
        $('#btnGuardarPermiso').removeClass('btn-primary').addClass('btn-warning')
            .html('<i class="fas fa-save"></i> Actualizar');

        $('#modalPermiso').modal('show');
    });

    // Envío del formulario (create o edit según #permisoAction)
    $('#formPermiso').on('submit', function (e) {
        e.preventDefault();

        const action = $('#permisoAction').val();
        const formData = $(this).serialize();

        let url, loadingMsg, successBtn;

        if (action === 'create') {
            url = `${baseUrl}controllers/permisos/crear_permiso_ajax.php`;
            loadingMsg = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            successBtn = '<i class="fas fa-save"></i> Guardar';
        } else {
            url = `${baseUrl}controllers/permisos/actualizar_permiso_ajax.php`;
            loadingMsg = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            successBtn = '<i class="fas fa-save"></i> Actualizar';
        }

        $.ajax({
            url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            beforeSend: function () {
                $('#btnGuardarPermiso').prop('disabled', true).html(loadingMsg);
            },
            success: function (response) {
                if (response.success) {
                    $('#modalPermiso').modal('hide');
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    $('#btnGuardarPermiso').prop('disabled', false).html(successBtn);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error en la comunicación con el servidor' });
                $('#btnGuardarPermiso').prop('disabled', false).html(successBtn);
            }
        });
    });

    // Reset al cerrar el modal
    $('#modalPermiso').on('hidden.bs.modal', function () {
        $('#formPermiso')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });
});
