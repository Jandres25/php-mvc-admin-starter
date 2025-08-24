/**
 * create-usuario.js - Script para la página de creación de usuarios
 * 
 * Incluye validaciones de formulario, vista previa de imágenes
 * y mejoras de usabilidad para el formulario de creación.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Usuarios
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    // Inicializar Select2
    initializeSelect2();

    // ============= VALIDACIÓN DE CAMPOS =============

    // Actualizar etiqueta del archivo seleccionado
    $('.custom-file-input').on('change', function () {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);

        // Mostrar vista previa de la imagen
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-image').attr('src', e.target.result);
                $('#preview-container').show();

                // Actualizar también la vista previa del perfil
                $('#profile-preview-img').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Validación de contraseñas mejorada
    function validatePasswords() {
        let clave = $('#clave').val();
        let confirmar = $('#confirmar_clave').val();
        let errorMessage = $('#password-error-message');

        if (confirmar.length > 0) {
            if (clave !== confirmar) {
                $('#confirmar_clave').addClass('is-invalid');
                errorMessage.show();
                return false;
            } else {
                $('#confirmar_clave').removeClass('is-invalid').addClass('is-valid');
                errorMessage.hide();
                return true;
            }
        }
        return true;
    }

    // Validar contraseñas en tiempo real
    $('#clave, #confirmar_clave').on('keyup', function () {
        validatePasswords();
    });

    // Validación del formulario antes de enviar
    $('#formUsuario').on('submit', function (e) {
        let clave = $('#clave').val();
        let isValid = true;

        // Validar coincidencia de contraseñas
        if (!validatePasswords()) {
            isValid = false;
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
        }

        // Validar longitud de contraseña
        if (clave.length < 6) {
            isValid = false;
            e.preventDefault();
            $('#clave').addClass('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La contraseña debe tener al menos 6 caracteres'
            });
        }

        return isValid;
    });

    // Validación de número de documento según tipo
    $('#tipodocumento').on('change', function () {
        let tipo = $(this).val();
        let numDocInput = $('#numdocumento');

        switch (tipo) {
            case 'DNI':
                numDocInput.attr('maxlength', '8');
                numDocInput.attr('pattern', '[0-9]{8}');
                numDocInput.attr('placeholder', 'Ingrese 8 dígitos');
                break;
            case 'RUC':
                numDocInput.attr('maxlength', '11');
                numDocInput.attr('pattern', '[0-9]{11}');
                numDocInput.attr('placeholder', 'Ingrese 11 dígitos');
                break;
            case 'Pasaporte':
                numDocInput.attr('maxlength', '12');
                numDocInput.removeAttr('pattern');
                numDocInput.attr('placeholder', 'Ingrese el número de pasaporte');
                break;
            default:
                numDocInput.removeAttr('maxlength');
                numDocInput.removeAttr('pattern');
                numDocInput.attr('placeholder', 'Ingrese el número de documento');
        }
    });

    // ============= MEJORAS DE USABILIDAD =============

    // Toggle para mostrar/ocultar contraseña
    $('#togglePassword').click(function () {
        const passwordField = $('#clave');
        const icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggleConfirmPassword').click(function () {
        const passwordField = $('#confirmar_clave');
        const icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Actualizar vista previa del perfil al cambiar los campos
    $('#nombre, #apellidopaterno, #apellidomaterno').on('input', function () {
        const nombre = $('#nombre').val() || '';
        const apellidoPaterno = $('#apellidopaterno').val() || '';
        const apellidoMaterno = $('#apellidomaterno').val() || '';

        let nombreCompleto = nombre;
        if (apellidoPaterno) nombreCompleto += ' ' + apellidoPaterno;
        if (apellidoMaterno) nombreCompleto += ' ' + apellidoMaterno;

        $('#profile-preview-name').text(nombreCompleto || 'Nuevo Usuario');
    });

    $('#cargo').change(function () {
        const cargo = $(this).val() || 'Cargo del usuario';
        $('#profile-preview-role').text(cargo);
    });

    $('#estado').change(function () {
        const estado = $(this).val();
        if (estado === '1') {
            $('#profile-preview-badge').removeClass('badge-danger').addClass('badge-success').text('Activo');
        } else {
            $('#profile-preview-badge').removeClass('badge-success').addClass('badge-danger').text('Inactivo');
        }
    });

    // Botones para seleccionar/deseleccionar todos los permisos
    $('#seleccionar-todos').click(function () {
        $('input[name="permisos[]"]').prop('checked', true);

        // Efecto visual
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Todos los permisos seleccionados',
            showConfirmButton: false,
            timer: 3000,
            toast: true
        });
    });

    $('#deseleccionar-todos').click(function () {
        $('input[name="permisos[]"]').prop('checked', false);

        // Efecto visual
        Swal.fire({
            position: 'top-end',
            icon: 'info',
            title: 'Todos los permisos deseleccionados',
            showConfirmButton: false,
            timer: 3000,
            toast: true
        });
    });

    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Ocultar mensajes de error al inicio
    $('#password-error-message').hide();
});