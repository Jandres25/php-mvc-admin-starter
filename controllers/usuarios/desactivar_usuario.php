<?php

/**
 * Cambio de Estado de Usuarios vía AJAX
 *
 * Procesa la activación/desactivación de usuarios mediante solicitudes AJAX
 *
 * @package ProyectoBase
 * @subpackage Controllers\Usuarios
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

// Verificar si el usuario está autenticado
requireLogin();

// Verificar permiso de gestión de usuarios
$authService = new \Services\AuthorizationService();
if (!$authService->tienePermisoNombre($_SESSION['usuario_id'], 'usuarios')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción']);
    exit;
}

header('Content-Type: application/json');

// Validar token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
    exit;
}

regenerateCSRFToken();

$id_usuario   = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$estado_actual = filter_var($_POST['estado_actual'] ?? null, FILTER_VALIDATE_INT);

if ($id_usuario === false || $estado_actual === false) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos para cambiar el estado del usuario']);
    exit;
}

if ($id_usuario === (int)$_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
    exit;
}

// Instanciar el controlador y procesar
$controller = new \Controllers\Usuarios\UsuarioController();
$resultado  = $controller->cambiarEstadoUsuario($id_usuario, $estado_actual);

if ($resultado['success']) {
    $_SESSION['mensaje'] = $resultado['message'];
    $_SESSION['icono']   = $resultado['icon'];
}

echo json_encode($resultado);
exit;
