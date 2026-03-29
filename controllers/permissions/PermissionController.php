<?php

/**
 * Permission Controller
 *
 * Manages operations related to permissions.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Permissions
 * @author Jandres25
 * @version 1.0
 */

namespace Controllers\Permissions;

use Models\Permission;

class PermissionController
{
    /**
     * Permission model
     * @var Permission
     */
    private $model;

    public function __construct()
    {
        $this->model = new Permission();
    }

    /**
     * Returns the list of permissions with user count.
     *
     * @param bool $activeOnly If true, returns only active permissions
     * @return array
     */
    public function index($activeOnly = false)
    {
        return $this->model->getAllWithUserCount($activeOnly);
    }

    /**
     * Creates a permission via AJAX.
     *
     * @return array
     */
    public function createAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Method not allowed.'];
        }

        $data = [
            'name'        => isset($_POST['name'])        ? trim($_POST['name'])        : '',
            'description' => isset($_POST['description']) ? trim($_POST['description']) : null,
        ];

        $data = $this->model->sanitizeData($data);

        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'Permission name is required.'];
        }

        if ($this->model->create($data)) {
            return [
                'success'    => true,
                'message'    => 'Permission created successfully.',
                'permission' => [
                    'id'          => $this->model->getLastInsertId(),
                    'name'        => $data['name'],
                    'status'      => 1,
                    'total_users' => 0,
                ],
            ];
        }

        return ['success' => false, 'message' => $this->model->getLastError()];
    }

    /**
     * Returns a permission by ID with user count and assigned users.
     *
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        $permission = $this->model->getById($id);
        if ($permission) {
            $permission['total_users'] = $this->model->countUsers($id);
            $permission['users']       = $this->model->getUsersByPermission($id);
        }
        return $permission;
    }

    /**
     * Updates a permission via AJAX.
     *
     * @return array
     */
    public function updateAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Method not allowed.'];
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if (!$id) {
            return ['success' => false, 'message' => 'Invalid permission ID.'];
        }

        $current = $this->model->getById($id);
        if (!$current) {
            return ['success' => false, 'message' => 'Permission not found.'];
        }

        $data = [
            'name'        => isset($_POST['name'])        ? trim($_POST['name'])        : $current['name'],
            'description' => isset($_POST['description']) ? trim($_POST['description']) : $current['description'],
        ];

        $data = $this->model->sanitizeData($data);

        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'Permission name is required.'];
        }

        if ($this->model->update($id, $data)) {
            return [
                'success'    => true,
                'message'    => 'Permission updated successfully.',
                'permission' => [
                    'id'          => $id,
                    'name'        => $data['name'],
                    'status'      => $current['status'],
                    'total_users' => $this->model->countUsers($id),
                ],
            ];
        }

        return ['success' => false, 'message' => $this->model->getLastError()];
    }

    /**
     * Toggles the status of a permission via AJAX.
     *
     * @return array
     */
    public function toggleStatusAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Method not allowed.'];
        }

        $id            = isset($_POST['id'])             ? (int)$_POST['id']             : 0;
        $currentStatus = isset($_POST['current_status']) ? (int)$_POST['current_status'] : null;

        if (!$id || $currentStatus === null) {
            return ['success' => false, 'message' => 'Invalid data.'];
        }

        if ($currentStatus == 1 && $this->model->countUsers($id) > 0) {
            return ['success' => false, 'message' => 'Cannot deactivate this permission because it has assigned users.'];
        }

        $newStatus = $currentStatus == 1 ? 0 : 1;

        if ($this->model->updateStatus($id, $newStatus)) {
            $label = $newStatus == 1 ? 'activated' : 'deactivated';
            return [
                'success'    => true,
                'message'    => "Permission $label successfully.",
                'new_status' => $newStatus,
            ];
        }

        return ['success' => false, 'message' => 'Error changing permission status: ' . $this->model->getLastError()];
    }

    /**
     * Returns users assigned to a specific permission.
     *
     * @param int $permissionId
     * @return array
     */
    public function getUsersByPermission($permissionId)
    {
        return $this->model->getUsersByPermission($permissionId);
    }

    /**
     * Returns active users without a specific permission (for assignment modal).
     *
     * @param int $permissionId
     * @return array
     */
    public function getUsersWithoutPermission($permissionId)
    {
        return $this->model->getUsersWithoutPermission($permissionId);
    }

    /**
     * Returns permission statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return $this->model->getStatistics();
    }
}
