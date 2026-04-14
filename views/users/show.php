<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../app/config/config.php';

requirePermission('users');

$module_styles  = ['users/show-user'];
$module_scripts = ['users/show-user'];

$pageController = new \App\Controllers\Users\UserPageController();
$viewData       = $pageController->buildShowViewDataFromRequest();
$user           = $viewData['user'];
$userPermissions = $viewData['user_permissions'];
$isAdminUser    = $viewData['is_admin_user'];

include_once '../layouts/header.php';
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>User Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>views/users"><i class="fas fa-users"></i> Users</a></li>
                    <li class="breadcrumb-item active">User Detail</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Left column - Profile and actions -->
            <div class="col-md-4">
                <!-- Profile card -->
                <div class="card card-info card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <?php if (isset($user['image']) && !empty($user['image'])): ?>
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?= $URL; ?>public/uploads/users/<?= $user['image']; ?>"
                                    alt="Profile image">
                            <?php else: ?>
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?= $URL; ?>public/uploads/users/user_default.jpg"
                                    alt="Profile image">
                            <?php endif; ?>
                        </div>

                        <h3 class="profile-username text-center">
                            <?= htmlspecialchars($user['name'] . ' ' . $user['first_surname'] . ' ' . $user['second_surname']); ?>
                        </h3>

                        <p class="text-muted text-center"><?= htmlspecialchars($user['position'] ?? 'No position assigned'); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-id-card mr-1"></i> <?= htmlspecialchars($user['document_type']); ?></b>
                                <a class="float-right"><?= htmlspecialchars($user['document_number']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope mr-1"></i> Email</b>
                                <a href="mailto:<?= htmlspecialchars($user['email']); ?>" class="float-right"><?= htmlspecialchars($user['email']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-phone mr-1"></i> Phone</b>
                                <a href="https://wa.me/<?= htmlspecialchars($user['phone'] ?? ''); ?>" class="float-right" target="_blank">
                                    <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not registered'; ?>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1"></i> Status</b>
                                <span class="float-right">
                                    <?php if ($user['status'] == 1): ?>
                                        <span class="badge badge-success badge-pill p-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill p-2">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $URL; ?>views/users/update.php?id=<?= $user['id']; ?>" class="btn btn-warning mb-3">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="<?= $URL; ?>views/users/index.php" class="btn btn-default mb-3">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Left column -->

            <!-- Right column - Detailed information -->
            <div class="col-md-8">
                <div class="card card-info card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="user-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-personal-tab" data-toggle="pill" href="#tab-personal" role="tab" aria-controls="tab-personal" aria-selected="true">
                                    <i class="fas fa-user mr-1"></i> Personal Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-address-tab" data-toggle="pill" href="#tab-address" role="tab" aria-controls="tab-address" aria-selected="false">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Address
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-permissions-tab" data-toggle="pill" href="#tab-permissions" role="tab" aria-controls="tab-permissions" aria-selected="false">
                                    <i class="fas fa-key mr-1"></i> Permissions
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="user-tab-content">
                            <!-- Personal Information Tab -->
                            <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="tab-personal-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['name']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Surname:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user-alt"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['first_surname']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Second Surname:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user-alt"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($user['second_surname']) ? htmlspecialchars($user['second_surname']) : 'Not registered'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Position:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($user['position']) ? htmlspecialchars($user['position']) : 'Not assigned'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Document Type:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['document_type']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Document Number:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['document_number']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email Address:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($user['email']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phone:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not registered'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Personal Information Tab -->

                            <!-- Address Tab -->
                            <div class="tab-pane fade" id="tab-address" role="tabpanel" aria-labelledby="tab-address-tab">
                                <?php if (!empty($user['address'])): ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label><i class="fas fa-map-marker-alt mr-1"></i> Full Address:</label>
                                                <p class="form-control" style="min-height: 100px;"><?= htmlspecialchars($user['address']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($user['address']); ?>" target="_blank" class="btn btn-info">
                                                <i class="fas fa-map-marked-alt mr-1"></i> View on Google Maps
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info"></i> No address information</h5>
                                        <p>This user has no address information registered.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- /Address Tab -->

                            <!-- Permissions Tab -->
                            <div class="tab-pane fade" id="tab-permissions" role="tabpanel" aria-labelledby="tab-permissions-tab">
                                <?php if ($isAdminUser): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="icon fas fa-check"></i> Administrator User</h5>
                                        <p>This user has the Administrator position and therefore has access to all system permissions.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <?php if (count($userPermissions) > 0): ?>
                                        <?php foreach ($userPermissions as $perm): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="info-box bg-light">
                                                    <span class="info-box-icon bg-info"><i class="fas fa-check-circle"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text"><?= htmlspecialchars($perm['name']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <h5><i class="icon fas fa-exclamation-triangle"></i> No specific permissions</h5>
                                                <p>This user has no specific permissions assigned.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- /Permissions Tab -->
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /Right column -->
        </div>
    </div>
</section>

<?php
include_once '../layouts/messages.php';
include_once '../layouts/footer.php';
?>
