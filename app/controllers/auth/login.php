<?php

/**
 * Login Processor
 *
 * Processes the login form submission.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Auth
 * @author Jandres25
 * @version 1.0
 */

// Include session (starts session, loads .env, defines global auth and CSRF functions)
require_once __DIR__ . '/../../../views/layouts/session.php';

// Include autoloader and configuration
require_once __DIR__ . '/../../config/config.php';

// Instantiate the controller
$authController = new \App\Controllers\Auth\AuthController();

// Process login
$authController->login();
