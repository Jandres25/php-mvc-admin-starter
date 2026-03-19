<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

requirePermiso('perfil');

$idusuario_session = $_SESSION['usuario_id'];

if (!isset($idusuario_session)) {
    $_SESSION['mensaje'] = 'Debe iniciar sesión para acceder a su perfil.';
    $_SESSION['icono'] = 'warning';
    header('Location: ' . $URL . 'views/login/login.php');
    exit;
}

$module_scripts = ['usuarios/perfil-usuario'];

include_once '../layouts/header.php';

$usuario_controller = new \Controllers\Usuarios\UsuarioController();
$usuario = $usuario_controller->editar($idusuario_session);

if (!$usuario) {
    $_SESSION['mensaje'] = 'Usuario no encontrado.';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL);
    exit;
}

$imagen_src = $URL . 'public/uploads/usuarios/' . (!empty($usuario['imagen']) ? htmlspecialchars($usuario['imagen']) : 'user_default.jpg');
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Mi Perfil</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Mi Perfil</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- Columna izquierda — resumen -->
            <div class="col-md-4">
                <div class="card card-outline card-primary sticky-top">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img id="sidebar-avatar"
                                class="profile-user-img img-fluid img-circle"
                                src="<?= $imagen_src; ?>"
                                alt="Foto de perfil"
                                style="width:100px;height:100px;object-fit:cover;">
                        </div>
                        <h3 class="profile-username text-center mt-2">
                            <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidopaterno']); ?>
                        </h3>
                        <p class="text-muted text-center"><?= htmlspecialchars($usuario['cargo'] ?? 'Sin cargo'); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope mr-1"></i> Correo</b>
                                <span class="float-right text-muted small"><?= htmlspecialchars($usuario['correo']); ?></span>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-phone mr-1"></i> Teléfono</b>
                                <span class="float-right text-muted">
                                    <?= !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : 'No registrado'; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-toggle-on mr-1"></i> Estado</b>
                                <span class="float-right">
                                    <?php if ($usuario['estado'] == 1): ?>
                                        <span class="badge badge-success badge-pill">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger badge-pill">Inactivo</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Fin columna izquierda -->

            <!-- Columna derecha — formularios -->
            <div class="col-md-8">
                <div class="card card-outline card-outline-tabs card-primary">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="perfil-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#tab-datos" data-toggle="tab" role="tab">
                                    <i class="fas fa-user-edit mr-1"></i> Mis datos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#tab-password" data-toggle="tab" role="tab">
                                    <i class="fas fa-lock mr-1"></i> Contraseña
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- Tab: Mis datos -->
                            <div class="tab-pane fade show active" id="tab-datos" role="tabpanel">
                                <form action="<?= $URL; ?>controllers/usuarios/procesar_actualizar_perfil.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="idusuario" value="<?= $usuario['idusuario']; ?>">

                                    <!-- Foto de perfil -->
                                    <div class="card card-outline card-success mb-3">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="fas fa-camera mr-2"></i>Foto de perfil</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-5 text-center">
                                                    <img id="preview-image"
                                                        src="<?= $imagen_src; ?>"
                                                        class="img-circle img-thumbnail"
                                                        style="width:120px;height:120px;object-fit:cover;"
                                                        alt="Vista previa">
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                                                        <label class="custom-file-label" for="imagen">Seleccionar archivo</label>
                                                    </div>
                                                    <small class="form-text text-muted">JPG, PNG, GIF, WEBP — máx. 2 MB</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contacto -->
                                    <div class="card card-outline card-info mb-3">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="fas fa-address-book mr-2"></i>Contacto</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    </div>
                                                    <input type="tel" class="form-control" id="telefono" name="telefono"
                                                        value="<?= htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                                        placeholder="Ingrese su teléfono" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label for="direccion">Dirección</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                    </div>
                                                    <textarea class="form-control" id="direccion" name="direccion" rows="2"
                                                        placeholder="Ingrese su dirección"><?= htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save mr-1"></i> Guardar cambios
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <!-- Fin Tab: Mis datos -->

                            <!-- Tab: Contraseña -->
                            <div class="tab-pane fade" id="tab-password" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i> Al cambiar la contraseña se cerrará la sesión.
                                </div>
                                <form id="formCambiarPassword" action="javascript:void(0)">
                                    <input type="hidden" name="idusuario" value="<?= $usuario['idusuario']; ?>">

                                    <div class="form-group">
                                        <label for="clave_actual">Contraseña actual <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            </div>
                                            <input type="password" class="form-control" id="clave_actual" name="clave_actual"
                                                placeholder="Contraseña actual" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="nueva_clave">Nueva contraseña <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="password" class="form-control" id="nueva_clave" name="nueva_clave"
                                                placeholder="Mínimo 6 caracteres" autocomplete="off" required minlength="6">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmar_nueva_clave">Confirmar nueva contraseña <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="password" class="form-control" id="confirmar_nueva_clave" name="confirmar_nueva_clave"
                                                placeholder="Repita la nueva contraseña" autocomplete="off" required minlength="6">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-danger" id="btnCambiarPassword">
                                        <i class="fas fa-key mr-1"></i> Cambiar contraseña
                                    </button>
                                </form>
                            </div>
                            <!-- Fin Tab: Contraseña -->

                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin columna derecha -->

        </div>
    </div>
</section>

<?php
include_once '../layouts/messages.php';
include_once '../layouts/footer.php';
?>