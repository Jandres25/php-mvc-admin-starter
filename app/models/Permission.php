<?php

/**
 * Permission Model
 *
 * Handles all database operations related to permissions.
 *
 * @package ProyectoBase
 * @subpackage App\Models
 * @author Jandres25
 * @version 1.0
 */

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Permission extends Model
{
    /**
     * Permissions table name
     * @var string
     */
    private $tabla = 'permissions';

    /**
     * Returns all permissions.
     *
     * @param bool $onlyActive  When true, returns only active permissions
     * @return array
     */
    public function getAll($onlyActive = false)
    {
        try {
            $query = "SELECT * FROM {$this->tabla}";
            if ($onlyActive) {
                $query .= " WHERE status = 1";
            }
            $query .= " ORDER BY name";

            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns all permissions with the count of assigned users (single query).
     *
     * @param bool $onlyActive
     * @return array  Each row includes total_users
     */
    public function getAllWithUserCount($onlyActive = false)
    {
        try {
            $where = $onlyActive ? "WHERE p.status = 1" : "";
            $query = "SELECT p.*, COUNT(up.user_id) AS total_users
                      FROM {$this->tabla} p
                      LEFT JOIN user_permissions up ON p.id = up.permission_id
                      {$where}
                      GROUP BY p.id
                      ORDER BY p.name";

            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns a single permission by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->tabla} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Creates a new permission.
     *
     * @param array $data  Keys: name, description
     * @return bool
     */
    public function create($data)
    {
        try {
            if ($this->nameExists($data['name'])) {
                $this->lastError = 'A permission with this name already exists.';
                return false;
            }

            $description = !empty($data['description']) ? $data['description'] : null;
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->tabla} (name, description) VALUES (:name, :description)"
            );
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            if ($description === null) {
                $stmt->bindValue(':description', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->lastError = 'A permission with this name already exists.';
            } else {
                $this->lastError = $e->getMessage();
            }
            return false;
        }
    }

    /**
     * Updates an existing permission.
     *
     * @param int   $id
     * @param array $data  Keys: name, description
     * @return bool
     */
    public function update($id, $data)
    {
        try {
            if ($this->nameExists($data['name'], $id)) {
                $this->lastError = 'A permission with this name already exists.';
                return false;
            }

            $description = !empty($data['description']) ? $data['description'] : null;
            $stmt = $this->connection->prepare(
                "UPDATE {$this->tabla} SET name = :name, description = :description WHERE id = :id"
            );
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            if ($description === null) {
                $stmt->bindValue(':description', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->lastError = 'A permission with this name already exists.';
            } else {
                $this->lastError = $e->getMessage();
            }
            return false;
        }
    }

    /**
     * Updates the status of a permission.
     *
     * @param int $id
     * @param int $status  1 = active, 0 = inactive
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->tabla} SET status = :status WHERE id = :id"
            );
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':id',     $id,     PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Checks whether a permission with the given name already exists.
     *
     * @param string   $name
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists($name, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) FROM {$this->tabla} WHERE name = :name AND id != :id";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':name', $name,      PDO::PARAM_STR);
                $stmt->bindParam(':id',   $excludeId, PDO::PARAM_INT);
            } else {
                $query = "SELECT COUNT(*) FROM {$this->tabla} WHERE name = :name";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the number of users assigned to a permission.
     *
     * @param int $permissionId
     * @return int
     */
    public function countUsers($permissionId)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM user_permissions WHERE permission_id = :id"
            );
            $stmt->bindParam(':id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }

    /**
     * Returns the users assigned to a specific permission.
     *
     * @param int $permissionId
     * @return array
     */
    public function getUsersByPermission($permissionId)
    {
        try {
            $query = "SELECT u.id, u.name, u.first_surname, u.second_surname, u.email, u.position, u.status
                      FROM users u
                      INNER JOIN user_permissions up ON u.id = up.user_id
                      WHERE up.permission_id = :permission_id
                      ORDER BY u.name, u.first_surname";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns active users who do NOT have a specific permission assigned.
     *
     * @param int $permissionId
     * @return array
     */
    public function getUsersWithoutPermission($permissionId)
    {
        try {
            $query = "SELECT u.id, u.name, u.first_surname, u.second_surname, u.position
                      FROM users u
                      WHERE u.status = 1
                        AND u.id NOT IN (
                            SELECT user_id FROM user_permissions WHERE permission_id = :permission_id
                        )
                      ORDER BY u.name, u.first_surname";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns statistics: total, active, inactive counts (single query).
     *
     * @return array  Keys: total, active, inactive, most_used
     */
    public function getStatistics()
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS inactive
                FROM {$this->tabla}
            ");
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $this->connection->prepare("
                SELECT p.id, p.name, COUNT(up.user_id) AS total_users
                FROM permissions p
                LEFT JOIN user_permissions up ON p.id = up.permission_id
                GROUP BY p.id
                ORDER BY total_users DESC
                LIMIT 5
            ");
            $stmt2->execute();
            $mostUsed = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total'     => (int) $counts['total'],
                'active'    => (int) $counts['active'],
                'inactive'  => (int) $counts['inactive'],
                'most_used' => $mostUsed,
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['total' => 0, 'active' => 0, 'inactive' => 0, 'most_used' => []];
        }
    }

    /**
     * Returns the ID of the last inserted row.
     *
     * @return int
     */
    public function getLastInsertId()
    {
        try {
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return 0;
        }
    }
}
