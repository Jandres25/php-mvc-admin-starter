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

// Incluir sesión (inicia sesión, carga .env, define funciones globales de auth y CSRF)
require_once __DIR__ . '/../../views/layouts/session.php';

// Incluir autoload y configuración
require_once __DIR__ . '/../../config/config.php';

// Crear instancia del controlador
$authController = new \Controllers\Auth\AuthController();

// Procesar login
$authController->login();
