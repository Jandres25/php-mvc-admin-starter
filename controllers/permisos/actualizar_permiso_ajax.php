<?php
require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/PermisoController.php';

// Verificar si es una solicitud AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit('Acceso no permitido');
}

// Verificar si el usuario está autenticado
requireLogin();

header('Content-Type: application/json');

// Instanciar el controlador
$controller = new PermisoController();

// Procesar la actualización
$resultado = $controller->actualizarAjax();

// Establecer mensaje en la sesión para mensajes.php
if ($resultado['success']) {
    $_SESSION['mensaje'] = 'Permiso actualizado correctamente';
    $_SESSION['icono'] = 'success';
}

// Devolver respuesta JSON
echo json_encode($resultado);
exit;
