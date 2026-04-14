<?php

/**
 * User Controller
 *
 * Handles user management operations.
 *
 * @package ProyectoBase
 * @subpackage App\Controllers\Users
 * @author Jandres25
 * @version 1.0
 */

namespace App\Controllers\Users;

use App\Models\User;
use App\Services\ImageService;
use App\Services\AuthorizationService;

class UserController
{
    /**
     * User model
     * @var User
     */
    private $model;

    /**
     * Image service
     * @var ImageService
     */
    private $imageService;

    public function __construct()
    {
        $this->model        = new User();
        $this->imageService = new ImageService(__DIR__ . '/../../../public/uploads/users/');
    }

    /**
     * Returns all users.
     *
     * @return array
     */
    public function index()
    {
        return $this->model->getAll();
    }

    /**
     * Renders the create-user form.
     */
    public function create()
    {
        require_once __DIR__ . '/../../../views/users/create.php';
    }

    /**
     * Maps POST data to the user data array using English field names.
     *
     * @param array $postData
     * @return array
     */
    private function prepareUserData($postData)
    {
        return [
            'name'            => isset($postData['name'])            ? trim($postData['name'])            : '',
            'first_surname'   => isset($postData['first_surname'])   ? trim($postData['first_surname'])   : '',
            'second_surname'  => !empty($postData['second_surname'])  ? trim($postData['second_surname'])  : null,
            'document_type'   => isset($postData['document_type'])   ? trim($postData['document_type'])   : '',
            'document_number' => isset($postData['document_number']) ? trim($postData['document_number']) : '',
            'address'         => !empty($postData['address'])  ? trim($postData['address'])  : null,
            'phone'           => !empty($postData['phone'])    ? trim($postData['phone'])    : null,
            'email'           => !empty($postData['email'])    ? trim($postData['email'])    : '',
            'position'        => !empty($postData['position']) ? trim($postData['position']) : '',
            'password'        => isset($postData['password'])  ? trim($postData['password']) : '',
            'status'          => isset($postData['status'])    ? (int)$postData['status']    : 1,
            'image'           => null,
        ];
    }

    /**
     * Processes the create-user form and persists the new user.
     *
     * @return array  Result with keys: success, message, icon, redirect
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Access not allowed.', 'icon' => 'warning', 'redirect' => 'index.php'];
        }

        $data   = $this->model->sanitizeData($this->prepareUserData($_POST));
        $errors = $this->model->validateData($data);

        if (!empty($errors)) {
            return ['success' => false, 'message' => $errors[0], 'icon' => 'error', 'redirect' => 'create.php'];
        }

        $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        if ($data['password'] !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match.', 'icon' => 'error', 'redirect' => 'create.php'];
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imagePath = $this->imageService->processImage($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
            } else {
                return ['success' => false, 'message' => 'Error processing image. Check format and size.', 'icon' => 'error', 'redirect' => 'create.php'];
            }
        }

        if ($this->model->create($data)) {
            $userId = $this->model->getLastInsertId();
            $this->processPermissions($userId, $_POST);
            return ['success' => true, 'message' => 'User created successfully.', 'icon' => 'success', 'redirect' => 'index.php'];
        }

        return ['success' => false, 'message' => 'Error creating user: ' . $this->model->getLastError(), 'icon' => 'error', 'redirect' => 'create.php'];
    }

    /**
     * Returns the user data for the edit form, or redirects if not found.
     *
     * @param int|null $id
     * @return array
     */
    public function edit($id = null)
    {
        if (!$id) {
            global $URL;
            $_SESSION['message'] = 'Invalid user ID.';
            $_SESSION['icon']    = 'error';
            header('Location: ' . $URL . 'views/users');
            exit;
        }

        $user = $this->model->getById($id);

        if (!$user) {
            global $URL;
            $_SESSION['message'] = 'User not found.';
            $_SESSION['icon']    = 'error';
            header('Location: ' . $URL . 'views/users');
            exit;
        }

        return $user;
    }

