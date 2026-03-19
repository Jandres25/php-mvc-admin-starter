/**
 * detalle-permiso.js - Página de detalle de un permiso
 *
 * Inicializa el DataTable de usuarios con este permiso y gestiona
 * la asignación/revocación de usuarios desde esta vista.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permisos
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    // Inicializar Select2 del modal con dropdownParent para evitar conflicto con focus trap de Bootstrap
    initializeSelect2('#selectUsuario', {
        dropdownParent: $('#modalAsignarUsuario'),
        allowClear: true,
        placeholder: 'Buscar usuario...'
    });

    const config = createTableConfig('usuarios', [0, 1, 2, 3], {
        "pageLength": 10,
        "language": {
            "sEmptyTable": "Ningún usuario tiene asignado este permiso"
        }
    });

    const table = $("#detallePermisos").DataTable(config);
    table.buttons().container().appendTo('#detallePermisos_wrapper .col-md-6:eq(0)');

    // Abrir modal de asignación
    $('#btnAsignarUsuario').on('click', function () {
        $('#selectUsuario').val(null).trigger('change');
        $('#modalAsignarUsuario').modal('show');
    });

    // Confirmar asignación
    $('#btnConfirmarAsignacion').on('click', function () {
        const idusuario = $('#selectUsuario').val();
        if (!idusuario) {
            Swal.fire({ icon: 'warning', title: 'Selecciona un usuario', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Asignando...');

        $.ajax({
            url: `${baseUrl}controllers/permisos/asignar_usuario_permiso_ajax.php`,
            type: 'POST',
            dataType: 'json',
            data: { idusuario, idpermiso: idPermiso, csrf_token: csrfToken },
            success: function (response) {
                if (response.success) {
                    $('#modalAsignarUsuario').modal('hide');
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    $btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Asignar');
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor' });
                $btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Asignar');
            }
        });
    });

    // Reset al cerrar el modal
    $('#modalAsignarUsuario').on('hidden.bs.modal', function () {
        $('#btnConfirmarAsignacion').prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> Asignar');
    });

    // Revocar usuario
    $(document).on('click', '.btn-revocar', function () {
        const idusuario = $(this).data('idusuario');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Revocar permiso?',
            html: `Se quitará este permiso a <b>${nombre}</b>.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, revocar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `${baseUrl}controllers/permisos/revocar_usuario_permiso_ajax.php`,
                type: 'POST',
                dataType: 'json',
                data: { idusuario, idpermiso: idPermiso, csrf_token: csrfToken },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor' });
                }
            });
        });
    });
});
