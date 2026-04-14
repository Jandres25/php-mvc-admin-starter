<?php

/**
 * Permissions page controller for app-layer view preparation.
 *
 * @package ProyectoBase
 * @subpackage App\Controllers\Permissions
 * @author Jandres25
 * @version 1.0
 */

namespace App\Controllers\Permissions;

use App\Core\BaseController;
use Models\Permission;

class PermissionPageController extends BaseController
{
    /**
     * Permission model.
     *
     * @var Permission
     */
    private $permissionModel;

    public function __construct()
    {
        parent::__construct();
        $this->permissionModel = new Permission();
    }

    /**
     * Builds view-model data for permissions/index.php.
     *
     * @return array
     */
    public function buildIndexViewData(): array
    {
        $rows = [];
        foreach ($this->permissionModel->getAllWithUserCount() as $permission) {
            $rows[] = $this->mapIndexRow($permission);
        }

        return [
            'statistics' => $this->permissionModel->getStatistics(),
            'permissions' => $rows,
        ];
    }

    /**
     * Builds view-model data for permissions/detail.php from request.
     *
     * @return array
     */
    public function buildDetailViewDataFromRequest(): array
    {
        $permission = $this->resolvePermissionFromRequest();
        $permissionId = (int)$permission['id'];
        $users = $permission['users'] ?? [];

        return [
            'permission' => $permission,
            'users' => $users,
            'users_without_permission' => $this->permissionModel->getUsersWithoutPermission($permissionId),
            'is_inactive' => ((int)$permission['status']) === 0,
            'permission_id' => $permissionId,
        ];
    }

    /**
     * Maps one permission row for index rendering.
     *
     * @param array $permission
     * @return array
     */
    private function mapIndexRow(array $permission): array
    {
        $isActive = ((int)$permission['status']) === 1;
        $totalUsers = isset($permission['total_users']) ? (int)$permission['total_users'] : 0;

        return [
            'id' => (int)$permission['id'],
            'name' => $permission['name'] ?? '',
            'description' => !empty($permission['description']) ? $permission['description'] : 'N/A',
            'description_raw' => $permission['description'] ?? '',
            'status' => (int)$permission['status'],
            'status_label' => $isActive ? 'Active' : 'Inactive',
            'status_badge_class' => $isActive ? 'badge-success' : 'badge-danger',
            'status_btn_class' => $isActive ? 'btn-danger' : 'btn-success',
            'status_btn_title' => $isActive ? 'Deactivate' : 'Activate',
            'status_icon_class' => $isActive ? 'fa-times' : 'fa-check',
            'total_users' => $totalUsers,
            'users_badge_class' => $totalUsers > 0 ? 'badge-primary' : 'badge-secondary',
            'users_label' => $totalUsers === 1 ? 'user' : 'users',
        ];
    }

    /**
     * Resolves and validates requested permission.
     *
     * @return array
     */
    private function resolvePermissionFromRequest(): array
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $this->redirectToPermissionsWithError('Invalid permission ID.');
        }

        $permission = $this->permissionModel->getById($id);
        if (!$permission) {
            $this->redirectToPermissionsWithError('Permission not found.');
        }

        $permission['total_users'] = $this->permissionModel->countUsers($id);
        $permission['users']       = $this->permissionModel->getUsersByPermission($id);

        return $permission;
    }

    /**
     * Redirects to permissions index with flash error.
     *
     * @param string $message
     * @return void
     */
    private function redirectToPermissionsWithError(string $message): void
    {
        global $URL;

        $_SESSION['message'] = $message;
        $_SESSION['icon']    = 'error';
        header('Location: ' . $URL . 'views/permissions');
        exit;
    }
}
