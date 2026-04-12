<?php

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

$controller = new \Controllers\Auth\PasswordResetController();
$controller->resetPassword();
