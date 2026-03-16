/**
 * common-validate.js - Configuración global de jQuery Validate para Bootstrap 4
 *
 * Solo configura la integración visual (clases, errorPlacement).
 * Los mensajes y reglas se definen en cada módulo que llame a .validate().
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Core
 * @version 1.0
 */

$.validator.setDefaults({
    errorClass: 'is-invalid',
    validClass: 'is-valid',
    errorElement: 'span',

    // No ignorar selects ocultos por Select2
    ignore: ':hidden:not(select)',

    // Validar solo al salir del campo (blur) y en submit, no en cada keystroke
    onkeyup: false,

    /**
     * Coloca el mensaje de error dentro del form-group del campo.
     * La clase d-block es necesaria porque Bootstrap oculta .invalid-feedback por defecto.
     * @param {jQuery} error - Span de error generado por jQuery Validate
     * @param {jQuery} element - Campo inválido
     */
    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback d-block');
        element.closest('.form-group').append(error);
    },

    highlight: function (element, errorClass, validClass) {
        $(element).addClass(errorClass).removeClass(validClass);
    },

    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass(errorClass).addClass(validClass);
    },

    /** Limpiar mensaje al recuperar validez */
    success: function (label, element) {
        $(element).removeClass('is-invalid').addClass('is-valid');
    },

    /**
     * Submit por defecto: enviar el formulario nativo.
     * Cada módulo puede sobreescribir con su propio submitHandler.
     * @param {HTMLFormElement} form
     */
    submitHandler: function (form) {
        form.submit();
    }
});
