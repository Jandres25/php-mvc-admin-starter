<?php

/**
 * Assign User to Permission (AJAX)
 *
 * Assigns a user to a specific permission via AJAX.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Permissions
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

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

$userId       = filter_var($_POST['user_id']       ?? 0, FILTER_VALIDATE_INT);
$permissionId = filter_var($_POST['permission_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$userId || !$permissionId) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$result = $authService->assignPermission($userId, $permissionId);

if ($result) {
    $userModel = new \App\Models\User();
    $userModel->updatePermissionsTimestamp($userId);

    if ($userId === $_SESSION['user_id']) {
        $permModel = new \App\Models\Permission();
        $perm      = $permModel->getById($permissionId);
        if ($perm && !in_array($perm['name'], $_SESSION['user_permissions'])) {
            $_SESSION['user_permissions'][] = $perm['name'];
            $_SESSION['permissions_ts']     = date('Y-m-d H:i:s');
        }
    }

    $_SESSION['message'] = 'User assigned successfully.';
    $_SESSION['icon']    = 'success';
    echo json_encode(['success' => true, 'message' => 'User assigned successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error assigning user.']);
}
exit;
