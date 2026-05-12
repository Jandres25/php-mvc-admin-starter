<?php

namespace App\Controllers\Dashboard;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Permission;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): void
    {
        $userModel       = new User();
        $permissionModel = new Permission();

        $this->render(
            'dashboard/index',
            [
                'userStats'            => $userModel->getStatistics(),
                'permStats'            => $permissionModel->getStatistics(),
                'recentUsers'          => $userModel->getRecent(5),
                'canManageUsers'       => Auth::hasPermission('users'),
                'canManagePermissions' => Auth::hasPermission('permissions'),
            ]
        );
    }
}
