<?php
require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

requireLogin();

if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$email  = trim($_POST['email']   ?? '');
$userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

if (!$email) {
    echo 'true'; // Empty — the required rule handles it
    exit;
}

$model  = new \App\Models\User();
$exists = $model->emailExists($email, $userId);

echo $exists ? json_encode('This email is already in use.') : 'true';
