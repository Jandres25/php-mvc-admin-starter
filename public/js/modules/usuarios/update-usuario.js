/**
 * update-usuario.js - Script para la página de actualización de usuarios
 * 
 * Incluye validaciones de formulario, vista previa de imágenes
 * y mejoras de usabilidad para el formulario de actualización.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Usuarios
 * @author Jandres25
 * @version 1.0
 */

$(document).ready(function () {
    // Inicializar Select2
    initializeSelect2();

    // ============= JQUERY VALIDATE =============

    const idUsuario = $('input[name="idusuario"]').val() || '';

    $('#formUsuario').validate({
        rules: {
            nombre:          { required: true },
            apellidopaterno: { required: true },
            tipodocumento:   { required: true },
            numdocumento: {
                required: true,
                remote: {
                    url: baseUrl + 'controllers/usuarios/check_documento.php',
                    type: 'post',
                    data: {
                        tipodocumento: function () { return $('#tipodocumento').val(); },
                        numdocumento:  function () { return $('#numdocumento').val(); },
                        idusuario:     function () { return idUsuario; }
                    }
                }
            },
            correo: {
                required: true,
                email: true,
                remote: {
                    url: baseUrl + 'controllers/usuarios/check_correo.php',
                    type: 'post',
                    data: {
                        correo:    function () { return $('#correo').val(); },
                        idusuario: function () { return idUsuario; }
                    }
                }
            },
            cargo: { required: true },
            clave: { minlength: 8 },
            confirmar_clave: {
                minlength: 8,
                equalTo: {
                    param: '#clave',
                    depends: function () { return $('#clave').val().length > 0; }
                }
            },
            imagen: { extension: 'jpg|jpeg|png|gif|webp' }
        },
        messages: {
            nombre:          { required: 'El nombre es obligatorio.' },
            apellidopaterno: { required: 'El apellido paterno es obligatorio.' },
            tipodocumento:   { required: 'Seleccione un tipo de documento.' },
            numdocumento: {
                required: 'El número de documento es obligatorio.',
                remote:   'Este documento ya está registrado.'
            },
            correo: {
                required: 'El correo es obligatorio.',
                email:    'Ingrese un correo electrónico válido.',
                remote:   'Este correo ya está en uso.'
            },
            cargo:  { required: 'Seleccione un cargo.' },
            clave: {
                minlength: 'La contraseña debe tener al menos 8 caracteres.'
            },
            confirmar_clave: {
                minlength: 'La contraseña debe tener al menos 8 caracteres.',
                equalTo:   'Las contraseñas no coinciden.'
            },
            imagen: {
                extension: 'Solo se permiten imágenes (jpg, png, gif, webp).'
            }
        },
        submitHandler: function (form) {
            const $btn = $(form).find('[type="submit"]');
            $btn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
            form.submit();
        }
    });

    // Revalidar selects de Select2 al cambiar
    $('#tipodocumento, #cargo').on('change', function () {
        $(this).valid();
    });

    // Revalidar documento al cambiar tipo (las reglas dinámicas cambian)
    $('#tipodocumento').on('change', function () {
        if ($('#numdocumento').val()) $('#numdocumento').valid();
    });

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

        $('#profile-preview-name').text(nombreCompleto || 'Usuario');
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

});