<?php

/**
 * Usuarios sin permiso (AJAX)
 *
 * Devuelve la lista de usuarios activos que no tienen asignado un permiso específico,
 * formateados para Select2.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Permisos
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

// Verificar si es una solicitud AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit('Acceso no permitido');
}

requireLogin();

$authService = new \Services\AuthorizationService();
if (!$authService->tienePermisoNombre($_SESSION['usuario_id'], 'permisos')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción']);
    exit;
}

header('Content-Type: application/json');

$idPermiso = filter_var($_GET['idpermiso'] ?? 0, FILTER_VALIDATE_INT);

if (!$idPermiso) {
    echo json_encode([]);
    exit;
}

$controller = new \Controllers\Permisos\PermisoController();
$usuarios = $controller->getUsuariosSinPermiso($idPermiso);

$resultado = array_map(function ($u) {
    $nombre = htmlspecialchars($u['nombre'] . ' ' . $u['apellidopaterno'] . ' ' . ($u['apellidomaterno'] ?? ''));
    $cargo  = $u['cargo'] ? ' — ' . htmlspecialchars($u['cargo']) : '';
    return ['id' => $u['idusuario'], 'text' => trim($nombre) . $cargo];
}, $usuarios);

echo json_encode($resultado);
exit;
