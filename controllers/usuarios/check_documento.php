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

$tipodocumento = trim($_POST['tipodocumento'] ?? '');
$numdocumento  = trim($_POST['numdocumento']  ?? '');
$idusuario     = filter_var($_POST['idusuario'] ?? '', FILTER_VALIDATE_INT) ?: null;

if (!$tipodocumento || !$numdocumento) {
    echo 'true'; // Sin valores — las reglas required lo manejan
    exit;
}

$modelo = new \Models\Usuario();
$existe  = $modelo->existeTipoDocumento($tipodocumento, $numdocumento, $idusuario);

echo $existe ? json_encode('Este documento ya está registrado.') : 'true';
