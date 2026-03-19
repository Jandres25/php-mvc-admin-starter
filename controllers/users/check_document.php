<?php
require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

requireLogin();

if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$documentType   = trim($_POST['document_type']   ?? '');
$documentNumber = trim($_POST['document_number'] ?? '');
$userId         = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

if (!$documentType || !$documentNumber) {
    echo 'true'; // Empty — the required rule handles it
    exit;
}

$model  = new \Models\User();
$exists = $model->documentTypeExists($documentType, $documentNumber, $userId);

echo $exists ? json_encode('This document is already registered.') : 'true';
