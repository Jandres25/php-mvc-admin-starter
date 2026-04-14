<?php

/**
 * Create User Processor
 *
 * Processes the create-user form submission.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Users
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

requireLogin();
requirePermission('users');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['message'] = 'Action not allowed.';
    $_SESSION['icon']    = 'error';
    header('Location: ' . $URL . 'views/users/index.php');
    exit;
}

regenerateCSRFToken();

$controller = new \App\Controllers\Users\UserController();
$result     = $controller->save();

$_SESSION['message'] = $result['message'];
$_SESSION['icon']    = $result['icon'];

header('Location: ' . $URL . 'views/users/' . $result['redirect']);
exit;
