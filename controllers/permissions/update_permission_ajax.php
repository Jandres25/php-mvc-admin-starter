<?php

/**
 * Update Permission (AJAX)
 *
 * Processes the update of an existing permission via AJAX.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Permissions
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
if (!$authService->hasPermissionByName($_SESSION['user_id'], 'permissions')) {
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

$controller = new \App\Controllers\Permissions\PermissionController();
$result     = $controller->updateAjax();

if ($result['success']) {
    $_SESSION['message'] = 'Permission updated successfully.';
    $_SESSION['icon']    = 'success';
}

echo json_encode($result);
exit;
