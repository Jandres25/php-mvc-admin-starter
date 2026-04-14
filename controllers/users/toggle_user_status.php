<?php

/**
 * Toggle User Status (AJAX)
 *
 * Activates or deactivates a user via AJAX.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Users
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit('Access not allowed.');
}

requireLogin();

$authService = new \App\Services\AuthorizationService();
if (!$authService->hasPermissionByName($_SESSION['user_id'], 'users')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action.']);
    exit;
}

header('Content-Type: application/json');

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

regenerateCSRFToken();

$userId        = filter_var($_POST['id']            ?? null, FILTER_VALIDATE_INT);
$currentStatus = filter_var($_POST['current_status'] ?? null, FILTER_VALIDATE_INT);

if ($userId === false || $currentStatus === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

if ($userId === (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account.']);
    exit;
}

$controller = new \App\Controllers\Users\UserController();
$result     = $controller->toggleUserStatus($userId, $currentStatus);

if ($result['success']) {
    $_SESSION['message'] = $result['message'];
    $_SESSION['icon']    = $result['icon'];
}

echo json_encode($result);
exit;
