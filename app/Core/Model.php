<?php

namespace App\Core;

use App\Config\Connection;
use PDOException;
use Throwable;

/**
 * Base Model
 *
 * Provides shared database utilities and generic CRUD fallbacks.
 * Concrete models extend this class, set $table, and override
 * methods that require JOINs or specific field handling.
 *
 * @package ProyectoBase
 * @subpackage App\Core
 */
abstract class Model
{
    /** @var \PDO */
    protected $connection;

    /** @var string  Override in each model: protected $table = 'table_name'; */
    protected $table = '';

    /** @var string  Last PDO error message */
    protected $lastError = '';

    public function __construct()
    {
        $this->connection = $this->getConnection();
    }

    // -------------------------------------------------------------------------
    // Connection
    // -------------------------------------------------------------------------

    protected function getConnection()
    {
        try {
            return Connection::getInstance()->getConnection();
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Generic CRUD — override in models that need JOINs or specific fields
    // -------------------------------------------------------------------------

    /**
     * Returns a single row by primary key.
     *
     * @param  int $id
     * @return array|false
     */
    public function find(int $id)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1"
            );
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns all rows from the table.
     *
     * @return array
     */
    public function all(): array
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM {$this->table}");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Inserts a new row. Keys of $data must match column names.
     *
     * @param  array $data
     * @return int   Last insert ID, or 0 on failure
     */
    public function insert(array $data): int
    {
        try {
            $columns      = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));

            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})"
            );
            $stmt->execute(array_values($data));
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Updates a row by primary key. Keys of $data must match column names.
     *
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            $set    = implode(' = ?, ', array_keys($data)) . ' = ?';
            $values = array_merge(array_values($data), [$id]);

            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET {$set} WHERE id = ?"
            );
            return $stmt->execute($values);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Deletes a row by primary key.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------

    /**
     * Executes a prepared statement and returns the statement object.
     *
     * @param  string $sql
     * @param  array  $params
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            throw $e;
        }
    }

    /**
     * Returns the ID generated by the last INSERT statement.
     *
     * @return int
     */
    public function getLastInsertId(): int
    {
        try {
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Trims whitespace from all string values in an array.
     *
     * @param  array $data
     * @return array
     */
    public function trimInput(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_string($value) ? trim($value) : $value;
        }
        return $sanitized;
    }

    /**
     * Returns the last PDO error message.
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }
}
