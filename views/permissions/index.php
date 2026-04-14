<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

requirePermission('permissions');

$plugins = ['datatables', 'datatables-export'];
$module_scripts = ['permissions/modal-permission', 'permissions/index-permissions'];

$pageController = new \App\Controllers\Permissions\PermissionPageController();
$viewData       = $pageController->buildIndexViewData();
$permissions    = $viewData['permissions'];
$statistics     = $viewData['statistics'];

include_once '../layouts/header.php';
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Permission Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item active">Permissions</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-key"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Permissions</span>
                        <span class="info-box-number"><?= $statistics['total']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active</span>
                        <span class="info-box-number"><?= $statistics['active']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Inactive</span>
                        <span class="info-box-number"><?= $statistics['inactive']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                            <h3 class="card-title mb-2 mb-sm-0">System Permissions</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm me-2" id="btnNewPermission">
                                    <i class="fas fa-plus"></i> New Permission
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="display: block;">
                        <div class="table-responsive">
                            <table id="tablePermissions" class="table table-bordered table-hover table-striped table-sm" style="visibility: hidden;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 10%">ID</th>
                                        <th class="text-center" style="width: 20%">Name</th>
                                        <th class="text-center" style="width: 40%">Description</th>
                                        <th class="text-center" style="width: 15%">Users</th>
                                        <th class="text-center" style="width: 10%">Status</th>
                                        <th class="text-center" style="width: 15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($permissions as $permission) :
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $permission['id']; ?></td>
                                            <td><?= htmlspecialchars($permission['name']); ?></td>
                                            <td><?= htmlspecialchars($permission['description']); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= htmlspecialchars($permission['users_badge_class']); ?> badge-pill p-2">
                                                    <?= $permission['total_users']; ?> <?= $permission['users_label']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= htmlspecialchars($permission['status_badge_class']); ?> badge-pill p-2"><?= htmlspecialchars($permission['status_label']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="<?= $URL; ?>views/permissions/detail.php?id=<?= $permission['id']; ?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="View details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $permission['id']; ?>"
                                                        data-name="<?= htmlspecialchars($permission['name']); ?>"
                                                        data-description="<?= htmlspecialchars($permission['description_raw']); ?>"
                                                        data-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn <?= htmlspecialchars($permission['status_btn_class']); ?> btn-sm btn-toggle-status"
                                                        data-id="<?= $permission['id']; ?>"
                                                        data-current-status="<?= $permission['status']; ?>"
                                                        data-users="<?= $permission['total_users']; ?>"
                                                        data-toggle="tooltip" title="<?= htmlspecialchars($permission['status_btn_title']); ?>">
                                                        <i class="fas <?= htmlspecialchars($permission['status_icon_class']); ?>"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '_modal_permission.php'; ?>

<?php
include_once '../layouts/messages.php';
include_once '../layouts/footer.php';
?>
