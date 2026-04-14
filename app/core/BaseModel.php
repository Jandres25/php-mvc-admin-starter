<?php

/**
 * Base model with shared DB connection and common helpers.
 *
 * @package ProyectoBase
 * @subpackage App\Core
 * @author Jandres25
 * @version 1.0
 */

namespace App\Core;

use App\Config\Connection;
use PDO;

abstract class BaseModel
{
    /**
     * Database connection.
     *
     * @var PDO
     */
    protected $connection;

    /**
     * Last error message.
     *
     * @var string
     */
    protected $lastError = '';

    public function __construct()
    {
        $this->connection = Connection::getInstance()->getConnection();
    }

    /**
     * Returns the last error message.
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Trims all string values in the input array.
     *
     * @param array $data
     * @return array
     */
    public function sanitizeData($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_string($value) ? trim($value) : $value;
        }
        return $sanitized;
    }
}
