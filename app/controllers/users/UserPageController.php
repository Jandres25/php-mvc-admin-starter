<?php

/**
 * Users page controller for app-layer view preparation.
 *
 * @package ProyectoBase
 * @subpackage App\Controllers\Users
 * @author Jandres25
 * @version 1.0
 */

namespace App\Controllers\Users;

use App\Core\BaseController;
use App\Models\User;
use App\Services\AuthorizationService;

class UserPageController extends BaseController
{
    /**
     * User model.
     *
     * @var User
     */
    private $userModel;
    /**
     * Authorization service.
     *
     * @var AuthorizationService
     */
    private $authService;

    public function __construct()
    {
        parent::__construct();
        $this->userModel   = new User();
        $this->authService = new AuthorizationService();
    }

    /**
     * Builds view-model data for users/index.php.
     *
     * @return array
     */
    public function buildIndexViewData(): array
    {
        $rows = [];
        $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        foreach ($this->userModel->getAll() as $user) {
            $rows[] = $this->mapIndexRow($user, $currentUserId);
        }

        return ['users' => $rows];
    }

    /**
     * Builds view-model data for users/show.php.
     *
     * @return array
     */
    public function buildShowViewDataFromRequest(): array
    {
        $user = $this->resolveUserFromRequest();
        $userId = (int)$user['id'];

        return [
            'user' => $user,
            'user_permissions' => $this->authService->getUserPermissions($userId),
            'is_admin_user' => $this->authService->isAdmin($userId),
        ];
    }

    /**
     * Builds view-model data for users/update.php.
     *
     * @return array
     */
    public function buildUpdateViewDataFromRequest(): array
    {
        $user = $this->resolveUserFromRequest();
        $userId = (int)$user['id'];

        return [
            'user' => $user,
            'all_permissions' => $this->authService->getAllPermissions(),
            'assigned_permissions' => $this->authService->getAssignedPermissions($userId),
        ];
    }

    /**
     * Builds view-model data for users/create.php.
     *
     * @return array
     */
    public function buildCreateViewData(): array
    {
        return [
            'all_permissions' => $this->authService->getAllPermissions(),
        ];
    }

    /**
     * Builds view-model data for users/profile.php.
     *
     * @return array
     */
    public function buildProfileViewData(): array
    {
        $user = $this->resolveCurrentUserFromSession();
        $imageName = !empty($user['image']) ? $user['image'] : 'user_default.jpg';

        return [
            'user' => $user,
            'image_src' => 'public/uploads/users/' . $imageName,
        ];
    }

    /**
     * Prepares one user row for index table rendering.
     *
     * @param array    $user
     * @param int|null $currentUserId
     * @return array
     */
    private function mapIndexRow(array $user, ?int $currentUserId): array
    {
        $isActive = ((int)$user['status']) === 1;
        $hasImage = !empty($user['image']);

        return [
            'id' => (int)$user['id'],
            'name' => $user['name'] ?? '',
            'first_surname' => $user['first_surname'] ?? '',
            'document_type' => $user['document_type'] ?? '',
            'document_number' => $user['document_number'] ?? '',
            'email' => $user['email'] ?? '',
            'image' => $hasImage ? $user['image'] : 'user_default.jpg',
            'position_label' => !empty($user['position']) ? $user['position'] : 'N/A',
            'status' => (int)$user['status'],
            'status_label' => $isActive ? 'Active' : 'Inactive',
            'status_badge_class' => $isActive ? 'badge-success' : 'badge-danger',
            'status_btn_class' => $isActive ? 'btn-danger' : 'btn-success',
            'status_icon_class' => $isActive ? 'fa-user-slash' : 'fa-user-check',
            'alert_title' => $isActive ? 'Deactivate User?' : 'Activate User?',
            'confirm_button_text' => $isActive ? 'Yes, deactivate' : 'Yes, activate',
            'can_toggle_status' => ((int)$user['id']) !== $currentUserId,
        ];
    }

    /**
     * Resolves and validates the user requested via query string.
     *
     * @return array
     */
    private function resolveUserFromRequest(): array
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $this->redirectToUsersWithError('Invalid user ID.');
        }

        $user = $this->userModel->getById($id);
        if (!$user) {
            $this->redirectToUsersWithError('User not found.');
        }

        return $user;
    }

    /**
     * Resolves current user from session.
     *
     * @return array
     */
    private function resolveCurrentUserFromSession(): array
    {
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($userId <= 0) {
            $this->redirectWithError('You must log in to access your profile.', 'views/auth/login.php', 'warning');
        }

        $user = $this->userModel->getById($userId);
        if (!$user) {
            $this->redirectWithError('User not found.', 'index.php');
        }

        return $user;
    }

    /**
     * Redirects to users index with flash error.
     *
     * @param string $message
     * @return void
     */
    private function redirectToUsersWithError(string $message): void
    {
        $this->redirectWithError($message, 'views/users');
    }

    /**
     * Redirects to a path with flash message.
     *
     * @param string $message
     * @param string $path
     * @param string $icon
     * @return void
     */
    private function redirectWithError(string $message, string $path, string $icon = 'error'): void
    {
        global $URL;

        $_SESSION['message'] = $message;
        $_SESSION['icon']    = $icon;
        header('Location: ' . $URL . ltrim($path, '/'));
        exit;
    }
}
