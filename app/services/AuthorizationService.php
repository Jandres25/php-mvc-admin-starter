<?php

/**
 * Authorization Service
 *
 * Manages user permissions and authorization.
 *
 * @package ProyectoBase
 * @subpackage App\Services
 * @author Jandres25
 * @version 1.0
 */

namespace App\Services;

use Config\Connection;
use PDO;
use PDOException;

class AuthorizationService
{
    /**
     * Database connection
     * @var PDO
     */
    private $connection;

    /**
     * Per-request cache for isAdmin() results
     * @var array<int, bool>
     */
    private static array $adminCache = [];

    public function __construct()
    {
        $this->connection = Connection::getInstance()->getConnection();
    }

    /**
     * Checks whether a user has a specific permission by ID.
     *
     * @param int $userId
     * @param int $permissionId
     * @return bool
     */
    public function hasPermission($userId, $permissionId)
    {
        if ($this->isAdmin($userId)) {
            return true;
        }

        return $this->hasPermissionAssigned($userId, $permissionId);
    }

    /**
     * Checks whether a user has a permission by name.
     * Uses the session cache when available to avoid extra queries per page load.
     *
     * @param int    $userId
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionByName($userId, $permissionName)
    {
        if (isset($_SESSION['user_permissions'])) {
            return in_array('*', $_SESSION['user_permissions']) ||
                   in_array($permissionName, $_SESSION['user_permissions']);
        }

        try {
            if ($this->isAdmin($userId)) {
                return true;
            }

            $stmt = $this->connection->prepare(
                "SELECT id FROM permissions WHERE name = :name AND status = 1"
            );
            $stmt->bindParam(':name', $permissionName, PDO::PARAM_STR);
            $stmt->execute();

            $permissionId = $stmt->fetchColumn();

            if (!$permissionId) {
                return false;
            }

            return $this->hasPermission($userId, $permissionId);
        } catch (PDOException $e) {
            error_log('Error checking permission by name: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks whether a user has the Administrator position.
     * Result is memoized per-request.
     *
     * @param int $userId
     * @return bool
     */
    public function isAdmin($userId)
    {
        if (isset(self::$adminCache[$userId])) {
            return self::$adminCache[$userId];
        }

        try {
            $stmt = $this->connection->prepare(
                "SELECT position FROM users WHERE id = :id AND status = 1"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $position = $stmt->fetchColumn();

            return self::$adminCache[$userId] = strtolower($position) === 'administrator';
        } catch (PDOException $e) {
            error_log('Error checking admin status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns all permissions assigned to a user.
     * Administrators receive all active permissions.
     *
     * @param int $userId
     * @return array  Each row: [id, name]
     */
    public function getUserPermissions($userId)
    {
        try {
            if ($this->isAdmin($userId)) {
                $stmt = $this->connection->prepare(
                    "SELECT id, name FROM permissions WHERE status = 1"
                );
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $stmt = $this->connection->prepare("
                SELECT p.id, p.name
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = :user_id AND p.status = 1
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching user permissions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Assigns a permission to a user (idempotent).
     *
     * @param int $userId
     * @param int $permissionId
     * @return bool
     */
    public function assignPermission($userId, $permissionId)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM user_permissions
                WHERE user_id = :user_id AND permission_id = :permission_id
            ");
            $stmt->bindParam(':user_id',       $userId,       PDO::PARAM_INT);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return true; // Already assigned
            }

            $stmt = $this->connection->prepare("
                INSERT INTO user_permissions (permission_id, user_id) VALUES (:permission_id, :user_id)
            ");
            $stmt->bindParam(':user_id',       $userId,       PDO::PARAM_INT);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error assigning permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Revokes a permission from a user (idempotent).
     *
     * @param int $userId
     * @param int $permissionId
     * @return bool
     */
    public function revokePermission($userId, $permissionId)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM user_permissions
                WHERE user_id = :user_id AND permission_id = :permission_id
            ");
            $stmt->bindParam(':user_id',       $userId,       PDO::PARAM_INT);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $stmt = $this->connection->prepare("
                    DELETE FROM user_permissions
                    WHERE user_id = :user_id AND permission_id = :permission_id
                ");
                $stmt->bindParam(':user_id',       $userId,       PDO::PARAM_INT);
                $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
                return $stmt->execute();
            }

            return true; // Nothing to revoke
        } catch (PDOException $e) {
            error_log('Error revoking permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns all active permissions in the system.
     *
     * @return array  Each row: [id, name]
     */
    public function getAllPermissions()
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id, name FROM permissions WHERE status = 1"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching all permissions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks whether a specific permission is assigned to a user.
     *
     * @param int $userId
     * @param int $permissionId
     * @return bool
     */
    public function hasPermissionAssigned($userId, $permissionId)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM user_permissions
                WHERE user_id = :user_id AND permission_id = :permission_id
            ");
            $stmt->bindParam(':user_id',       $userId,       PDO::PARAM_INT);
            $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error checking assigned permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Replaces all permissions for a user (delete + re-insert in a transaction).
     *
     * @param int   $userId
     * @param array $permissionIds  Array of permission IDs to assign
     * @return bool
     */
    public function updateUserPermissions($userId, $permissionIds)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare(
                "DELETE FROM user_permissions WHERE user_id = :user_id"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            foreach ($permissionIds as $permissionId) {
                $stmt = $this->connection->prepare("
                    INSERT INTO user_permissions (permission_id, user_id) VALUES (:permission_id, :user_id)
                ");
                $stmt->bindParam(':permission_id', $permissionId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id',       $userId,       PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Error updating user permissions: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns the IDs of all permissions assigned to a user.
     *
     * @param int $userId
     * @return array  List of permission IDs
     */
    public function getAssignedPermissions($userId)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT permission_id FROM user_permissions WHERE user_id = :user_id"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $ids = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ids[] = $row['permission_id'];
            }
            return $ids;
        } catch (PDOException $e) {
            error_log('Error fetching assigned permissions: ' . $e->getMessage());
            return [];
        }
    }
}
