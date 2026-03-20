<?php
require_once 'views/layouts/header.php';

$userModel       = new \Models\User();
$permissionModel = new \Models\Permission();

$userStats  = $userModel->getStatistics();
$permStats  = $permissionModel->getStatistics();
$recentUsers = $userModel->getRecent(5);

$canManageUsers       = $authService->hasPermissionByName($_SESSION['user_id'], 'users');
$canManagePermissions = $authService->hasPermissionByName($_SESSION['user_id'], 'permissions');
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Stats widgets -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $userStats['total']; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <?php if ($canManageUsers): ?>
                        <a href="<?= $URL; ?>views/users" class="small-box-footer">
                            Manage <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer">&nbsp;</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $userStats['active']; ?></h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                    <span class="small-box-footer">
                        <?= $userStats['inactive']; ?> inactive
                    </span>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $permStats['total']; ?></h3>
                        <p>Total Permissions</p>
                    </div>
                    <div class="icon"><i class="fas fa-key"></i></div>
                    <?php if ($canManagePermissions): ?>
                        <a href="<?= $URL; ?>views/permissions" class="small-box-footer">
                            Manage <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="small-box-footer">&nbsp;</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $permStats['active']; ?></h3>
                        <p>Active Permissions</p>
                    </div>
                    <div class="icon"><i class="fas fa-shield-alt"></i></div>
                    <span class="small-box-footer">
                        <?= $permStats['inactive']; ?> inactive
                    </span>
                </div>
            </div>
        </div>
        <!-- /.row stats -->

        <!-- Recent Users -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i> Recently Registered Users
                        </h3>
                        <?php if ($canManageUsers): ?>
                            <div class="card-tools">
                                <a href="<?= $URL; ?>views/users" class="btn btn-sm btn-primary">
                                    <i class="fas fa-users mr-1"></i> View All
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentUsers)): ?>
                            <p class="text-muted text-center py-4">No users registered yet.</p>
                        <?php else: ?>
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']); ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php if ((int)$user['status'] === 1): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $user['created_at']
                                                    ? date('m/d/Y', strtotime($user['created_at']))
                                                    : '—'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row recent users -->

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php
include_once 'views/layouts/messages.php';
require_once 'views/layouts/footer.php';
?>
