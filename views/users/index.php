<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

requirePermission('users');

$plugins = ['datatables', 'datatables-export'];

include_once '../layouts/header.php';

$controller = new \Controllers\Users\UserController();
$users      = $controller->index();

$module_scripts = ['users/index-users'];
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>User Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="card-title">User List</h3>
                            <div class="card-tools">
                                <a href="<?= $URL; ?>views/users/create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i> New User
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Collapse">
                                    <i class="fas fa-minus" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tableUsers" class="table table-sm table-bordered table-hover table-striped" style="visibility: hidden;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Document Type</th>
                                    <th>Document Number</th>
                                    <th>Email</th>
                                    <th>Image</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                foreach ($users as $user) :
                                    $currentStatus      = $user['status'];
                                    $statusBtnClass     = $currentStatus == 1 ? 'btn-danger' : 'btn-success';
                                    $statusIconClass    = $currentStatus == 1 ? 'fa-user-slash' : 'fa-user-check';
                                    $alertTitle         = $currentStatus == 1 ? 'Deactivate User?' : 'Activate User?';
                                    $alertText          = $currentStatus == 1 ? 'The user will not be able to access the system.' : 'The user will be able to access the system again.';
                                    $confirmButtonText  = $currentStatus == 1 ? 'Yes, deactivate' : 'Yes, activate';
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $counter++; ?></td>
                                        <td><?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']); ?></td>
                                        <td><?= htmlspecialchars($user['document_type']); ?></td>
                                        <td><?= htmlspecialchars($user['document_number']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td class="text-center">
                                            <?php if (isset($user['image'])): ?>
                                                <img src="<?= $URL; ?>public/uploads/users/<?= $user['image']; ?>" loading="lazy" alt="Profile picture of <?= htmlspecialchars($user['name']); ?>" class="img-thumbnail" width="30">
                                            <?php else : ?>
                                                <img src="<?= $URL; ?>public/uploads/users/user_default.jpg" loading="lazy" alt="Default profile picture" class="img-thumbnail" width="30">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= (!empty($user['position'])) ? htmlspecialchars($user['position']) : 'N/A'; ?></td>
                                        <td class="text-center">
                                            <?php if ($currentStatus == 1) : ?>
                                                <span class="badge badge-success badge-pill p-2">Active</span>
                                            <?php else : ?>
                                                <span class="badge badge-danger badge-pill p-2">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?= $URL; ?>views/users/show.php?id=<?= $user['id']; ?>" class="btn btn-info btn-sm" aria-label="View user <?= htmlspecialchars($user['name']); ?>">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </a>
                                                <a href="<?= $URL; ?>views/users/update.php?id=<?= $user['id']; ?>" class="btn btn-warning btn-sm" aria-label="Edit user <?= htmlspecialchars($user['name']); ?>">
                                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button type="button" class="btn <?= $statusBtnClass; ?> btn-sm btn-toggle-status"
                                                        aria-label="<?= $confirmButtonText; ?> user <?= htmlspecialchars($user['name']); ?>"
                                                        data-id="<?= $user['id']; ?>"
                                                        data-status="<?= $currentStatus; ?>"
                                                        data-name="<?= htmlspecialchars($user['name']); ?>">
                                                        <i class="fas <?= $statusIconClass; ?>" aria-hidden="true"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php
include_once '../layouts/messages.php';
include_once '../layouts/footer.php';
?>
