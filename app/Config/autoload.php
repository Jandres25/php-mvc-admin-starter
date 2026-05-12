<?php

/**
 * Manual Autoload System
 *
 * Automatically loads classes based on their namespace and directory structure.
 *
 * @package ProyectoBase
 * @subpackage App\Config
 * @author Jandres25
 * @version 1.0
 */

/**
 * Custom autoload function
 *
 * @param string $class Fully qualified class name with namespace
 */
function customAutoload($class)
{
    $class = ltrim($class, '\\');

    $parts = explode('\\', $class);
    if (empty($parts)) {
        return;
    }

    $className = array_pop($parts);
    $basePath  = dirname(__DIR__, 2);

    // App\* classes: map to app/<SubPath>/<ClassName>.php
    if (($parts[0] ?? null) === 'App') {
        $subParts = array_slice($parts, 1);
        $appBase  = $basePath . DIRECTORY_SEPARATOR . 'app';

        $sub  = implode(DIRECTORY_SEPARATOR, $subParts);
        $dir  = $sub !== '' ? $appBase . DIRECTORY_SEPARATOR . $sub : $appBase;
        $file = $dir . DIRECTORY_SEPARATOR . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Register the autoload function
spl_autoload_register('customAutoload');
