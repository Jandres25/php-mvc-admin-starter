<?php

/**
 * Procesador de Creación de Usuarios
 * 
 * Procesa el formulario de creación de nuevos usuarios
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Usuarios
 * @author Jandres25
 * @version 1.0
 */

// Incluir el archivo de sesión para tener acceso a la variable $URL
require_once __DIR__ . '/../../views/layouts/session.php';

// Incluir autoload
require_once __DIR__ . '/../../config/config.php';

requireLogin();
requirePermiso('usuarios');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['mensaje'] = 'Acción no permitida.';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/usuarios/index.php');
    exit;
}

regenerateCSRFToken();

// Instanciar el controlador
$controller = new \Controllers\Usuarios\UsuarioController();

// Procesar el formulario
$resultado = $controller->guardar();

// Guardar mensaje en la sesión
$_SESSION['mensaje'] = $resultado['message'];
$_SESSION['icono'] = $resultado['icon'];

// Redirigir según el resultado
header('Location: ' . $URL . 'views/usuarios/' . $resultado['redirect']);
exit;
