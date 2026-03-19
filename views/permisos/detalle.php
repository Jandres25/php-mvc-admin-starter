<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

requirePermiso('permisos');

// Verificar si se proporcionó un ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['mensaje'] = 'ID de permiso no válido';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/permisos');
    exit;
}

// Obtener datos del permiso antes de incluir el header (evita headers after output)
$controller = new \Controllers\Permisos\PermisoController();
$permiso = $controller->getById($id);

if (!$permiso) {
    $_SESSION['mensaje'] = 'Permiso no encontrado';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/permisos');
    exit;
}

$plugins = ['datatables', 'select2'];

// Incluir el encabezado
include_once '../layouts/header.php';

$module_scripts = ['permisos/modal-permiso', 'permisos/detalle-permiso'];

// Obtener usuarios con este permiso
$usuarios = $permiso['usuarios'];

// Obtener usuarios que NO tienen este permiso (para el modal de asignación)
$usuariosSinPermiso = $controller->getUsuariosSinPermiso($id);
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Detalle de Permiso</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>views/permisos"><i class="fas fa-key"></i> Permisos</a></li>
                    <li class="breadcrumb-item active">Detalle de Permiso</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna izquierda - Info del permiso -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <span class="fa-stack fa-2x">
                                <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                <i class="fas fa-key fa-stack-1x text-white"></i>
                            </span>
                            <h4 class="mt-2 mb-0"><?= htmlspecialchars($permiso['nombre']); ?></h4>
                            <p class="text-muted">#<?= $permiso['idpermiso']; ?></p>
                        </div>

                        <?php if (!empty($permiso['descripcion'])): ?>
                            <p class="text-muted text-center mb-3"><?= htmlspecialchars($permiso['descripcion']); ?></p>
                        <?php endif; ?>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1"></i> Estado</b>
                                <span class="float-right">
                                    <?php if ($permiso['estado'] == 1): ?>
                                        <span class="badge badge-success badge-pill p-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill p-2">Inactivo</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-users mr-1"></i> Usuarios asignados</b>
                                <span class="float-right">
                                    <span class="badge badge-info badge-pill p-2" id="contadorUsuarios"><?= count($usuarios); ?></span>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $URL; ?>views/permisos" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="button" class="btn btn-warning btn-editar"
                                data-id="<?= $permiso['idpermiso']; ?>"
                                data-nombre="<?= htmlspecialchars($permiso['nombre']); ?>"
                                data-descripcion="<?= htmlspecialchars($permiso['descripcion'] ?? ''); ?>">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha - Usuarios con este permiso -->
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Usuarios con este Permiso</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm mr-2" id="btnAsignarUsuario">
                                <i class="fas fa-user-plus"></i> Asignar Usuario
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="detallePermisos" class="table table-bordered table-hover table-striped table-sm" style="visibility: hidden;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Cargo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= $usuario['idusuario']; ?></td>
                                        <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidopaterno'] . ' ' . $usuario['apellidomaterno']); ?></td>
                                        <td><?= htmlspecialchars($usuario['correo']); ?></td>
                                        <td><?= htmlspecialchars($usuario['cargo']); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?= $URL; ?>views/usuarios/show.php?id=<?= $usuario['idusuario']; ?>" class="btn btn-info btn-sm" title="Ver usuario">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm btn-revocar"
                                                    data-idusuario="<?= $usuario['idusuario']; ?>"
                                                    data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidopaterno']); ?>"
                                                    title="Revocar permiso">
                                                    <i class="fas fa-user-minus"></i>
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
</section>

<?php include '_modal_permiso.php'; ?>

<!-- Modal Asignar Usuario -->
<div class="modal fade" id="modalAsignarUsuario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i> Asignar Usuario al Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="selectUsuario">Seleccionar Usuario <span class="text-danger">*</span></label>
                    <select class="form-control select2" id="selectUsuario">
                        <option value=""></option>
                        <?php foreach ($usuariosSinPermiso as $u): ?>
                            <option value="<?= $u['idusuario']; ?>">
                                <?= htmlspecialchars(trim($u['nombre'] . ' ' . $u['apellidopaterno'] . ' ' . ($u['apellidomaterno'] ?? '')) . ($u['cargo'] ? ' — ' . $u['cargo'] : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarAsignacion">
                    <i class="fas fa-user-plus"></i> Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const idPermiso = <?= $id; ?>;
</script>

<?php
include_once '../layouts/messages.php';
include_once '../layouts/footer.php';
?>