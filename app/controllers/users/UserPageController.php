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
use Models\User;

class UserPageController extends BaseController
{
    /**
     * User model.
     *
     * @var User
     */
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
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
}
