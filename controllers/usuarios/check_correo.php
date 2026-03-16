<?php
require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

requireLogin();

// Solo peticiones AJAX
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$correo    = trim($_POST['correo'] ?? '');
$idusuario = filter_var($_POST['idusuario'] ?? '', FILTER_VALIDATE_INT) ?: null;

if (!$correo) {
    echo 'true'; // Sin valor — la regla required lo maneja
    exit;
}

$modelo = new \Models\Usuario();
$existe  = $modelo->existeCorreo($correo, $idusuario);

echo $existe ? json_encode('Este correo ya está en uso.') : 'true';
