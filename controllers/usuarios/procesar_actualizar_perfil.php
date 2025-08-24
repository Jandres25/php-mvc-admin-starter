<?php

/**
 * Procesador de Actualización de Perfil
 * 
 * Procesa la actualización del perfil del usuario autenticado
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

// Incluir el archivo de sesión para tener acceso a la variable $URL
require_once __DIR__ . '/../../views/layouts/session.php';

// Incluir autoload
require_once __DIR__ . '/../../config/config.php';

// Instanciar el controlador
$controller = new \Controllers\Usuarios\UsuarioController();
$resultado = $controller->actualizarPerfil();

// Guardar mensaje en la sesión
$_SESSION['mensaje'] = $resultado['message'];
$_SESSION['icono'] = $resultado['icon'];

// Redirigir según el resultado
header('Location: ' . $URL . $resultado['redirect']);
exit;
