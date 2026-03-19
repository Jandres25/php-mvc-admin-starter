<?php

/**
 * Revocar usuario de permiso (AJAX)
 *
 * Revoca el permiso de un usuario específico.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Permisos
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

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

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
    exit;
}

regenerateCSRFToken();

$idusuario = filter_var($_POST['idusuario'] ?? 0, FILTER_VALIDATE_INT);
$idpermiso = filter_var($_POST['idpermiso'] ?? 0, FILTER_VALIDATE_INT);

if (!$idusuario || !$idpermiso) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$resultado = $authService->revocarPermiso($idusuario, $idpermiso);

if ($resultado) {
    // Actualizar timestamp para invalidar cache de sesión del usuario afectado
    $modeloUsuario = new \Models\Usuario();
    $modeloUsuario->actualizarPermisosTimestamp($idusuario);

    // Si el usuario revocado es el usuario actual, refrescar cache de sesión inmediatamente
    if ($idusuario === $_SESSION['usuario_id']) {
        $permisos = $authService->obtenerPermisosUsuario($idusuario);
        $_SESSION['usuario_permisos'] = array_column($permisos, 'nombre');
        $_SESSION['permisos_ts'] = date('Y-m-d H:i:s');
    }
    $_SESSION['mensaje'] = 'Permiso revocado correctamente';
    $_SESSION['icono']   = 'success';
    echo json_encode(['success' => true, 'message' => 'Permiso revocado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al revocar el permiso']);
}
exit;
