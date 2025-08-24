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

$module_scripts = ['usuarios/update-usuario'];
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1>Editar Usuario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= $URL; ?>views/usuarios"><i class="fas fa-users"></i> Usuarios</a></li>
                    <li class="breadcrumb-item active">Editar Usuario</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Formulario Principal (8 columnas) -->
            <div class="col-md-8">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Formulario de Edición de Usuario</h3>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form action="<?= $URL; ?>controllers/usuarios/actualizar_usuario.php" method="POST" enctype="multipart/form-data" id="formUsuario">
                        <input type="hidden" name="idusuario" value="<?= $usuario['idusuario']; ?>">
                        <div class="card-body">
                            <!-- Tarjeta de Información Personal -->
                            <div class="card card-outline card-primary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-address-card mr-2"></i>Información Personal</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Nombre -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nombre" name="nombre"
                                                    placeholder="Ingrese el nombre" value="<?= htmlspecialchars($usuario['nombre']); ?>" required>
                                            </div>
                                        </div>

                                        <!-- Apellido Paterno -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="apellidopaterno">Apellido Paterno <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="apellidopaterno" name="apellidopaterno"
                                                    placeholder="Ingrese el apellido paterno" value="<?= htmlspecialchars($usuario['apellidopaterno']); ?>" required>
                                            </div>
                                        </div>

                                        <!-- Apellido Materno -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="apellidomaterno">Apellido Materno</label>
                                                <input type="text" class="form-control" id="apellidomaterno" name="apellidomaterno"
                                                    placeholder="Ingrese el apellido materno" value="<?= htmlspecialchars($usuario['apellidomaterno'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Tipo de Documento -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tipodocumento">Tipo de Documento <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="tipodocumento" name="tipodocumento" required>
                                                    <option value="">Seleccione un tipo de documento</option>
                                                    <option value="DNI" <?= $usuario['tipodocumento'] == 'DNI' ? 'selected' : ''; ?>>DNI</option>
                                                    <option value="Pasaporte" <?= $usuario['tipodocumento'] == 'Pasaporte' ? 'selected' : ''; ?>>Pasaporte</option>
                                                    <option value="CI" <?= $usuario['tipodocumento'] == 'CI' ? 'selected' : ''; ?>>Cédula de Identidad</option>
                                                    <option value="RUC" <?= $usuario['tipodocumento'] == 'RUC' ? 'selected' : ''; ?>>RUC</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Número de Documento -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="numdocumento">Número de Documento <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="numdocumento" name="numdocumento"
                                                    placeholder="Ingrese el número de documento" value="<?= htmlspecialchars($usuario['numdocumento']); ?>"
                                                    maxlength="25" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Información Personal -->

                            <!-- Tarjeta de Información de Contacto -->
                            <div class="card card-outline card-info mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Información de Contacto</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Dirección -->
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="direccion">Dirección</label>
                                                <textarea class="form-control" id="direccion" name="direccion" rows="2"
                                                    placeholder="Ingrese la dirección"><?= htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Teléfono -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    </div>
                                                    <input type="tel" class="form-control" id="telefono" name="telefono"
                                                        placeholder="Ingrese el teléfono" value="<?= htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                                        maxlength="20">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Correo -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                                    </div>
                                                    <input type="email" class="form-control" id="correo" name="correo"
                                                        placeholder="Ingrese el correo electrónico" value="<?= htmlspecialchars($usuario['correo']); ?>"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Información de Contacto -->

                            <!-- Tarjeta de Información de Cuenta -->
                            <div class="card card-outline card-warning mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-user-lock mr-2"></i>Información de Cuenta</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Cargo -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="cargo">Cargo <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="cargo" name="cargo" required>
                                                    <option value="">Seleccione un cargo</option>
                                                    <option value="Administrador" <?= $usuario['cargo'] == 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                                                    <option value="Encargado" <?= $usuario['cargo'] == 'Encargado' ? 'selected' : ''; ?>>Encargado</option>
                                                    <option value="Vendedor" <?= $usuario['cargo'] == 'Vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Estado -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="estado">Estado</label>
                                                <select class="form-control select2" id="estado" name="estado">
                                                    <option value="1" <?= $usuario['estado'] == 1 ? 'selected' : ''; ?>>Activo</option>
                                                    <option value="0" <?= $usuario['estado'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Información de Cuenta -->

                            <!-- Tarjeta de Cambio de Contraseña -->
                            <div class="card card-outline card-danger mb-4 collapsed-card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-key mr-2"></i>Cambiar Contraseña (opcional)</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i> Deje estos campos en blanco si no desea cambiar la contraseña.
                                    </div>
                                    <div class="row">
                                        <!-- Contraseña -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="clave">Nueva Contraseña</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="clave" name="clave"
                                                        placeholder="Dejar en blanco para mantener la actual" autocomplete="off">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Mínimo 6 caracteres si decide cambiarla</small>
                                            </div>
                                        </div>

                                        <!-- Confirmar Contraseña -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="confirmar_clave">Confirmar Nueva Contraseña</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave"
                                                        placeholder="Confirme la nueva contraseña" autocomplete="off">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback" id="password-error-message">
                                                    Las contraseñas no coinciden
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Cambio de Contraseña -->

                            <!-- Tarjeta de Imagen de Perfil -->
                            <div class="card card-outline card-success mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-image mr-2"></i>Imagen de Perfil</h3>
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
                                                <label for="imagen">Seleccionar Nueva Imagen</label>
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="imagen" name="imagen"
                                                            accept="image/*">
                                                        <label class="custom-file-label" for="imagen">Seleccionar archivo</label>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WEBP. Máximo 2MB</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <!-- Imagen actual -->
                                            <label>Imagen Actual:</label><br>
                                            <?php if (isset($usuario['imagen']) && !empty($usuario['imagen'])): ?>
                                                <img src="<?= $URL; ?>public/uploads/usuarios/<?= $usuario['imagen']; ?>"
                                                    alt="Imagen actual" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php else: ?>
                                                <img src="<?= $URL; ?>public/uploads/usuarios/user_default.jpg"
                                                    alt="Imagen por defecto" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>

                                            <!-- Vista previa de imagen nueva -->
                                            <div id="preview-container" style="display: none; margin-top: 10px;">
                                                <label>Vista Previa Nueva Imagen:</label><br>
                                                <img id="preview-image" src="#" alt="Vista previa" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Imagen de Perfil -->

                            <!-- Tarjeta de Permisos -->
                            <div class="card card-outline card-secondary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-key mr-2"></i>Asignación de Permisos</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="seleccionar-todos">
                                                    <i class="fas fa-check-square mr-1"></i> Seleccionar todos
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deseleccionar-todos">
                                                    <i class="fas fa-square mr-1"></i> Deseleccionar todos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Permisos disponibles:</label>
                                                <div class="row">
                                                    <?php
                                                    // Obtener todos los permisos disponibles
                                                    $permisos = $authService->obtenerTodosLosPermisos();
                                                    foreach ($permisos as $permiso) :
                                                        // Verificar si el usuario tiene este permiso asignado
                                                        $checked = $authService->tienePermisoAsignado($usuario['idusuario'], $permiso['idpermiso']) ? 'checked' : '';
                                                    ?>
                                                        <div class="col-md-4">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="permiso_<?= $permiso['idpermiso'] ?>"
                                                                    name="permisos[]"
                                                                    value="<?= $permiso['idpermiso'] ?>"
                                                                    <?= $checked ?>>
                                                                <label class="custom-control-label" for="permiso_<?= $permiso['idpermiso'] ?>">
                                                                    <?= htmlspecialchars($permiso['nombre']) ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Permisos -->

                            <!-- Tarjeta de Información del Sistema -->
                            <div class="card card-outline card-secondary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Información del Sistema</h3>
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
                                                <label><i class="far fa-calendar-plus mr-1"></i> Fecha de Creación:</label>
                                                <p class="form-control bg-light">
                                                    <?= isset($usuario['fechacreacion']) ? date('d/m/Y H:i', strtotime($usuario['fechacreacion'])) : 'No disponible'; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-sync-alt mr-1"></i> Última Actualización:</label>
                                                <p class="form-control bg-light">
                                                    <?= isset($usuario['fechaactualizacion']) ? date('d/m/Y H:i', strtotime($usuario['fechaactualizacion'])) : 'No disponible'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Tarjeta Información del Sistema -->
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12 col-sm-auto mb-2 mb-sm-0 mr-sm-2">
                                    <button type="submit" class="btn btn-warning btn-block">
                                        <i class="fas fa-save mr-1"></i> Actualizar Usuario
                                    </button>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <a href="<?= $URL; ?>views/usuarios" class="btn btn-secondary btn-block">
                                        <i class="fas fa-times mr-1"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card -->
            </div>
            <!-- Fin formulario principal -->

            <!-- Guía de ayuda (4 columnas) -->
            <div class="col-md-4">
                <!-- Tarjeta de vista previa -->
                <div class="card card-warning mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Vista previa del perfil</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-preview">
                            <?php if (isset($usuario['imagen']) && !empty($usuario['imagen'])): ?>
                                <img id="profile-preview-img" src="<?= $URL; ?>public/uploads/usuarios/<?= $usuario['imagen']; ?>" class="img-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <img id="profile-preview-img" src="<?= $URL; ?>public/uploads/usuarios/user_default.jpg" class="img-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php endif; ?>
                            <h5 id="profile-preview-name" class="mt-3"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidopaterno'] . ' ' . $usuario['apellidomaterno']); ?></h5>
                            <p id="profile-preview-role" class="text-muted"><?= htmlspecialchars($usuario['cargo']); ?></p>
                            <?php if ($usuario['estado'] == 1): ?>
                                <div id="profile-preview-badge" class="badge badge-success">Activo</div>
                            <?php else: ?>
                                <div id="profile-preview-badge" class="badge badge-danger">Inactivo</div>
                            <?php endif; ?>
                        </div>
                        <div class="alert alert-light mt-3">
                            <small><i class="fas fa-info-circle"></i> Esta es una vista previa de cómo se verá el perfil del usuario.</small>
                        </div>
                    </div>
                </div>

                <!-- Acordeón de ayuda -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle mr-1"></i> Guía para actualizar usuarios</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion" id="accordionGuide">
                            <!-- Guía de Información Personal -->
                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            <i class="fas fa-address-card mr-1"></i> Información Personal
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-info">
                                            <ul class="mb-0">
                                                <li>Complete todos los campos marcados con <span class="text-danger">*</span></li>
                                                <li>El <strong>apellido materno</strong> es opcional</li>
                                                <li>Verifique que el <strong>número de documento</strong> sea correcto</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guía de Cambio de Contraseña -->
                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingTwo">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            <i class="fas fa-key mr-1"></i> Cambio de Contraseña
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-warning">
                                            <ul class="mb-0">
                                                <li>Deje ambos campos en blanco si <strong>no desea cambiar</strong> la contraseña</li>
                                                <li>Si decide cambiarla, la <strong>contraseña</strong> debe tener al menos 6 caracteres</li>
                                                <li>Se recomienda usar letras, números y símbolos para mayor seguridad</li>
                                                <li>Ambas contraseñas deben coincidir</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guía de Permisos -->
                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingThree">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            <i class="fas fa-shield-alt mr-1"></i> Permisos
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-info">
                                            <ul class="mb-0">
                                                <li>Asigne los <strong>permisos</strong> según las funciones que realizará el usuario</li>
                                                <li>Los usuarios con cargo <strong>Administrador</strong> pueden tener todos los permisos</li>
                                                <li>Use los botones de selección rápida para facilitar la asignación</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guía de Imagen -->
                            <div class="card mb-0 border-0">
                                <div class="card-header" id="headingFour">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            <i class="fas fa-image mr-1"></i> Imagen de Perfil
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionGuide">
                                    <div class="card-body">
                                        <div class="callout callout-success">
                                            <ul class="mb-0">
                                                <li>Seleccione una nueva imagen <strong>solo si desea cambiarla</strong></li>
                                                <li>Formatos recomendados: JPG, PNG</li>
                                                <li>Tamaño recomendado: cuadrada de 500x500 píxeles</li>
                                                <li>Si no sube una nueva imagen, se mantendrá la actual</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Importante:</strong> Verifique que el correo electrónico y número de documento no entren en conflicto con otro usuario.
                        </div>
                    </div>
                </div>
                <!-- Fin acordeón de ayuda -->
            </div>
            <!-- Fin guía de ayuda -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php
include_once '../layouts/mensajes.php';
include_once '../layouts/footer.php';
?>