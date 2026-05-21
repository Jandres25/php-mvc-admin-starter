<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>User Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= URL ?>"><i class="fas fa-home"></i> Home</a></li>
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
                                <a href="<?= URL ?>users/create" class="btn btn-primary btn-sm">
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
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                foreach ($users as $user):
                                    $isActive = ((int) $user['status']) === 1;
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $counter++; ?></td>
                                        <td><?= htmlspecialchars($user['name'] . ' ' . $user['first_surname']); ?></td>
                                        <td><?= htmlspecialchars($user['document_type']); ?></td>
                                        <td><?= htmlspecialchars($user['document_number']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td class="text-center">
                                            <img src="<?= URL ?>uploads/users/<?= htmlspecialchars(!empty($user['image']) ? $user['image'] : 'user_default.jpg'); ?>" loading="lazy" alt="Profile picture of <?= htmlspecialchars($user['name']); ?>" class="img-thumbnail" width="30">
                                        </td>
                                        <td><?= htmlspecialchars($user['role_name'] ?? 'N/A'); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $isActive ? 'badge-success' : 'badge-danger'; ?> badge-pill p-2"><?= $isActive ? 'Active' : 'Inactive'; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?= URL ?>users/<?= $user['id'] ?>" class="btn btn-info btn-sm" aria-label="View user <?= htmlspecialchars($user['name']); ?>" data-toggle="tooltip" title="View user">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </a>
                                                <a href="<?= URL ?>users/<?= $user['id'] ?>/edit" class="btn btn-warning btn-sm" aria-label="Edit user <?= htmlspecialchars($user['name']); ?>" data-toggle="tooltip" title="Edit user">
                                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                                </a>
                                                <?php if (((int) $user['id']) !== $currentUserId): ?>
                                                    <button type="button" class="btn <?= $isActive ? 'btn-danger' : 'btn-success'; ?> btn-sm btn-toggle-status"
                                                        aria-label="<?= $isActive ? 'Yes, deactivate' : 'Yes, activate'; ?> user <?= htmlspecialchars($user['name']); ?>"
                                                        data-id="<?= $user['id']; ?>"
                                                        data-status="<?= $user['status']; ?>"
                                                        data-name="<?= htmlspecialchars($user['name']); ?>"
                                                        data-toggle="tooltip" title="<?= $isActive ? 'Deactivate User?' : 'Activate User?'; ?>">
                                                        <i class="fas <?= $isActive ? 'fa-user-slash' : 'fa-user-check'; ?>" aria-hidden="true"></i>
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

