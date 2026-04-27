<?php

namespace App\Core;

use App\Config\Connection;

abstract class Model
{
    protected $db;
    protected $connection;
    protected $lastError = '';
    protected $table;

    public function __construct()
    {
        $this->db = $this->getConnection();
        $this->connection = $this->db;
    }

    protected function getConnection()
    {
        try {
            return Connection::getInstance()->getConnection();
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            throw $e;
        }
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    public function insert($data)
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($data), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $this->query($sql, $values);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $values[] = $id;

        $setClause = implode(' = ?, ', $columns) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";

        $this->query($sql, $values);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->query($sql, [$id]);
    }

    public function sanitizeData($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_string($value) ? trim($value) : $value;
        }
        return $sanitized;
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}
