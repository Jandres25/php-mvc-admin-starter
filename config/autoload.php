<?php

/**
 * Manual Autoload System
 *
 * Automatically loads classes based on their namespace and directory structure.
 *
 * @package ProyectoBase
 * @subpackage Config
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
    // Split namespace (directories) from the class name
    $parts = explode('\\', $class);
    $className = array_pop($parts);

    // Namespaces map to lowercase directories; the class name retains its capitalization
    $directory = strtolower(implode(DIRECTORY_SEPARATOR, $parts));

    // Build the file path based on the namespace
    $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $className . '.php';

    // Check if the file exists and load it
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // If not found, fall back to searching by class name directly
    // Useful for special cases like Config\Connection

    // Search in config/ if the namespace is Config
    if ($parts[0] === 'Config') {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . strtolower($className) . '.php';
        if (file_exists($configFile)) {
            require_once $configFile;
        }
    }
}

// Register the autoload function
spl_autoload_register('customAutoload');
