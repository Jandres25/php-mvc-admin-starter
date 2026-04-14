<?php

/**
 * Logout Processor
 *
 * Processes the session termination.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Auth
 * @author Jandres25
 * @version 1.0
 */

// Include session (starts session, loads .env, defines $URL and global functions)
require_once __DIR__ . '/../../views/layouts/session.php';

// Include autoloader and configuration
require_once __DIR__ . '/../../app/config/config.php';

// Instantiate the controller
$authController = new \App\Controllers\Auth\AuthController();

// Process logout
$authController->logout();
