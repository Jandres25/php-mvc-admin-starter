<?php

/**
 * Main configuration file
 *
 * Contains the global application configuration, including
 * database connection parameters and general settings.
 *
 * @package ProyectoBase
 * @subpackage Config
 * @author Jandres25
 * @version 1.0
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/autoload.php';

// Set the timezone, falling back to a default if not defined
$timezone = env('TIMEZONE');
date_default_timezone_set($timezone);

return [
    'database' => [
        'host' => env('DB_HOST'),
        'name' => env('DB_NAME'),
        'user' => env('DB_USER'),
        'pass' => env('DB_PASS'),
        'charset' => env('DB_CHARSET')
    ],
    'app' => [
        'timezone' => $timezone,
        'name' => 'ProyectoBase',
        'url' => env('APP_URL'),
        'debug' => env('DEBUG'),
        'version' => env('APP_VERSION', '1.0.0')
    ]
];
