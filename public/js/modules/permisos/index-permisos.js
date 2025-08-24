/**
 * index-permisos.js - Gestión de la página principal de permisos
 * 
 * Maneja la inicialización de DataTables y las operaciones CRUD
 * de permisos mediante modales y AJAX.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permisos
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    // Usar la utilidad para crear la configuración de DataTables
    const config = createTableConfig('Permisos', [0, 1, 2, 3], {
        // Opciones específicas adicionales si son necesarias
    });

    // Inicializar DataTable con la configuración generada
    const table = $("#tablaPermisos").DataTable(config);

    // Mover los botones al contenedor adecuado
    table.buttons().container().appendTo('#tablaPermisos_wrapper .col-md-6:eq(0)');

    // Botón para crear nuevo permiso
    $('#btnNuevoPermiso').on('click', function () {
        // Limpiar formulario
        $('#formPermiso')[0].reset();

        // Configurar para creación
        $('#permisoAction').val('create');
        $('#idPermiso').val('');
        $('#nombre').val('');

        // Cambiar apariencia del modal
        $('#modalPermisoHeader').removeClass('bg-warning').addClass('bg-primary');
        $('#modalPermisoLabel').text('Crear Nuevo Permiso');
        $('#btnGuardarPermiso').removeClass('btn-warning').addClass('btn-primary');
        $('#btnGuardarPermiso').html('<i class="fas fa-save"></i> Guardar');

        // Mostrar modal
        $('#modalPermiso').modal('show');
    });

    // Botón para editar permiso
    $(document).on('click', '.btn-editar', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        // Configurar para edición
        $('#permisoAction').val('edit');
        $('#idPermiso').val(id);
        $('#nombre').val(nombre);

        // Cambiar apariencia del modal
        $('#modalPermisoHeader').removeClass('bg-primary').addClass('bg-warning');
        $('#modalPermisoLabel').text('Editar Permiso');
        $('#btnGuardarPermiso').removeClass('btn-primary').addClass('btn-warning');
        $('#btnGuardarPermiso').html('<i class="fas fa-save"></i> Actualizar');

        // Mostrar modal
        $('#modalPermiso').modal('show');
    });

    // Procesar formulario
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
            url: url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            beforeSend: function () {
                $('#btnGuardarPermiso').prop('disabled', true).html(loadingMsg);
            },
            success: function (response) {
                if (response.success) {
                    // Cerrar modal y mostrar mensaje de éxito
                    $('#modalPermiso').modal('hide');

                    // Recargar la página para mostrar el mensaje con mensajes.php
                    location.reload();
                } else {
                    // Mostrar mensaje de error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                    $('#btnGuardarPermiso').prop('disabled', false).html(successBtn);
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error en la comunicación con el servidor'
                });
                $('#btnGuardarPermiso').prop('disabled', false).html(successBtn);
            }
        });
    });

    // Manejar cambio de estado
    $(document).on('click', '.cambiar-estado', function () {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado-actual');
        const usuarios = $(this).data('usuarios');

        // Validar si hay usuarios antes de desactivar
        if (estadoActual == 1 && usuarios > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensajeErrorDesactivar
            });
            return;
        }

        const nuevoEstado = estadoActual == 1 ? 0 : 1;
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
                    data: {
                        id: id,
                        estado_actual: estadoActual
                    },
                    success: function (response) {
                        if (response.success) {
                            // Recargar la página para mostrar el mensaje con mensajes.php
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error en la comunicación con el servidor'
                        });
                    }
                });
            }
        });
    });

    // Limpiar modal al cerrarlo
    $('#modalPermiso').on('hidden.bs.modal', function () {
        $('#formPermiso')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });
});