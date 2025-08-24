/**
 * detalle-permiso.js - Script para la página de detalle de permisos
 * 
 * Configura DataTables para mostrar el detalle de permisos
 * asignados a usuarios específicos.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Permisos
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    // Opción más simple: usar directamente las utilidades para configurar el idioma
    $("#detallePermisos").DataTable({
        "responsive": true,
        "autoWidth": false,
        "pageLength": 10,
        // Usar el idioma predefinido pero personalizado para usuarios
        "language": DataTableUtils.customizeLanguageFor('usuarios')
    });
});