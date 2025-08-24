<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

$idusuario = $_SESSION['usuario_id'];
$authService = new \Services\AuthorizationService();

// Verificar si el usuario tiene acceso al módulo
if (!($authService->tienePermisoNombre($idusuario, 'usuarios')) && !($authService->esAdministrador($idusuario))) {
    $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta sección.';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL);
    exit;
}

// Incluir el encabezado
include_once '../layouts/header.php';

// Verificar si se proporcionó un ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['mensaje'] = 'ID de usuario no válido';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/usuarios');
    exit;
}

// Instanciar el controlador y obtener los datos del usuario
$controller = new \Controllers\Usuarios\UsuarioController();
$usuario = $controller->editar($id);
// Verificar si el usuario existe
if (!$usuario) {
    $_SESSION['mensaje'] = 'Usuario no encontrado';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/usuarios');
    exit;
}

// Obtener los permisos del usuario
$permisos_usuario = $authService->obtenerPermisosUsuario($usuario['idusuario']);
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Detalle de Usuario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>views/usuarios"><i class="fas fa-users"></i> Usuarios</a></li>
                    <li class="breadcrumb-item active">Detalle de Usuario</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna izquierda - Perfil y acciones -->
            <div class="col-md-4">
                <!-- Tarjeta de perfil -->
                <div class="card card-info card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <?php if (isset($usuario['imagen']) && !empty($usuario['imagen'])): ?>
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?= $URL; ?>public/uploads/usuarios/<?= $usuario['imagen']; ?>"
                                    alt="Imagen de perfil">
                            <?php else: ?>
                                <img class="profile-user-img img-fluid img-circle"
                                    src="<?= $URL; ?>public/uploads/usuarios/user_default.jpg"
                                    alt="Imagen de perfil">
                            <?php endif; ?>
                        </div>

                        <h3 class="profile-username text-center">
                            <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidopaterno'] . ' ' . $usuario['apellidomaterno']); ?>
                        </h3>

                        <p class="text-muted text-center"><?= htmlspecialchars($usuario['cargo'] ?? 'Sin cargo asignado'); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-id-card mr-1"></i> <?= htmlspecialchars($usuario['tipodocumento']); ?></b>
                                <a class="float-right"><?= htmlspecialchars($usuario['numdocumento']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope mr-1"></i> Correo</b>
                                <a href="mailto:<?= htmlspecialchars($usuario['correo']); ?>" class="float-right"><?= htmlspecialchars($usuario['correo']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-phone mr-1"></i> Teléfono</b>
                                <a href="tel:<?= htmlspecialchars($usuario['telefono'] ?? ''); ?>" class="float-right">
                                    <?= !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : 'No registrado'; ?>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1"></i> Estado</b>
                                <span class="float-right">
                                    <?php if ($usuario['estado'] == 1): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $URL; ?>views/usuarios/update.php?id=<?= $usuario['idusuario']; ?>" class="btn btn-warning mb-3">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="<?= $URL; ?>views/usuarios/index.php" class="btn btn-secondary mb-3">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de actividad del usuario -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Información del Sistema</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <strong><i class="far fa-calendar-plus mr-1"></i> Fecha de Creación:</strong>
                        <p class="text-muted">
                            <?= isset($usuario['fechacreacion']) ? date('d/m/Y H:i', strtotime($usuario['fechacreacion'])) : 'No disponible'; ?>
                        </p>

                        <hr>

                        <strong><i class="fas fa-sync-alt mr-1"></i> Última Actualización:</strong>
                        <p class="text-muted">
                            <?= isset($usuario['fechaactualizacion']) ? date('d/m/Y H:i', strtotime($usuario['fechaactualizacion'])) : 'No disponible'; ?>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Fin columna izquierda -->

            <!-- Columna derecha - Información detallada -->
            <div class="col-md-8">
                <!-- Card tabs -->
                <div class="card card-info card-outline card-tabs">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs" id="user-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-info-personal-tab" data-toggle="pill" href="#tab-info-personal" role="tab" aria-controls="tab-info-personal" aria-selected="true">
                                    <i class="fas fa-user mr-1"></i> Información Personal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-direccion-tab" data-toggle="pill" href="#tab-direccion" role="tab" aria-controls="tab-direccion" aria-selected="false">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Dirección
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-permisos-tab" data-toggle="pill" href="#tab-permisos" role="tab" aria-controls="tab-permisos" aria-selected="false">
                                    <i class="fas fa-key mr-1"></i> Permisos
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="user-tab-content">
                            <!-- Tab Información Personal -->
                            <div class="tab-pane fade show active" id="tab-info-personal" role="tabpanel" aria-labelledby="tab-info-personal-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nombre:</label>
                                            <p class="form-control"><?= htmlspecialchars($usuario['nombre']); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Apellido Paterno:</label>
                                            <p class="form-control"><?= htmlspecialchars($usuario['apellidopaterno']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Apellido Materno:</label>
                                            <p class="form-control"><?= !empty($usuario['apellidomaterno']) ? htmlspecialchars($usuario['apellidomaterno']) : 'No registrado'; ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cargo:</label>
                                            <p class="form-control"><?= !empty($usuario['cargo']) ? htmlspecialchars($usuario['cargo']) : 'No asignado'; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo de Documento:</label>
                                            <p class="form-control"><?= htmlspecialchars($usuario['tipodocumento']); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Número de Documento:</label>
                                            <p class="form-control"><?= htmlspecialchars($usuario['numdocumento']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Correo Electrónico:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <p class="form-control"><?= htmlspecialchars($usuario['correo']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Teléfono:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <p class="form-control"><?= !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : 'No registrado'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tab Información Personal -->

                            <!-- Tab Dirección -->
                            <div class="tab-pane fade" id="tab-direccion" role="tabpanel" aria-labelledby="tab-direccion-tab">
                                <?php if (!empty($usuario['direccion'])): ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label><i class="fas fa-map-marker-alt mr-1"></i> Dirección Completa:</label>
                                                <p class="form-control" style="min-height: 100px;"><?= htmlspecialchars($usuario['direccion']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($usuario['direccion']); ?>" target="_blank" class="btn btn-info">
                                                <i class="fas fa-map-marked-alt mr-1"></i> Ver en Google Maps
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info"></i> Sin información de dirección</h5>
                                        <p>El usuario no tiene registrada información de dirección.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Fin Tab Dirección -->

                            <!-- Tab Permisos -->
                            <div class="tab-pane fade" id="tab-permisos" role="tabpanel" aria-labelledby="tab-permisos-tab">
                                <?php if ($authService->esAdministrador($usuario['idusuario'])): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="icon fas fa-check"></i> Usuario Administrador</h5>
                                        <p>Este usuario tiene el cargo de Administrador, por lo que tiene acceso a todos los permisos del sistema.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <?php if (count($permisos_usuario) > 0): ?>
                                        <?php foreach ($permisos_usuario as $permiso): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="info-box bg-light">
                                                    <span class="info-box-icon bg-info"><i class="fas fa-check-circle"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text"><?= htmlspecialchars($permiso['nombre']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <h5><i class="icon fas fa-exclamation-triangle"></i> Sin permisos específicos</h5>
                                                <p>Este usuario no tiene permisos específicos asignados.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Fin Tab Permisos -->
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- Fin columna derecha -->
        </div>
    </div>
</section>

<style>
    /* Color info para el texto de las pestañas no activas */
    #user-tabs .nav-link:not(.active) {
        color: #17a2b8;
        /* Color info */
    }

    /* Opcional: Color info más intenso al pasar el mouse por pestañas no activas */
    #user-tabs .nav-link:not(.active):hover {
        color: #138496;
        /* Un tono más oscuro de info */
    }
</style>

<script>
    // Guardar la última pestaña activa en localStorage
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        localStorage.setItem('lastUserDetailTab', $(e.target).attr('href'));
    });

    // Restaurar la última pestaña activa
    var lastTab = localStorage.getItem('lastUserDetailTab');
    if (lastTab) {
        $('a[href="' + lastTab + '"]').tab('show');
    }

    // Mostrar imagen más grande al hacer clic
    $('.profile-user-img').on('click', function() {
        const imgSrc = $(this).attr('src');

        Swal.fire({
            imageUrl: imgSrc,
            imageAlt: 'Imagen de perfil',
            confirmButtonText: 'Cerrar',
            customClass: {
                image: 'img-fluid'
            }
        });
    });
</script>

<?php
include_once '../layouts/mensajes.php';
include_once '../layouts/footer.php';
?>