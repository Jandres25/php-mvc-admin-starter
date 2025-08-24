<?php

/**
 * Procesador de Logout
 * 
 * Este archivo procesa el cierre de sesión
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Auth
 * @author Jandres25
 * @version 1.0
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir autoload y configuración
require_once __DIR__ . '/../../config/config.php';

// Crear instancia del controlador
$authController = new \Controllers\Auth\AuthController();

// Procesar logout
$authController->logout();
