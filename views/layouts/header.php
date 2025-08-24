<?php
// Incluir el archivo de sesión
require_once __DIR__ . '/session.php';

// Verificar si el usuario está autenticado
requireLogin();

// Obtener datos del usuario actual
$currentUser = getCurrentUser();
$idusuariosesion = $currentUser['id'];

// Incluir autoload
require_once __DIR__ . '/../../config/config.php';

// Incluir el servicio de autorización
$authService = new \Services\AuthorizationService();

// Usar la variable global URL
global $URL;

?>

<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema Base MVC</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/lib/bootstrap/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/lib/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= $URL; ?>public/css/core/webfonts.css">
    <link rel="icon" type="image/png" href="<?= $URL; ?>public/img/e-commerce_logo.png">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/lib/adminlte/adminlte.min.css">
    <!-- Datatables -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/datatables/datatables.min.css">
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/datatables/buttons.bootstrap4.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/select2/select2-bootstrap4.min.css">
    <!-- Sweetalert2 -->
    <link rel="stylesheet" href="<?= $URL; ?>public/css/plugins/sweetalert2/sweetalert2.min.css">
    <script src="<?= $URL; ?>public/js/plugins/sweetalert2/sweetalert2.min.js"></script>
    <!-- jQuery -->
    <script src="<?= $URL; ?>public/js/lib/jquery/jquery.min.js"></script>

    <!-- Estilos específicos por módulo -->
    <?php if (isset($module_styles) && is_array($module_styles)): ?>
        <?php foreach ($module_styles as $style): ?>
            <link rel="stylesheet" href="<?= $URL; ?>public/css/modules/<?= $style; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        const baseUrl = "<?= $URL; ?>";
    </script>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= $URL; ?>" class="nav-link">Sistema Base</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= $URL; ?>public/uploads/usuarios/<?= $currentUser['imagen']; ?>" loading="eager" class="img-circle elevation-2" alt="User Image">
                            <p>
                                <?= $currentUser['nombre']; ?>
                                <small><?= $currentUser['cargo']; ?></small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <?php if ($authService->tienePermisoNombre($idusuariosesion, 'perfil')) : ?>
                                <a href="<?= $URL; ?>views/usuarios/perfil.php?id=<?= $currentUser['id']; ?>" class="btn btn-default btn-flat">Perfil</a>
                            <?php endif; ?>
                            <a href="<?= $URL; ?>controllers/auth/logout.php" class="btn btn-default btn-flat float-right">Cerrar Sesión</a>
                        </li>
                    </ul>

                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-2">
            <!-- Brand Logo -->
            <a href="<?= $URL; ?>" class="brand-link">
                <img src="<?= $URL; ?>public/img/AdminLTELogo.png" loading="eager" alt="Logo" class="brand-image img-circle elevation-0" style="opacity: .8">
                <span class="brand-text font-weight-light">Sistema Base</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-4">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="<?= $URL; ?>" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Administración -->
                        <?php if ($authService->tienePermisoNombre($idusuariosesion, 'usuarios') || $authService->tienePermisoNombre($idusuariosesion, 'permisos')) : ?>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-user-shield"></i>
                                    <p>
                                        Administración
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php if ($authService->tienePermisoNombre($idusuariosesion, 'usuarios')) : ?>
                                        <li class="nav-item">
                                            <a href="<?= $URL; ?>views/usuarios" class="nav-link">
                                                <i class="fas fa-user-alt nav-icon"></i>
                                                <p>Usuarios</p>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($authService->tienePermisoNombre($idusuariosesion, 'permisos')) : ?>
                                        <li class="nav-item">
                                            <a href="<?= $URL; ?>views/permisos" class="nav-link">
                                                <i class="fas fa-key nav-icon"></i>
                                                <p>Permisos</p>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Espacio para agregar nuevos módulos -->

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">