<?php
require_once 'views/layouts/header.php';
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row ">
            <div class="col-sm-6">
                <h1 class="m-0">Página Inicial</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
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
                        <h1 class="display-5 fw-bold">Bienvenido al Sistema Base - <?= $_SESSION['usuario_cargo']; ?></h1>
                        <p class="col-md-8 fs-4">
                            Este sistema base te permite crear rápidamente aplicaciones con un sistema de autenticación
                            completo, gestión de permisos y una estructura MVC organizada.
                        </p>
                        <div class="mt-4 d-flex gap-2 flex-wrap">
                            <?php if ($authService->tienePermisoNombre($idusuariosesion, 'usuarios')) : ?>
                                <a href="<?= $URL; ?>views/usuarios" class="btn btn-primary btn-lg">
                                    <i class="fas fa-users"></i> Gestionar Usuarios
                                </a>
                            <?php endif; ?>
                            <?php if ($authService->tienePermisoNombre($idusuariosesion, 'permisos')) : ?>
                                <a href="<?= $URL; ?>views/permisos" class="btn btn-warning btn-lg">
                                    <i class="fas fa-key"></i> Gestionar Permisos
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
                        <h3 class="card-title"><i class="fas fa-list"></i> Estructura del Proyecto</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> config/ - Configuraciones
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> controllers/ - Controladores
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> models/ - Modelos
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> services/ - Servicios
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder mr-2"></i> views/ - Vistas
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cogs"></i> Características</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-lock mr-2"></i> Sistema de autenticación
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-key mr-2"></i> Gestión de permisos
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-layer-group mr-2"></i> Arquitectura MVC
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-shield-alt mr-2"></i> Protección CSRF
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-image mr-2"></i> Manejo de imágenes
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
include_once 'views/layouts/mensajes.php';
require_once 'views/layouts/footer.php';
?>