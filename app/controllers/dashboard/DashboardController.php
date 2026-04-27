<?php

namespace App\Controllers\Dashboard;

use App\Core\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuthorizationService;

class DashboardController extends Controller
{
    public function index(): void
    {
        $userModel       = new User();
        $permissionModel = new Permission();
        $authService     = new AuthorizationService();
        $currentUserId   = (int) ($_SESSION['user_id'] ?? 0);

        $this->render(
            'dashboard/index',
            [
                'userStats'           => $userModel->getStatistics(),
                'permStats'           => $permissionModel->getStatistics(),
                'recentUsers'         => $userModel->getRecent(5),
                'canManageUsers'      => $authService->hasPermissionByName($currentUserId, 'users'),
                'canManagePermissions'=> $authService->hasPermissionByName($currentUserId, 'permissions'),
            ]
        );
    }
}
