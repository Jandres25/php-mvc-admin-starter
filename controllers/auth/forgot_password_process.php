<?php

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

$controller = new \App\Controllers\Auth\PasswordResetController();
$controller->requestReset();
