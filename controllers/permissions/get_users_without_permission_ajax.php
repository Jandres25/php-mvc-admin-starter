<?php

/**
 * Users Without Permission (AJAX)
 *
 * Returns the list of active users who do not have a specific permission assigned,
 * formatted for Select2.
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

$authService = new \Services\AuthorizationService();
if (!$authService->hasPermissionByName($_SESSION['user_id'], 'permissions')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action.']);
    exit;
}

header('Content-Type: application/json');

$permissionId = filter_var($_GET['permission_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$permissionId) {
    echo json_encode([]);
    exit;
}

$controller = new \Controllers\Permissions\PermissionController();
$users      = $controller->getUsersWithoutPermission($permissionId);

$result = array_map(function ($u) {
    $name     = htmlspecialchars($u['name'] . ' ' . $u['first_surname'] . ' ' . ($u['second_surname'] ?? ''));
    $position = $u['position'] ? ' — ' . htmlspecialchars($u['position']) : '';
    return ['id' => $u['id'], 'text' => trim($name) . $position];
}, $users);

echo json_encode($result);
exit;
