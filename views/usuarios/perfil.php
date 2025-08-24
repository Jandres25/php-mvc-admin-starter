<?php
require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../../config/config.php';

$idusuario_session = $_SESSION['usuario_id'];
$authService = new \Services\AuthorizationService();

// Verificar si el usuario tiene acceso al módulo
if (!$authService->tienePermisoNombre($idusuario_session, 'perfil')) {
    $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta sección.';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL);
    exit;
}

// Verificar si el usuario está logueado
if (!isset($idusuario_session)) {
    // Redirigir al login si no está autenticado
    $_SESSION['mensaje'] = 'Debe iniciar sesión para acceder a su perfil.';
    $_SESSION['icono'] = 'warning';
    header('Location: ' . $URL . 'views/login/login.php');
    exit;
}

// Incluir el encabezado
include_once '../layouts/header.php';

// Instanciar el controlador y obtener los datos del usuario
$usuario_controller = new \Controllers\Usuarios\UsuarioController();
$usuario = $usuario_controller->editar($idusuario_session);

// Verificar si el usuario existe
if (!$usuario) {
    $_SESSION['mensaje'] = 'Usuario no encontrado.';
    $_SESSION['icono'] = 'error';
    header('Location: ' . $URL);
    exit;
}

$module_scripts = ['usuarios/perfil-usuario'];
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Perfil de Usuario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Perfil de Usuario</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna para la imagen y datos básicos -->
            <div class="col-md-4">
                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle"
                                src="<?= $URL . 'public/uploads/usuarios/' . (!empty($usuario['imagen']) && file_exists(__DIR__ . '/../../public/uploads/usuarios/' . $usuario['imagen']) ? htmlspecialchars($usuario['imagen']) : 'user_default.jpg'); ?>"
                                alt="User profile picture"
                                style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <h3 class="profile-username text-center"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidopaterno']); ?></h3>
                        <p class="text-muted text-center"><?= htmlspecialchars($usuario['cargo'] ?? 'N/A'); ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Correo</b> <a class="float-right"><?= htmlspecialchars($usuario['correo'] ?? 'N/A'); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Teléfono</b> <a class="float-right"><?= htmlspecialchars($usuario['telefono'] ?? 'N/A'); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Columna para los formularios de actualización -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#datosPersonales" data-toggle="tab">Datos Personales</a></li>
                            <li class="nav-item"><a class="nav-link" href="#cambiarPassword" data-toggle="tab">Cambiar Contraseña</a></li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Tab Datos Personales -->
                            <div class="active tab-pane" id="datosPersonales">
                                <form action="<?= $URL; ?>controllers/usuarios/procesar_actualizar_perfil.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="idusuario" value="<?= $usuario['idusuario']; ?>">

                                    <div class="card card-outline card-primary mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title">Información Personal</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Nombre -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="nombre" name="nombre"
                                                            value="<?= htmlspecialchars($usuario['nombre']); ?>" required>
                                                    </div>
                                                </div>

                                                <!-- Apellido Paterno -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="apellidopaterno">Apellido Paterno <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="apellidopaterno" name="apellidopaterno"
                                                            value="<?= htmlspecialchars($usuario['apellidopaterno']); ?>" required>
                                                    </div>
                                                </div>

                                                <!-- Apellido Materno -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="apellidomaterno">Apellido Materno</label>
                                                        <input type="text" class="form-control" id="apellidomaterno" name="apellidomaterno"
                                                            value="<?= htmlspecialchars($usuario['apellidomaterno'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-outline card-info mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title">Información de Contacto</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Dirección -->
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="direccion">Dirección</label>
                                                        <textarea class="form-control" id="direccion" name="direccion" rows="2"
                                                            placeholder="Ingrese su dirección"><?= htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Teléfono -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="telefono">Teléfono</label>
                                                        <input type="tel" class="form-control" id="telefono" name="telefono"
                                                            value="<?= htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                                            placeholder="Ingrese su teléfono">
                                                    </div>
                                                </div>

                                                <!-- Correo Electrónico -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                                                        <input type="email" class="form-control" id="correo" name="correo"
                                                            value="<?= htmlspecialchars($usuario['correo']); ?>"
                                                            placeholder="Ingrese su correo electrónico" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-outline card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">Imagen de Perfil</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Imagen actual -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Imagen Actual</label><br>
                                                        <?php if (isset($usuario['imagen']) && !empty($usuario['imagen'])): ?>
                                                            <img src="<?= $URL; ?>public/uploads/usuarios/<?= htmlspecialchars($usuario['imagen']); ?>"
                                                                alt="Imagen de perfil" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                                        <?php else: ?>
                                                            <img src="<?= $URL; ?>public/uploads/usuarios/user_default.jpg"
                                                                alt="Imagen por defecto" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Imagen nueva -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="imagen">Cambiar Imagen</label>
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                                                                <label class="custom-file-label" for="imagen">Seleccionar archivo</label>
                                                            </div>
                                                        </div>
                                                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WEBP. Máximo 2MB</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vista previa de imagen nueva -->
                                            <div class="row" id="preview-container" style="display: none;">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Vista Previa Nueva Imagen:</label><br>
                                                        <img id="preview-image" src="#" alt="Vista previa" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Guardar Cambios
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab Cambiar Contraseña -->
                            <div class="tab-pane" id="cambiarPassword">
                                <form id="formCambiarPassword" action="javascript:void(0)">
                                    <input type="hidden" name="idusuario" value="<?= $usuario['idusuario']; ?>">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Al cambiar su contraseña, se cerrará su sesión y deberá iniciar sesión nuevamente.
                                    </div>

                                    <div class="form-group row">
                                        <label for="clave_actual" class="col-sm-4 col-form-label">Contraseña Actual <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="password" class="form-control" id="clave_actual" name="clave_actual" placeholder="Contraseña Actual" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="nueva_clave" class="col-sm-4 col-form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="password" class="form-control" id="nueva_clave" name="nueva_clave" placeholder="Nueva Contraseña" autocomplete="off" required minlength="6">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="confirmar_nueva_clave" class="col-sm-4 col-form-label">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="password" class="form-control" id="confirmar_nueva_clave" name="confirmar_nueva_clave" placeholder="Confirmar Nueva Contraseña" autocomplete="off" required minlength="6">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="offset-sm-4 col-sm-8">
                                            <button type="submit" class="btn btn-danger" id="btnCambiarPassword">Cambiar Contraseña</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div><!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include_once '../layouts/mensajes.php';
include_once '../layouts/footer.php';
?>