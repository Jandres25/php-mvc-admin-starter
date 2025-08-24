/**
 * index-usuarios.js - Gestión de la página principal de usuarios
 * 
 * Maneja la inicialización de DataTables y las funciones de 
 * activación/desactivación de usuarios.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Usuarios
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('Usuarios', [0, 1, 2, 3, 4, 6, 7], {
        pageLength: 5
    });

    // Inicializar DataTable con la configuración generada
    const table = $("#tablaUsuarios").DataTable(config);

    // Mover los botones al contenedor adecuado (si es necesario)
    table.buttons().container().appendTo('#tablaUsuarios_wrapper .col-md-6:eq(0)');
});

document.addEventListener('DOMContentLoaded', function () {
    // Función para manejar el cambio de estado
    function handleEstadoChange(boton) {
        const usuarioId = boton.dataset.id;
        const estadoActual = boton.dataset.estado;
        const nombreUsuario = boton.dataset.nombre;

        const tituloAlerta = estadoActual == 1 ? `¿Desactivar a ${nombreUsuario}?` : `¿Activar a ${nombreUsuario}?`;
        const textoAlerta = estadoActual == 1 ? 'El usuario no podrá acceder al sistema.' : 'El usuario podrá acceder nuevamente al sistema.';
        const confirmButtonText = estadoActual == 1 ? 'Sí, desactivar' : 'Sí, activar';
        const cancelButtonText = 'Cancelar';

        Swal.fire({
            title: tituloAlerta,
            text: textoAlerta,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: estadoActual == 1 ? '#d33' : '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
            // Configuraciones adicionales para dispositivos móviles
            allowOutsideClick: false,
            allowEscapeKey: true,
            buttonsStyling: true,
            reverseButtons: false,
            focusConfirm: false,
            focusCancel: true,
            // Asegurar que el modal tenga el z-index correcto
            customClass: {
                container: 'swal-mobile-container'
            },
            didOpen: () => {
                // Asegurar que el modal sea accesible en móviles
                const swalContainer = document.querySelector('.swal2-container');
                if (swalContainer) {
                    swalContainer.style.zIndex = '9999';
                    swalContainer.style.position = 'fixed';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `${baseUrl}controllers/usuarios/desactivar_usuario.php?id=${usuarioId}&estado=${estadoActual}`;
            }
        });
    }

    // Usar delegación de eventos para mejor compatibilidad móvil
    document.body.addEventListener('click', function(e) {
        // Verificar si el elemento clickeado es un botón de cambiar estado
        if (e.target.classList.contains('btn-cambiar-estado') || e.target.closest('.btn-cambiar-estado')) {
            e.preventDefault();
            e.stopPropagation();
            
            const boton = e.target.classList.contains('btn-cambiar-estado') ? e.target : e.target.closest('.btn-cambiar-estado');
            handleEstadoChange(boton);
        }
    }, { capture: true, passive: false }); // Configuración optimizada para móviles

    // Agregar soporte para eventos táctiles en dispositivos móviles
    document.body.addEventListener('touchend', function(e) {
        if (e.target.classList.contains('btn-cambiar-estado') || e.target.closest('.btn-cambiar-estado')) {
            e.preventDefault();
            const boton = e.target.classList.contains('btn-cambiar-estado') ? e.target : e.target.closest('.btn-cambiar-estado');
            
            // Pequeño retraso para evitar doble evento
            setTimeout(() => {
                handleEstadoChange(boton);
            }, 100);
        }
    }, { passive: false });

    // Añadir clase para indicar que es un dispositivo táctil
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        document.body.classList.add('touch-device');
    }
});