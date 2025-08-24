<?php

/**
 * Cambio de Contraseña vía AJAX
 * 
 * Procesa el cambio de contraseña de usuarios mediante solicitudes AJAX
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Usuarios
 * @author Jandres25
 * @version 1.0
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir autoload
require_once __DIR__ . '/../../config/config.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Instanciar el controlador
$controller = new \Controllers\Usuarios\UsuarioController();

// Crear un método específico para el manejo AJAX del cambio de contraseña
$result = $controller->actualizarClavePerfilAjax();

// Devolver respuesta JSON
header('Content-Type: application/json');
echo json_encode($result);
exit;
