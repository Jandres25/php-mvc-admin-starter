<?php

/**
 * Sistema de Autoload Manual
 * 
 * Carga automáticamente las clases basándose en su namespace y estructura de carpetas
 * 
 * @package ProyectoBase
 * @subpackage Config
 * @author Jandres25
 * @version 1.0
 */

/**
 * Función de autoload personalizada
 * 
 * @param string $class Nombre completo de la clase con namespace
 */
function customAutoload($class)
{
    // Convertir namespace separadores a separadores de directorio
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Construir la ruta del archivo basándose en el namespace
    $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . strtolower($class) . '.php';

    // Verificar si el archivo existe y cargarlo
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Si no se encuentra, intentar buscar directamente por el nombre de clase
    // Esto es útil para casos especiales como Config\Conexion
    $parts = explode('\\', $class);
    $className = end($parts);

    // Buscar en config/ si es del namespace Config
    if ($parts[0] === 'Config') {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . strtolower($className) . '.php';
        if (file_exists($configFile)) {
            require_once $configFile;
        }
    }
}

// Registrar la función de autoload
spl_autoload_register('customAutoload');
