<?php

/**
 * User Model
 *
 * Handles all database operations related to users.
 *
 * @package ProyectoBase
 * @subpackage App\Models
 * @author Jandres25
 * @version 1.0
 */

namespace App\Models;

use App\Core\Model;
use App\Services\DashboardCache;
use PDO;
use PDOException;

class User extends Model
{
    /**
     * Users table name
     * @var string
     */
    private $tabla = 'users';

    /**
     * Returns all users ordered by ID descending.
     *
     * @return array
     */
    public function getAll()
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name
                FROM {$this->tabla} u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns a single user by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->tabla} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Creates a new user.
     *
     * @param array $data
     * @return bool
     */
    public function create($data)
    {
        try {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            $query = "INSERT INTO {$this->tabla}
                        (name, first_surname, second_surname, document_type, document_number,
                         address, phone, email, password, image, status, role_id)
                      VALUES
                        (:name, :first_surname, :second_surname, :document_type, :document_number,
                         :address, :phone, :email, :password, :image, :status, :role_id)";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':name',            $data['name'],            PDO::PARAM_STR);
            $stmt->bindParam(':first_surname',   $data['first_surname'],   PDO::PARAM_STR);

            if (empty($data['second_surname'])) {
                $stmt->bindValue(':second_surname', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':second_surname', $data['second_surname'], PDO::PARAM_STR);
            }

            $stmt->bindParam(':document_type',   $data['document_type'],   PDO::PARAM_STR);
            $stmt->bindParam(':document_number', $data['document_number'], PDO::PARAM_STR);

            if (empty($data['address'])) {
                $stmt->bindValue(':address', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
            }

            if (empty($data['phone'])) {
                $stmt->bindValue(':phone', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            }

            if (empty($data['email'])) {
                $stmt->bindValue(':email', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            }

            if (empty($data['image'])) {
                $stmt->bindValue(':image', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);
            }

            $stmt->bindParam(':password', $passwordHash,   PDO::PARAM_STR);
            $stmt->bindParam(':status',   $data['status'], PDO::PARAM_INT);

            if (empty($data['role_id'])) {
                $stmt->bindValue(':role_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':role_id', (int) $data['role_id'], PDO::PARAM_INT);
            }

            if ($stmt->execute()) {
                DashboardCache::forget('user_stats');
                DashboardCache::forget('users_by_status');
                DashboardCache::forget('recent_users');
                DashboardCache::forget('users_by_month');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates an existing user (admin edit, no password change here).
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        try {
            $query = "UPDATE {$this->tabla} SET
                        name            = :name,
                        first_surname   = :first_surname,
                        second_surname  = :second_surname,
                        document_type   = :document_type,
                        document_number = :document_number,
                        address         = :address,
                        phone           = :phone,
                        email           = :email,
                        image           = :image,
                        status          = :status,
                        role_id         = :role_id
                      WHERE id = :id";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':name',            $data['name'],            PDO::PARAM_STR);
            $stmt->bindParam(':first_surname',   $data['first_surname'],   PDO::PARAM_STR);

            if (empty($data['second_surname'])) {
                $stmt->bindValue(':second_surname', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':second_surname', $data['second_surname'], PDO::PARAM_STR);
            }

            $stmt->bindParam(':document_type',   $data['document_type'],   PDO::PARAM_STR);
            $stmt->bindParam(':document_number', $data['document_number'], PDO::PARAM_STR);

            if (empty($data['address'])) {
                $stmt->bindValue(':address', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
            }

            if (empty($data['phone'])) {
                $stmt->bindValue(':phone', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            }

            if (empty($data['email'])) {
                $stmt->bindValue(':email', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            }

            if (empty($data['image'])) {
                $stmt->bindValue(':image', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);
            }

            $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);

            if (empty($data['role_id'])) {
                $stmt->bindValue(':role_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':role_id', (int) $data['role_id'], PDO::PARAM_INT);
            }

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                DashboardCache::forget('user_stats');
                DashboardCache::forget('users_by_status');
                DashboardCache::forget('recent_users');
                DashboardCache::forget('users_by_month');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates only the fields a user can edit from their own profile.
     *
     * @param int   $id
     * @param array $data  Keys: phone, address, image
     * @return bool
     */
    public function updateProfile($id, $data)
    {
        try {
            $query = "UPDATE {$this->tabla} SET phone = :phone, address = :address, image = :image WHERE id = :id";
            $stmt  = $this->connection->prepare($query);

            if (empty($data['phone'])) {
                $stmt->bindValue(':phone', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            }

            if (empty($data['address'])) {
                $stmt->bindValue(':address', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
            }

            if (empty($data['image'])) {
                $stmt->bindValue(':image', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);
            }

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a user's password.
     *
     * @param int    $id
     * @param string $password  Plain-text password (will be hashed)
     * @return bool
     */
    public function updatePassword($id, $password)
    {
        try {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $stmt  = $this->connection->prepare(
                "UPDATE {$this->tabla} SET password = :password WHERE id = :id"
            );
            $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':id',       $id,   PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a user's status.
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

            if ($stmt->execute()) {
                DashboardCache::forget('user_stats');
                DashboardCache::forget('users_by_status');
                DashboardCache::forget('recent_users');
                DashboardCache::forget('users_by_month');
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Activates a user.
     *
     * @param int $id
     * @return bool
     */
    public function activate($id)
    {
        return $this->updateStatus($id, 1);
    }

    /**
     * Deactivates a user.
     *
     * @param int $id
     * @return bool
     */
    public function deactivate($id)
    {
        return $this->updateStatus($id, 0);
    }

    /**
     * Checks whether an email address is already registered.
     *
     * @param string   $email
     * @param int|null $excludeId  Exclude this user ID from the check
     * @return bool
     */
    public function emailExists($email, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) FROM {$this->tabla} WHERE email = :email AND id != :id";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':email', $email,     PDO::PARAM_STR);
                $stmt->bindParam(':id',    $excludeId, PDO::PARAM_INT);
            } else {
                $query = "SELECT COUNT(*) FROM {$this->tabla} WHERE email = :email";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the status of a user identified by email.
     *
     * @param string $email
     * @return int|null
     */
    public function getStatusByEmail($email)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT status FROM {$this->tabla} WHERE email = :email"
            );
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Checks whether a document number is already registered.
     *
     * @param string $documentNumber
     * @return bool
     */
    public function documentNumberExists($documentNumber)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->tabla} WHERE document_number = :document_number"
            );
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns a user ID by document number.
     *
     * @param string $documentNumber
     * @return int|null
     */
    public function getIdByDocumentNumber($documentNumber)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id FROM {$this->tabla} WHERE document_number = :document_number"
            );
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Returns a user ID by email address.
     *
     * @param string $email
     * @return int|null
     */
    public function getIdByEmail($email)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT id FROM {$this->tabla} WHERE email = :email"
            );
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Returns the status of a user by ID.
     *
     * @param int $id
     * @return int|null
     */
    public function getStatusById($id)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT status FROM {$this->tabla} WHERE id = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Verifies login credentials by email.
     *
     * @param string $email
     * @param string $password
     * @return array|false  User row on success, false otherwise
     */
    public function loginByEmail($email, $password)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->tabla} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Verifies login credentials by document number.
     *
     * @param string $documentNumber
     * @param string $password
     * @return array|false
     */
    public function loginByDocumentNumber($documentNumber, $password)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->tabla} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.document_number = :document_number
            ");
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Checks whether a document type + number combination is already registered.
     *
     * @param string   $documentType
     * @param string   $documentNumber
     * @param int|null $excludeId
     * @return bool
     */
    public function documentTypeExists($documentType, $documentNumber, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) FROM {$this->tabla}
                          WHERE document_type = :document_type
                            AND document_number = :document_number
                            AND id != :id";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':document_type',   $documentType,   PDO::PARAM_STR);
                $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
                $stmt->bindParam(':id',              $excludeId,      PDO::PARAM_INT);
            } else {
                $query = "SELECT COUNT(*) FROM {$this->tabla}
                          WHERE document_type = :document_type AND document_number = :document_number";
                $stmt  = $this->connection->prepare($query);
                $stmt->bindParam(':document_type',   $documentType,   PDO::PARAM_STR);
                $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Validates user data before create or update.
     *
     * @param array    $data
     * @param int|null $excludeId
     * @return array  List of error messages (empty = valid)
     */
    public function validateData($data, $excludeId = null)
    {
        $errors   = [];
        $isUpdate = ($excludeId !== null);

        if (!$isUpdate) {
            if (
                empty($data['name']) ||
                empty($data['first_surname']) ||
                empty($data['document_type']) ||
                empty($data['document_number']) ||
                empty($data['email']) ||
                empty($data['password'])
            ) {
                $errors[] = 'All required fields must be filled in.';
            }
        } else {
            $required = ['name', 'first_surname', 'document_type', 'document_number', 'email'];
            $missing  = [];
            foreach ($required as $field) {
                if (isset($data[$field]) && empty($data[$field])) {
                    $missing[] = $field;
                }
            }
            if (!empty($missing)) {
                $errors[] = 'The following required fields cannot be empty: ' . implode(', ', $missing);
            }
        }

        if (!empty($data['email'])) {
            if ($this->emailExists($data['email'], $excludeId)) {
                $errors[] = 'This email address is already registered.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'The email address format is invalid.';
            }
        }

        if (!empty($data['document_type']) && !empty($data['document_number'])) {
            if ($this->documentTypeExists($data['document_type'], $data['document_number'], $excludeId)) {
                $errors[] = 'A user with this document type and number already exists.';
            }
        }

        if ((!$isUpdate && isset($data['password'])) || !empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }
        }

        return $errors;
    }

    /**
     * Validates profile-editable fields (phone, address).
     *
     * @param array $data
     * @return array
     */
    public function validateProfileData($data)
    {
        $errors = [];

        if (!empty($data['phone']) && !preg_match('/^\+?[0-9]{7,15}$/', $data['phone'])) {
            $errors[] = 'Phone must contain between 7 and 15 numeric digits.';
        }

        if (!empty($data['address']) && strlen($data['address']) > 255) {
            $errors[] = 'Address cannot exceed 255 characters.';
        }

        return $errors;
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

    /**
     * Updates only the image of a user.
     *
     * @param int         $id
     * @param string|null $image  Filename or null to clear
     * @return bool
     */
    public function updateImage($id, $image)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->tabla} SET image = :image WHERE id = :id"
            );
            if ($image === null) {
                $stmt->bindValue(':image', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':image', $image, PDO::PARAM_STR);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

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
                "SELECT password FROM {$this->tabla} WHERE id = :id"
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

    /**
     * Updates the permissions timestamp for a user.
     * Call after any permission change to invalidate the session cache.
     *
     * @param int $userId
     * @return bool
     */
    public function updatePermissionsTimestamp($userId)
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE {$this->tabla} SET permissions_updated_at = NOW() WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the permissions_updated_at timestamp for a user.
     *
     * @param int $userId
     * @return string|null  Timestamp string or null
     */
    public function getPermissionsTimestamp($userId)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT permissions_updated_at FROM {$this->tabla} WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

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
     * Persist password reset token and expiry for a user identified by email.
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
                "UPDATE {$this->tabla} SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email"
            );
            $stmt->bindParam(':token', $token,  PDO::PARAM_STR);
            $stmt->bindParam(':expiry', $expiry, PDO::PARAM_STR);
            $stmt->bindParam(':email',  $email,  PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Finds a user by a valid reset token.
     *
     * @param string $token
     * @return array|false
     */
    public function getUserByResetToken($token)
    {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->tabla} WHERE reset_token = :token AND reset_token_expiry > NOW()"
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
     * @param string $newPassword Plain-text password
     * @return bool
     */
    public function resetPassword($id, $newPassword)
    {
        try {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare(
                "UPDATE {$this->tabla} SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id"
            );
            $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':id',       $id,   PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function setRememberToken(int $userId, string $tokenHash, string $expires): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE users SET remember_token = :token, remember_token_expires = :expires WHERE id = :id"
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

    public function findByRememberToken(string $tokenHash): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM users u
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

    public function clearRememberToken(int $userId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE users SET remember_token = NULL, remember_token_expires = NULL WHERE id = :id"
            );
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns total, active and inactive user counts.
     *
     * @return array{total: int, active: int, inactive: int}
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
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total'    => (int) $row['total'],
                'active'   => (int) $row['active'],
                'inactive' => (int) $row['inactive'],
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }

    /**
     * Returns the most recently registered users.
     *
     * @param int $limit Number of users to return
     * @return array
     */
    public function getRecent($limit = 5)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT id, name, first_surname, email, status, created_at
                FROM {$this->tabla}
                ORDER BY created_at DESC, id DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns active vs inactive user counts for the donut chart.
     *
     * @return array{active: int, inactive: int}
     */
    public function getUsersByStatus(): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS inactive
                FROM {$this->tabla}
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'active'   => (int) ($row['active']   ?? 0),
                'inactive' => (int) ($row['inactive']  ?? 0),
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['active' => 0, 'inactive' => 0];
        }
    }

    // -------------------------------------------------------------------------
    // Lookup methods (resolve user without password verification)
    // -------------------------------------------------------------------------

    /**
     * Returns a user row by email without verifying the password.
     * Used by the login flow to resolve the user before throttle checks.
     *
     * @param  string $email
     * @return array|false
     */
    public function findByEmail(string $email): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->tabla} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email
                LIMIT 1
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns a user row by document number without verifying the password.
     *
     * @param  string $documentNumber
     * @return array|false
     */
    public function findByDocumentNumber(string $documentNumber): array|false
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT u.*, r.name AS role_name, r.is_system AS role_is_system
                FROM {$this->tabla} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.document_number = :document_number
                LIMIT 1
            ");
            $stmt->bindParam(':document_number', $documentNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Login throttle methods
    // -------------------------------------------------------------------------

    /**
     * Increments the failed login counter for a user and sets locked_until
     * when the max-attempts threshold is reached. All changes land in one UPDATE.
     *
     * @param  int   $userId
     * @return array Updated row with keys: login_attempts, locked_until, last_attempt_at
     */
    public function recordFailure(int $userId): array
    {
        try {
            $maxAttempts    = (int) env('LOGIN_MAX_ATTEMPTS', 5);
            $lockoutMinutes = (int) env('LOGIN_LOCKOUT_MINUTES', 15);

            $stmt = $this->connection->prepare("
                UPDATE {$this->tabla}
                SET
                    login_attempts  = login_attempts + 1,
                    last_attempt_at = NOW(),
                    locked_until    = IF(
                        login_attempts + 1 >= :max,
                        DATE_ADD(NOW(), INTERVAL :minutes MINUTE),
                        locked_until
                    )
                WHERE id = :id
            ");
            $stmt->bindValue(':max',     $maxAttempts,    PDO::PARAM_INT);
            $stmt->bindValue(':minutes', $lockoutMinutes, PDO::PARAM_INT);
            $stmt->bindParam(':id',      $userId,         PDO::PARAM_INT);
            $stmt->execute();

            // Return the fresh throttle state
            $stmt2 = $this->connection->prepare("
                SELECT login_attempts, locked_until, last_attempt_at
                FROM {$this->tabla} WHERE id = :id
            ");
            $stmt2->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt2->execute();
            return $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Resets all throttle columns after a successful login.
     *
     * @param  int  $userId
     * @return void
     */
    public function clearAttempts(int $userId): void
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE {$this->tabla}
                SET login_attempts = 0, locked_until = NULL, last_attempt_at = NULL
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Manually unlocks a user (admin action). Identical to clearAttempts
     * but returns bool so the controller can confirm success.
     *
     * @param  int  $userId
     * @return bool
     */
    public function unlock(int $userId): bool
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE {$this->tabla}
                SET login_attempts = 0, locked_until = NULL, last_attempt_at = NULL
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Evaluates whether a user is currently locked out (lazy: checks locked_until vs NOW()).
     * Does NOT write to the DB — the Service layer decides whether to reset.
     *
     * @param  int   $userId
     * @return array{locked: bool, remaining_seconds: int}
     */
    public function getLockStatus(int $userId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    locked_until,
                    GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until)) AS remaining_seconds
                FROM {$this->tabla}
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['locked_until'] === null) {
                return ['locked' => false, 'remaining_seconds' => 0];
            }

            $remaining = (int) $row['remaining_seconds'];
            return [
                'locked'            => $remaining > 0,
                'remaining_seconds' => $remaining,
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['locked' => false, 'remaining_seconds' => 0];
        }
    }

    /**
     * Returns user registration counts grouped by month for the line chart.
     * Always returns exactly $months entries (zero-filled for empty months).
     *
     * @param int $months Number of months to look back (including current)
     * @return array  Each element: ['ym' => 'YYYY-MM', 'total' => int]
     */
    public function getUsersByMonth(int $months = 6): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
                FROM {$this->tabla}
                WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL :offset MONTH), '%Y-%m-01')
                GROUP BY ym
                ORDER BY ym ASC
            ");
            $stmt->bindValue(':offset', $months - 1, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Index DB results by 'YYYY-MM'
            $byMonth = [];
            foreach ($rows as $row) {
                $byMonth[$row['ym']] = (int) $row['total'];
            }

            // Build full range, filling gaps with 0
            $result = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $ym = date('Y-m', strtotime("-{$i} months"));
                $result[] = ['ym' => $ym, 'total' => $byMonth[$ym] ?? 0];
            }

            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }
}
