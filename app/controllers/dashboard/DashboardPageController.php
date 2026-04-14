<?php

/**
 * Dashboard page controller for view-model preparation.
 *
 * @package ProyectoBase
 * @subpackage App\Controllers\Dashboard
 * @author Jandres25
 * @version 1.0
 */

namespace App\Controllers\Dashboard;

use App\Models\User;
use App\Models\Permission;
use App\Services\AuthorizationService;

class DashboardPageController
{
    /**
     * User model.
     *
     * @var User
     */
    private $userModel;

    /**
     * Permission model.
     *
     * @var Permission
     */
    private $permissionModel;

    /**
     * Authorization service.
     *
     * @var AuthorizationService
     */
    private $authService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->permissionModel = new Permission();
        $this->authService = new AuthorizationService();
    }

    /**
     * Builds dashboard view data.
     *
     * @return array
     */
    public function buildViewData(): array
    {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);

        return [
            'user_stats' => $this->userModel->getStatistics(),
            'permission_stats' => $this->permissionModel->getStatistics(),
            'recent_users' => $this->userModel->getRecent(5),
            'can_manage_users' => $this->authService->hasPermissionByName($currentUserId, 'users'),
            'can_manage_permissions' => $this->authService->hasPermissionByName($currentUserId, 'permissions'),
        ];
    }
}
