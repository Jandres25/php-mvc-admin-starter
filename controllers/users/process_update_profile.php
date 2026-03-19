<?php

/**
 * Profile Update Processor
 *
 * Processes the profile update form for the authenticated user.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Users
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/../../views/layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

$controller = new \Controllers\Users\ProfileController();
$result     = $controller->updateProfile();

$_SESSION['message'] = $result['message'];
$_SESSION['icon']    = $result['icon'];

header('Location: ' . $URL . $result['redirect']);
exit;
