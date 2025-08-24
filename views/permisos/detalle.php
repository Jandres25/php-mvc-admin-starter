<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

$idusuario = $_SESSION['usuario_id'];
$auth = new \Services\AuthorizationService();

// Verificar si el usuario tiene acceso al módulo
if (!($auth->tienePermisoNombre($idusuario, 'permisos')) && !($auth->esAdministrador($idusuario))) {
    $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta sección.';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL);
    exit;
}

// Verificar si se proporcionó un ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['mensaje'] = 'ID de permiso no válido';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/permisos');
    exit;
}

// Incluir el encabezado
include_once '../layouts/header.php';

// Instanciar el controlador y obtener los datos del permiso
$controller = new \Controllers\Permisos\PermisoController();
$permiso = $controller->getById($id);

// Verificar si el permiso existe
if (!$permiso) {
    $_SESSION['mensaje'] = 'Permiso no encontrado';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL . 'views/permisos');
    exit;
}

$module_scripts = ['permisos/detalle-permiso'];

// Obtener usuarios con este permiso
$usuarios = $permiso['usuarios'];
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
            <div class="col-md-12">
                <!-- Tarjeta de información del permiso -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Información del Permiso</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ID del Permiso:</label>
                                    <input type="text" class="form-control" readonly value="<?= $permiso['idpermiso']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre:</label>
                                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($permiso['nombre']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estado:</label>
                                    <p>
                                        <?php if ($permiso['estado'] == 1): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Usuarios con este permiso:</label>
                                    <p>
                                        <span class="badge badge-info"><?= count($usuarios); ?> usuario(s)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?= $URL; ?>views/permisos" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a la lista
                        </a>
                        <button type="button" class="btn btn-warning btn-editar ml-2"
                            data-id="<?= $permiso['idpermiso']; ?>"
                            data-nombre="<?= htmlspecialchars($permiso['nombre']); ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de usuarios con este permiso -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Usuarios con este Permiso</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="detallePermisos" class="table table-bordered table-hover table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Cargo</th>
                                        <th>Estado</th>
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
                                                <?php if ($usuario['estado'] == 1): ?>
                                                    <span class="badge badge-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $URL; ?>views/usuarios/show.php?id=<?= $usuario['idusuario']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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

<!-- Modal para Editar Permiso -->
<div class="modal fade" id="modalPermiso" tabindex="-1" role="dialog" aria-labelledby="modalPermisoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalPermisoLabel">Editar Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPermiso" method="post">
                <div class="modal-body">
                    <input type="hidden" id="permisoAction" name="action" value="edit">
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
                    <button type="submit" class="btn btn-warning" id="btnGuardarPermiso">
                        <i class="fas fa-save"></i> Actualizar
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
    $(document).ready(function() {
        // Configurar el modal de edición
        $('.btn-editar').on('click', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const descripcion = $(this).data('descripcion');

            $('#idPermiso').val(id);
            $('#nombre').val(nombre);
            $('#descripcion').val(descripcion);

            $('#modalPermiso').modal('show');
        });

        // Manejar el envío del formulario de edición
        $('#formPermiso').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: baseUrl + 'controllers/permisos/actualizar_permiso_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: formData,
                beforeSend: function() {
                    $('#btnGuardarPermiso').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#modalPermiso').modal('hide');
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                        $('#btnGuardarPermiso').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error en la comunicación con el servidor'
                    });
                    $('#btnGuardarPermiso').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar');
                }
            });
        });
    });
</script>