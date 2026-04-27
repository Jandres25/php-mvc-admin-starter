<?php

namespace App\Controllers\Permissions;

use App\Core\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuthorizationService;

class PermissionController extends Controller
{
    private $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new Permission();
    }

    public function pageIndex()
    {
        $permissions = [];
        foreach ($this->permissionModel->getAllWithUserCount() as $permission) {
            $permissions[] = $this->mapIndexRow($permission);
        }
        $statistics = $this->permissionModel->getStatistics();

        $this->render(
            'permissions/index',
            compact('permissions', 'statistics'),
            ['datatables', 'datatables-export'],
            ['permissions/modal-permission', 'permissions/index-permissions']
        );
    }

    public function detail($id = null)
    {
        $permission = $this->getPermissionOrRedirect((int) $id);

        $users           = $permission['users'] ?? [];
        $usersWithoutPerm = $this->permissionModel->getUsersWithoutPermission((int) $permission['id']);
        $isInactive      = ((int) $permission['status']) === 0;
        $permissionId    = (int) $permission['id'];

        $this->render(
            'permissions/detail',
            compact('permission', 'users', 'usersWithoutPerm', 'isInactive', 'permissionId'),
            ['datatables', 'select2'],
            ['permissions/modal-permission', 'permissions/detail-permission']
        );
    }

    public function create()
    {
        $this->csrfCheck();

        $data = [
            'name'        => trim($_POST['name']        ?? ''),
            'description' => trim($_POST['description'] ?? '') ?: null,
        ];

        $data = $this->permissionModel->sanitizeData($data);

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission name is required.']);
        }

        if ($this->permissionModel->create($data)) {
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permission created successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => 'Permission created successfully.',
                'permission' => [
                    'id'          => $this->permissionModel->getLastInsertId(),
                    'name'        => $data['name'],
                    'status'      => 1,
                    'total_users' => 0,
                ],
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->permissionModel->getLastError()]);
    }

    public function update()
    {
        $this->csrfCheck();

        $id = (int) ($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid permission ID.']);
        }

        $current = $this->permissionModel->getById($id);
        if (!$current) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission not found.']);
        }

        $data = [
            'name'        => trim($_POST['name']        ?? '') ?: $current['name'],
            'description' => trim($_POST['description'] ?? '') ?: $current['description'],
        ];

        $data = $this->permissionModel->sanitizeData($data);

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission name is required.']);
        }

        if ($this->permissionModel->update($id, $data)) {
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permission updated successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => 'Permission updated successfully.',
                'permission' => [
                    'id'          => $id,
                    'name'        => $data['name'],
                    'status'      => $current['status'],
                    'total_users' => $this->permissionModel->countUsers($id),
                ],
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->permissionModel->getLastError()]);
    }

    public function toggleStatus()
    {
        $this->csrfCheck();

        $id            = filter_var($_POST['id']             ?? null, FILTER_VALIDATE_INT);
        $currentStatus = filter_var($_POST['current_status'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $currentStatus === false) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        if ($currentStatus == 1 && $this->permissionModel->countUsers($id) > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot deactivate this permission because it has assigned users.']);
        }

        $newStatus = $currentStatus == 1 ? 0 : 1;

        if ($this->permissionModel->updateStatus($id, $newStatus)) {
            regenerateCSRFToken();
            $label = $newStatus == 1 ? 'activated' : 'deactivated';
            $_SESSION['message'] = "Permission $label successfully.";
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => "Permission $label successfully.",
                'new_status' => $newStatus,
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Error changing permission status: ' . $this->permissionModel->getLastError()]);
    }

    public function assignUser()
    {
        $this->csrfCheck();

        $authService  = new AuthorizationService();
        $userId       = filter_var($_POST['user_id']       ?? 0, FILTER_VALIDATE_INT);
        $permissionId = filter_var($_POST['permission_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$userId || !$permissionId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        $ok = $authService->assignPermission($userId, $permissionId);
        if ($ok) {
            $userModel = new User();
            $userModel->updatePermissionsTimestamp($userId);
            regenerateCSRFToken();
            $_SESSION['message'] = 'User assigned successfully.';
            $_SESSION['icon']    = 'success';
        }

        $this->jsonResponse(['success' => (bool) $ok, 'message' => $ok ? 'User assigned successfully.' : 'Error assigning user.']);
    }

    public function revokeUser()
    {
        $this->csrfCheck();

        $authService  = new AuthorizationService();
        $userId       = filter_var($_POST['user_id']       ?? 0, FILTER_VALIDATE_INT);
        $permissionId = filter_var($_POST['permission_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$userId || !$permissionId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        $ok = $authService->revokePermission($userId, $permissionId);
        if ($ok) {
            $userModel = new User();
            $userModel->updatePermissionsTimestamp($userId);
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permission revoked successfully.';
            $_SESSION['icon']    = 'success';
        }

        $this->jsonResponse(['success' => (bool) $ok, 'message' => $ok ? 'Permission revoked successfully.' : 'Error revoking permission.']);
    }

    public function getUsersWithout()
    {
        $permissionId = filter_var($_GET['permission_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$permissionId) {
            $this->jsonResponse([]);
        }

        $users  = $this->permissionModel->getUsersWithoutPermission($permissionId);
        $result = [];
        foreach ($users as $u) {
            $name     = htmlspecialchars(trim($u['name'] . ' ' . $u['first_surname'] . ' ' . ($u['second_surname'] ?? '')), ENT_QUOTES, 'UTF-8');
            $position = $u['position'] ? ' — ' . htmlspecialchars($u['position'], ENT_QUOTES, 'UTF-8') : '';
            $result[] = ['id' => $u['id'], 'text' => $name . $position];
        }

        $this->jsonResponse($result);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getPermissionOrRedirect($id)
    {
        if ($id <= 0) {
            $_SESSION['message'] = 'Invalid permission ID.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'permissions');
        }

        $permission = $this->permissionModel->getById($id);
        if (!$permission) {
            $_SESSION['message'] = 'Permission not found.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'permissions');
        }

        $permission['total_users'] = $this->permissionModel->countUsers($id);
        $permission['users']       = $this->permissionModel->getUsersByPermission($id);

        return $permission;
    }

    private function mapIndexRow(array $permission)
    {
        $isActive   = ((int) $permission['status']) === 1;
        $totalUsers = isset($permission['total_users']) ? (int) $permission['total_users'] : 0;

        return [
            'id'                 => (int) $permission['id'],
            'name'               => $permission['name'] ?? '',
            'description'        => !empty($permission['description']) ? $permission['description'] : 'N/A',
            'description_raw'    => $permission['description'] ?? '',
            'status'             => (int) $permission['status'],
            'status_label'       => $isActive ? 'Active' : 'Inactive',
            'status_badge_class' => $isActive ? 'badge-success' : 'badge-danger',
            'status_btn_class'   => $isActive ? 'btn-danger' : 'btn-success',
            'status_btn_title'   => $isActive ? 'Deactivate' : 'Activate',
            'status_icon_class'  => $isActive ? 'fa-times' : 'fa-check',
            'total_users'        => $totalUsers,
            'users_badge_class'  => $totalUsers > 0 ? 'badge-primary' : 'badge-secondary',
            'users_label'        => $totalUsers === 1 ? 'user' : 'users',
        ];
    }
}
