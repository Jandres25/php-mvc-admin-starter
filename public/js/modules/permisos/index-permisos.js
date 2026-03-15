/**
 * index-permisos.js - Gestión de la página principal de permisos
 *
 * Maneja la inicialización de DataTables, el modal en modo creación
 * y el cambio de estado. La lógica del modal (edición, submit, reset)
 * está centralizada en modal-permiso.js.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permisos
 * @author Jandres25
 * @version 1.0
 */

const mensajeErrorDesactivar = 'No se puede desactivar el permiso porque hay usuarios que lo tienen asignado';

$(document).ready(function () {
    const config = createTableConfig('Permisos', [0, 1, 2, 3], {});
    const table = $("#tablaPermisos").DataTable(config);
    table.buttons().container().appendTo('#tablaPermisos_wrapper .col-md-6:eq(0)');

    // Abrir modal en modo creación
    $('#btnNuevoPermiso').on('click', function () {
        $('#formPermiso')[0].reset();
        $('#permisoAction').val('create');
        $('#idPermiso').val('');
        $('#nombre').val('');

        $('#modalPermisoHeader').removeClass('bg-warning').addClass('bg-primary');
        $('#modalPermisoLabel').text('Crear Nuevo Permiso');
        $('#btnGuardarPermiso').removeClass('btn-warning').addClass('btn-primary')
            .html('<i class="fas fa-save"></i> Guardar');

        $('#modalPermiso').modal('show');
    });

    // Cambiar estado de un permiso
    $(document).on('click', '.cambiar-estado', function () {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado-actual');
        const usuarios = $(this).data('usuarios');

        if (estadoActual == 1 && usuarios > 0) {
            showToast(mensajeErrorDesactivar, 'error');
            return;
        }

        const textoEstado = estadoActual == 1 ? 'desactivar' : 'activar';
        const textoEstadoCapitalizado = textoEstado.charAt(0).toUpperCase() + textoEstado.slice(1);

        Swal.fire({
            title: `¿${textoEstadoCapitalizado} este permiso?`,
            text: `El permiso será ${textoEstado}do.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: estadoActual == 1 ? '#d33' : '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${textoEstado}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}controllers/permisos/cambiar_estado_permiso_ajax.php`,
                    type: 'POST',
                    dataType: 'json',
                    data: { id, estado_actual: estadoActual, csrf_token: csrfToken },
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error en la comunicación con el servidor' });
                    }
                });
            }
        });
    });
});
