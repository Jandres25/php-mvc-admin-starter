<?php
require_once 'views/layouts/header.php';
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row ">
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
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="p-5 mb-4 bg-white rounded-3 shadow-sm">
                    <div class="container-fluid py-5">
                        <h1 class="display-5 fw-bold">Welcome to the Base System - <?= $_SESSION['user_position']; ?></h1>
                        <p class="col-md-8 fs-4">
                            This base system lets you quickly build applications with a complete authentication system,
                            permission management, and an organized MVC structure.
                        </p>
                        <div class="mt-4 d-flex gap-2 flex-wrap">
                            <?php if ($authService->hasPermissionByName($_SESSION['user_id'], 'users')) : ?>
                                <a href="<?= $URL; ?>views/users" class="btn btn-primary btn-lg">
                                    <i class="fas fa-users"></i> Manage Users
                                </a>
                            <?php endif; ?>
                            <?php if ($authService->hasPermissionByName($_SESSION['user_id'], 'permissions')) : ?>
                                <a href="<?= $URL; ?>views/permissions" class="btn btn-warning btn-lg">
                                    <i class="fas fa-key"></i> Manage Permissions
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Project Structure</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> config/ - Configuration
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> controllers/ - Controllers
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> models/ - Models
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> services/ - Services
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> views/ - Views
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cogs"></i> Features</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-lock mr-2"></i> Authentication system
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-key mr-2"></i> Permission management
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-layer-group mr-2"></i> MVC architecture
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-shield-alt mr-2"></i> CSRF protection
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-image mr-2"></i> Image handling
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php
include_once 'views/layouts/messages.php';
require_once 'views/layouts/footer.php';
?>
