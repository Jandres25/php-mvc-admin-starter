<?php

/**
 * Change Password via AJAX
 *
 * Processes password changes for authenticated users via AJAX.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Users
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

$controller = new \App\Controllers\Users\ProfileController();
$result     = $controller->updatePasswordAjax();

header('Content-Type: application/json');
echo json_encode($result);
exit;
