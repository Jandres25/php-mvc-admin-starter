<?php

namespace App\Controllers\Dashboard;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardCache;

class DashboardController extends Controller
{
    public function index(): void
    {
        $userModel       = new User();
        $permissionModel = new Permission();
        $roleModel       = new Role();

        $userStats    = DashboardCache::remember('user_stats',      fn() => $userModel->getStatistics());
        $permStats    = DashboardCache::remember('perm_stats',      fn() => $permissionModel->getStatistics());
        $roleStats    = DashboardCache::remember('role_stats',      fn() => $roleModel->getStatistics());
        $recentUsers  = DashboardCache::remember('recent_users',    fn() => $userModel->getRecent(5));
        $usersByStatus = DashboardCache::remember('users_by_status', fn() => $userModel->getUsersByStatus());
        $topPerms     = DashboardCache::remember('top_permissions', fn() => $permissionModel->getTopAssigned(5));
        $usersByMonth = DashboardCache::remember('users_by_month',  fn() => $userModel->getUsersByMonth(6));

        $this->render(
            'dashboard/index',
            [
                'userStats'            => $userStats,
                'permStats'            => $permStats,
                'recentUsers'          => $recentUsers,
                'roleStats'            => $roleStats,
                'canManageUsers'       => Auth::hasPermission('users'),
                'canManagePermissions' => Auth::hasPermission('permissions'),
                'canManageRoles'       => Auth::hasPermission('roles'),
                'chartData'            => [
                    'usersByStatus' => $usersByStatus,
                    'topPerms'      => $topPerms,
                    'usersByMonth'  => $usersByMonth,
                ],
            ],
            ['chart'],
            ['dashboard/index-dashboard']
        );
    }
}
