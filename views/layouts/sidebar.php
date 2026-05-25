<?php

use App\Core\Auth;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(parse_url(URL, PHP_URL_PATH), '/');

/**
 * Returns 'active' if $segment matches the current URI path.
 * $segment is the path after APP_URL base (e.g. 'users', 'roles').
 */
$isActive = function (string $segment) use ($uri, $base): string {
    $path = $base . '/' . ltrim($segment, '/');
    return str_starts_with($uri, $path) ? 'active' : '';
};

$isDashboard = ($uri === $base || $uri === $base . '/') ? 'active' : '';

$adminPerms = ['users', 'permissions', 'roles'];
$hasAdminAccess = array_reduce($adminPerms, fn($carry, $p) => $carry || Auth::hasPermission($p), false);
$adminMenuOpen = array_reduce($adminPerms, fn($carry, $p) => $carry || $isActive($p) !== '', false);

?>

<aside class="main-sidebar sidebar-light-primary elevation-2">
    <!-- Brand Logo -->
    <a href="<?= URL ?>" class="brand-link">
        <img src="<?= URL ?>/img/e-commerce_logo.png" loading="eager" alt="Logo"
            class="brand-image img-circle elevation-0" style="opacity: .8">
        <span class="brand-text font-weight-light">Base System</span>
    </a>

    <div class="sidebar">
        <nav class="mt-4">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= URL ?>" class="nav-link <?= $isDashboard ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Administration -->
                <?php if ($hasAdminAccess) : ?>
                    <li class="nav-item <?= $adminMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= $adminMenuOpen ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>
                                Administration
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (Auth::hasPermission('users')) : ?>
                                <li class="nav-item">
                                    <a href="<?= URL ?>users" class="nav-link <?= $isActive('users') ?>">
                                        <i class="fas fa-user-alt nav-icon"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                            <?php endif ?>
                            <?php if (Auth::hasPermission('permissions')) : ?>
                                <li class="nav-item">
                                    <a href="<?= URL ?>permissions" class="nav-link <?= $isActive('permissions') ?>">
                                        <i class="fas fa-key nav-icon"></i>
                                        <p>Permissions</p>
                                    </a>
                                </li>
                            <?php endif ?>
                            <?php if (Auth::hasPermission('roles')) : ?>
                                <li class="nav-item">
                                    <a href="<?= URL ?>roles" class="nav-link <?= $isActive('roles') ?>">
                                        <i class="fas fa-user-tag nav-icon"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </li>
                <?php endif ?>

                <!-- Audit Log -->
                <?php if (Auth::hasPermission('audit_log')) : ?>
                    <li class="nav-item">
                        <a href="<?= URL ?>audit-log" class="nav-link <?= $isActive('audit-log') ?>">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Audit Log</p>
                        </a>
                    </li>
                <?php endif ?>

                <!-- Space to add new modules -->

            </ul>
        </nav>
    </div>
</aside>
