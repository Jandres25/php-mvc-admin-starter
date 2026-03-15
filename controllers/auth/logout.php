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

// Incluir sesión (inicia sesión, carga .env, define $URL y funciones globales)
require_once __DIR__ . '/../../views/layouts/session.php';

// Incluir autoload y configuración
require_once __DIR__ . '/../../config/config.php';

// Crear instancia del controlador
$authController = new \Controllers\Auth\AuthController();

// Procesar logout
$authController->logout();