    /**
     * Processes the update-user form and persists the changes.
     *
     * @return array
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Access not allowed.', 'icon' => 'warning', 'redirect' => 'index.php'];
        }

        $id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

        if (!$id) {
            return ['success' => false, 'message' => 'Invalid user ID.', 'icon' => 'error', 'redirect' => 'index.php'];
        }

        $currentUser = $this->model->getById($id);
        if (!$currentUser) {
            return ['success' => false, 'message' => 'User not found.', 'icon' => 'error', 'redirect' => 'index.php'];
        }

        $oldImage = $currentUser['image'];
        $data     = $this->prepareUserData($_POST);

        // Preserve the current status (not editable from the edit form)
        $data['status'] = (int)$currentUser['status'];

        // Keep existing values for required fields if not submitted
        $required = ['name', 'first_surname', 'document_type', 'document_number', 'email', 'position'];
        foreach ($required as $field) {
            if (empty($data[$field]) && isset($currentUser[$field])) {
                $data[$field] = $currentUser[$field];
            }
        }

        $data  = $this->model->sanitizeData($data);
        $data['image'] = $oldImage;

        $errors = $this->model->validateData($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'message' => $errors[0], 'icon' => 'error', 'redirect' => "update.php?id=$id"];
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $newImagePath = $this->imageService->processImage($_FILES['image']);
            if ($newImagePath) {
                $data['image'] = $newImagePath;
                if ($oldImage && $oldImage !== 'user_default.jpg') {
                    $this->imageService->deleteImage($oldImage);
                }
            } else {
                return ['success' => false, 'message' => 'Error processing the new image. Check format and size.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
            }
        }

        $updated = $this->model->update($id, $data);

        $password        = isset($_POST['password'])         ? trim($_POST['password'])         : '';
        $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        $passwordUpdated = true;

        if (!empty($password) || !empty($confirmPassword)) {
            if (empty($password) || empty($confirmPassword)) {
                return ['success' => false, 'message' => 'Both password fields are required to change the password.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
            }
            if ($password !== $confirmPassword) {
                return ['success' => false, 'message' => 'Passwords do not match.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
            }
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
            }
            $passwordUpdated = $this->model->updatePassword($id, $password);
        }

        if ($updated && $passwordUpdated) {
            $this->processPermissions($id, $_POST);
            return ['success' => true, 'message' => 'User updated successfully.', 'icon' => 'success', 'redirect' => 'index.php'];
        }

        $errorMsg = 'Error updating user.';
        if (!$updated) {
            $dbError   = $this->model->getLastError();
            $errorMsg .= ' ' . ($dbError ?: 'Unknown error.');
        }
        if (!$passwordUpdated) {
            $errorMsg .= ' Password update failed.';
        }
        return ['success' => false, 'message' => $errorMsg, 'icon' => 'error', 'redirect' => "update.php?id=$id"];
    }

    /**
     * Handles the AJAX password change from the profile page.
     *
     * @return array
     */
    public function updateProfilePasswordAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Access not allowed.'];
        }

        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Session not started.'];
        }

        $id              = $_SESSION['user_id'];
        $currentPassword = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $newPassword     = isset($_POST['new_password'])     ? trim($_POST['new_password'])     : '';
        $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return ['success' => false, 'message' => 'All password fields are required.'];
        }

        if (!$this->model->verifyCurrentPassword($id, $currentPassword)) {
            return ['success' => false, 'message' => 'The current password is incorrect.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'New passwords do not match.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        if ($this->model->updatePassword($id, $newPassword)) {
            return ['success' => true, 'message' => 'Password updated successfully.'];
        }

        return ['success' => false, 'message' => 'Error updating password: ' . $this->model->getLastError()];
    }

    /**
     * Toggles a user's active/inactive status.
     *
     * @param int|null $id
     * @param int|null $currentStatus
     * @return array
     */
    public function toggleUserStatus($id = null, $currentStatus = null)
    {
        if ($id === null || $currentStatus === null) {
            return ['success' => false, 'message' => 'Invalid user ID or status.', 'icon' => 'error'];
        }

        $newStatus = $currentStatus == 1 ? 0 : 1;

        if ($this->model->updateStatus($id, $newStatus)) {
            $action = $newStatus == 1 ? 'activated' : 'deactivated';
            return ['success' => true, 'message' => "User $action successfully.", 'icon' => 'success'];
        }

        return ['success' => false, 'message' => 'Error changing user status: ' . $this->model->getLastError(), 'icon' => 'error'];
    }

    /**
     * Syncs the permissions selected in the form with the user's assigned permissions.
     *
     * @param int   $userId
     * @param array $postData
     */
    private function processPermissions($userId, $postData)
    {
        $authService      = new AuthorizationService();
        $allPermissions   = $authService->getAllPermissions();
        $selectedIds      = isset($postData['permissions']) ? $postData['permissions'] : [];

        foreach ($allPermissions as $permission) {
            $permId = $permission['id'];
            if (in_array($permId, $selectedIds)) {
                $authService->assignPermission($userId, $permId);
            } else {
                $authService->revokePermission($userId, $permId);
            }
        }

        $this->model->updatePermissionsTimestamp($userId);

        if ($userId === ($_SESSION['user_id'] ?? null)) {
            $permissions = $authService->getUserPermissions($userId);
            $_SESSION['user_permissions'] = array_column($permissions, 'name');
            $_SESSION['permissions_ts']   = date('Y-m-d H:i:s');
        }
    }
}
