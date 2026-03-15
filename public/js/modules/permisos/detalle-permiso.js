/**
 * detalle-permiso.js - Página de detalle de un permiso
 *
 * Inicializa el DataTable de usuarios con este permiso.
 * La lógica del modal de edición está centralizada en modal-permiso.js.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permisos
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    const config = createTableConfig('usuarios', [0, 1, 2, 3], {
        "pageLength": 10,
        "language": {
            "sEmptyTable": "Ningún usuario tiene asignado este permiso"
        }
    });

    const table = $("#detallePermisos").DataTable(config);
    table.buttons().container().appendTo('#detallePermisos_wrapper .col-md-6:eq(0)');
});
