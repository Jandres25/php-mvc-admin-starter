<?php

namespace App\Models\Traits;

use PDO;
use PDOException;

/**
 * UserPasswordTrait
 *
 * Groups password-related operations: current password verification,
 * password reset token lifecycle, and remember-me token management.
 *
 * @package ProyectoBase
 * @subpackage App\Models\Traits
 */
trait UserPasswordTrait
{
    // -------------------------------------------------------------------------
    // Password verification
    // -------------------------------------------------------------------------

    /**
     * Verifies whether the given plain-text password matches the stored hash.
     *
     * @param int    $id
     * @param string $currentPassword
     * @return bool
     */
    public function verifyCurrentPassword($id, $currentPassword)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT password FROM {$this->table} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $hash = $stmt->fetchColumn();
            return password_verify($currentPassword, $hash);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Password reset token lifecycle
    // -------------------------------------------------------------------------

    /**
     * Generates a password reset token, persists it, and returns the raw token.
     * Returns null if the email is not found or the update fails.
     *
     * @param string $email
     * @return string|null  Raw token to include in the reset link
     */
    public function createPasswordResetToken(string $email): ?string
    {
        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        return $this->setResetToken($email, $token, $expiry) ? $token : null;
    }

    /**
     * Persists the password reset token and expiry for a user identified by email.
     *
     * @param string $email
     * @param string $token
     * @param string $expiry  DateTime string (Y-m-d H:i:s)
     * @return bool
     */
    public function setResetToken(string $email, string $token, string $expiry): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET reset_token = :token, reset_token_expiry = :expiry
                 WHERE email = :email"
            );
            $stmt->bindParam(':token',  $token,  PDO::PARAM_STR);
            $stmt->bindParam(':expiry', $expiry, PDO::PARAM_STR);
            $stmt->bindParam(':email',  $email,  PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Finds a user by a valid (non-expired) reset token.
     *
     * @param string $token
     * @return array|false
     */
    public function getUserByResetToken($token)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->table}
                 WHERE reset_token = :token AND reset_token_expiry > NOW()"
            );
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Resets the user's password and clears the reset token.
     *
     * @param int    $id
     * @param string $newPassword Plain-text password (will be hashed)
     * @return bool
     */
    public function resetPassword($id, $newPassword)
    {
        try {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET password = :password, reset_token = NULL, reset_token_expiry = NULL
                 WHERE id = :id"
            );
            $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':id',       $id,   PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Remember-me token
    // -------------------------------------------------------------------------

    /**
     * Stores a hashed remember-me token and its expiry for a user.
     *
     * @param int    $userId
     * @param string $tokenHash
     * @param string $expires   DateTime string (Y-m-d H:i:s)
     * @return bool
     */
    public function setRememberToken(int $userId, string $tokenHash, string $expires): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET remember_token = :token, remember_token_expires = :expires
                 WHERE id = :id"
            );
            $stmt->bindParam(':token',   $tokenHash, PDO::PARAM_STR);
            $stmt->bindParam(':expires', $expires,   PDO::PARAM_STR);
            $stmt->bindParam(':id',      $userId,    PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Finds an active user by a valid (non-expired) remember-me token hash.
     *
     * @param string $tokenHash
     * @return array|false
     */
    public function findByRememberToken(string $tokenHash): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.remember_token = :token
                  AND u.remember_token_expires > NOW()
                  AND u.status = 1
                LIMIT 1
            ");
            $stmt->bindParam(':token', $tokenHash, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Clears the remember-me token for a user (on logout).
     *
     * @param int $userId
     * @return bool
     */
    public function clearRememberToken(int $userId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->table}
                 SET remember_token = NULL, remember_token_expires = NULL
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
