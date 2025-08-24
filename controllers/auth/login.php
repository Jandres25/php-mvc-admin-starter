<?php

/**
 * Procesador de Login
 * 
 * Este archivo procesa el formulario de login
 * 
 * @package ProyectoBase
 * @subpackage Controllers\Auth
 * @author Jandres25
 * @version 1.0
 */

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir autoload y configuración
require_once __DIR__ . '/../../config/config.php';

// Crear instancia del controlador
$authController = new \Controllers\Auth\AuthController();

// Procesar login
$authController->login();
