<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', '/');
}

if (!defined('URL')) {
    define('URL', 'http://localhost/test/');
}

date_default_timezone_set('UTC');

// Absorb any accidental output (header calls, etc.) so PHPUnit's
// beStrictAboutOutputDuringTests does not fail on SUT code that echoes.
ob_start();
