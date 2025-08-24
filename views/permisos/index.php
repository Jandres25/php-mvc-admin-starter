<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

$idusuario = $_SESSION['usuario_id'];
$auth = new \Services\AuthorizationService();

// Verificar si el usuario tiene acceso al módulo ANTES de incluir el header
if (!($auth->tienePermisoNombre($idusuario, 'permisos')) && !($auth->esAdministrador($idusuario))) {
    $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta sección.';
    $_SESSION['icono'] = 'error';

    // Redirigir al inicio
    header('Location: ' . $URL);
    exit;
}

// Incluir el encabezado después de verificar permisos
include_once '../layouts/header.php';

$controller = new \Controllers\Permisos\PermisoController();
$permisos = $controller->index();
$estadisticas = $controller->getEstadisticas();

$module_scripts = ['permisos/index-permisos'];
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Gestión de Permisos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Permisos</li>
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
                        <span class="info-box-text">Total de Permisos</span>
                        <span class="info-box-number"><?= $estadisticas['total']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Permisos Activos</span>
                        <span class="info-box-number"><?= $estadisticas['activos']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Permisos Inactivos</span>
                        <span class="info-box-number"><?= $estadisticas['inactivos']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                            <h3 class="card-title mb-2 mb-sm-0">Permisos del Sistema</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm me-2" id="btnNuevoPermiso">
                                    <i class="fas fa-plus"></i> Nuevo Permiso
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="display: block;">
                        <div class="table-responsive">
                            <table id="tablaPermisos" class="table table-bordered table-hover table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 10%">ID</th>
                                        <th class="text-center" style="width: 50%">Nombre</th>
                                        <th class="text-center" style="width: 15%">Usuarios</th>
                                        <th class="text-center" style="width: 10%">Estado</th>
                                        <th class="text-center" style="width: 15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($permisos as $permiso) :
                                        $estado_actual = $permiso['estado'];
                                        $clase_estado = $estado_actual == 1 ? 'badge-success' : 'badge-danger';
                                        $texto_estado = $estado_actual == 1 ? 'Activo' : 'Inactivo';
                                        $total_usuarios = $permiso['total_usuarios'] ?? 0;
                                        $clase_usuarios = $total_usuarios > 0 ? 'badge-primary' : 'badge-secondary';
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $permiso['idpermiso']; ?></td>
                                            <td><?= htmlspecialchars($permiso['nombre']); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $clase_usuarios; ?>">
                                                    <?= $total_usuarios; ?> usuario<?= $total_usuarios != 1 ? 's' : ''; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $clase_estado; ?>"><?= $texto_estado; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="<?= $URL; ?>views/permisos/detalle.php?id=<?= $permiso['idpermiso']; ?>" class="btn btn-info btn-sm" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-warning btn-sm btn-editar"
                                                        data-id="<?= $permiso['idpermiso']; ?>"
                                                        data-nombre="<?= htmlspecialchars($permiso['nombre']); ?>"
                                                        title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn <?= $estado_actual == 1 ? 'btn-danger' : 'btn-success'; ?> btn-sm cambiar-estado"
                                                        data-id="<?= $permiso['idpermiso']; ?>"
                                                        data-estado-actual="<?= $estado_actual; ?>"
                                                        data-usuarios="<?= $total_usuarios; ?>"
                                                        title="<?= $estado_actual == 1 ? 'Desactivar' : 'Activar'; ?>">
                                                        <i class="fas <?= $estado_actual == 1 ? 'fa-times' : 'fa-check'; ?>"></i>
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

<!-- Modal para Permiso -->
<div class="modal fade" id="modalPermiso" tabindex="-1" role="dialog" aria-labelledby="modalPermisoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" id="modalPermisoHeader">
                <h5 class="modal-title" id="modalPermisoLabel">Gestión de Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPermiso" method="post">
                <div class="modal-body">
                    <input type="hidden" id="permisoAction" name="action" value="create">
                    <input type="hidden" id="idPermiso" name="idpermiso" value="">

                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <small class="form-text text-muted">Nombre único para el permiso</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPermiso">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once '../layouts/mensajes.php';
include_once '../layouts/footer.php';
?>

<script>
    const mensajeErrorDesactivar = "No se puede desactivar el permiso porque hay usuarios que lo tienen asignado";
</script>