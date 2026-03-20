<?php

/**
 * Environment variable helpers
 *
 * Provides functions to load and access environment variables
 * from a .env file.
 *
 * @package ProyectoBase
 * @subpackage Config
 * @author Jandres25
 * @version 1.0
 */

/**
 * Loads environment variables from a .env file
 *
 * Reads the .env file line by line and sets environment variables
 * using putenv() and $_ENV.
 *
 * @param string $path Path to the .env file
 * @throws Exception If the .env file does not exist
 * @return void
 */
function loadEnv($path)
{
    if (!file_exists($path)) {
        throw new Exception("The .env file does not exist. Create one based on .env.example");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Strip surrounding quotes if present
        if (!empty($value)) {
            $value = trim($value, '"');
            $value = trim($value, "'");
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

/**
 * Gets the value of an environment variable
 *
 * Retrieves an environment variable value and handles type
 * conversions for boolean, null, and empty values.
 *
 * @param string $key     Environment variable name
 * @param mixed  $default Default value if the variable does not exist
 * @return mixed The environment variable value or the default
 */
function env($key, $default = null)
{
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    // Handle boolean values
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }

    return $value;
}

// Load environment variables
try {
    loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    die('Error loading the .env file: ' . $e->getMessage());
}
