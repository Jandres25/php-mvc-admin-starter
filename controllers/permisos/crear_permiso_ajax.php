<?php

/**
 * Creación de Permisos vía AJAX
 * 
 * Procesa la creación de nuevos permisos mediante solicitudes AJAX
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

// Verificar si el usuario está autenticado
requireLogin();

// Verificar permiso de gestión de permisos
$authService = new \Services\AuthorizationService();
if (!$authService->tienePermisoNombre($_SESSION['usuario_id'], 'permisos')) {
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

// Instanciar el controlador
$controller = new \Controllers\Permisos\PermisoController();

// Procesar la creación
$resultado = $controller->crearAjax();

// Establecer mensaje en la sesión para mensajes.php
if ($resultado['success']) {
    $_SESSION['mensaje'] = 'Permiso creado correctamente';
    $_SESSION['icono'] = 'success';
}

// Devolver respuesta JSON
echo json_encode($resultado);
exit;
