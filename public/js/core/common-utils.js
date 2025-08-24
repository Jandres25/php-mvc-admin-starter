/**
 * common-utils.js - Utilidades JavaScript básicas para el sistema
 * 
 * Este archivo contiene funciones simples para inicializar componentes
 * comunes en el ProyectoBase.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Core
 * @author Jandres25
 * @version 1.0
 */

/**
 * Inicializa Select2 en los elementos seleccionados sin modificar sus opciones
 * 
 * @param {string} selector - Selector de los elementos a inicializar (opcional)
 * @param {object} options - Opciones adicionales para Select2 (opcional)
 */
function initializeSelect2(selector = '.select2', options = {}) {
    // Opciones predeterminadas para Select2
    const defaultOptions = {
        theme: 'bootstrap4',
        width: '100%',
        allowClear: false,
        minimumResultsForSearch: 7,
        closeOnSelect: true,
        dropdownAutoWidth: true,
        language: {
            noResults: function () {
                return "No se encontraron resultados";
            },
            searching: function () {
                return "Buscando...";
            },
            inputTooShort: function (args) {
                var remainingChars = args.minimum - args.input.length;
                return "Por favor ingrese " + remainingChars + " caracteres más";
            },
            loadingMore: function () {
                return "Cargando más resultados...";
            },
            removeAllItems: function () {
                return "Eliminar todos";
            }
        }
    };

    // Combinar opciones predeterminadas con las opciones personalizadas
    const mergedOptions = $.extend(true, {}, defaultOptions, options);

    // Inicializar Select2 en los elementos seleccionados
    $(selector).each(function () {
        // Si ya tiene Select2, destruirlo primero
        if ($(this).data('select2')) {
            $(this).select2('destroy');
        }

        // Aplicar Select2 sin modificar las opciones existentes
        $(this).select2(mergedOptions);
    });
}

/**
 * Actualiza un elemento Select2 después de cambiar sus opciones
 * 
 * @param {string} selector - Selector del elemento select
 * @param {string|number} valueToSelect - Valor a seleccionar (opcional)
 * @param {object} options - Opciones adicionales para Select2 (opcional)
 */
function refreshSelect2(selector, valueToSelect = null, options = {}) {
    // Destruir la instancia existente de Select2
    if ($(selector).data('select2')) {
        $(selector).select2('destroy');
    }

    // Reinicializar Select2
    initializeSelect2(selector, options);

    // Seleccionar el valor si se proporciona
    if (valueToSelect !== null) {
        $(selector).val(valueToSelect).trigger('change');
    }
}

/**
 * Inicializa tooltips con soporte básico para dispositivos móviles
 */
function initializeTooltips() {
    // Detectar si es un dispositivo táctil
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    // Configuración de tooltips según el tipo de dispositivo
    $('[data-toggle="tooltip"]').tooltip({
        trigger: isTouchDevice ? 'click' : 'hover',
        placement: 'auto',
        delay: isTouchDevice ? { show: 0, hide: 2000 } : { show: 50, hide: 100 }
    });

    if (isTouchDevice) {
        // En dispositivos táctiles, agregar clase para estilizar tooltips
        $('.tooltip').addClass('tooltip-touch');

        // Para enlaces con tooltips, mostrar un botón de info adicional
        $('a[data-toggle="tooltip"]').each(function () {
            const $link = $(this);

            // Verificar si ya tiene un botón de información
            if ($link.find('.info-btn').length === 0) {
                // Agregar un ícono de información junto al enlace
                $link.append('<span class="info-btn ml-1"><i class="fas fa-info-circle text-info"></i></span>');

                // Mostrar tooltip al hacer clic en el ícono de información
                $link.find('.info-btn').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $link.tooltip('show');
                });
            }
        });

        // Cerrar tooltips al tocar en otra parte
        $(document).on('touchstart', function (e) {
            if (!$(e.target).closest('[data-toggle="tooltip"], .tooltip').length) {
                $('[data-toggle="tooltip"]').tooltip('hide');
            }
        });
    }
}

/**
 * Formatea una fecha en formato legible (DD/MM/YYYY)
 * 
 * @param {string|Date} date - Fecha a formatear
 * @returns {string} Fecha formateada
 */
function formatDate(date) {
    if (!date) return '';

    const d = new Date(date);
    if (isNaN(d.getTime())) return '';

    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
}

/**
 * Formatea una hora en formato legible (HH:MM)
 * 
 * @param {string} time - Hora a formatear
 * @returns {string} Hora formateada
 */
function formatTime(time) {
    if (!time) return '';

    // Si es solo hora (HH:MM)
    if (time.length <= 5) return time;

    // Si es datetime, extraer solo la parte de la hora
    if (time.includes('T')) {
        const d = new Date(time);
        if (isNaN(d.getTime())) return '';
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }

    // Devolver solo HH:MM si viene con segundos (HH:MM:SS)
    return time.substring(0, 5);
}

/**
 * Formatea un número como moneda (Bs)
 * 
 * @param {number} amount - Monto a formatear
 * @param {number} decimals - Número de decimales (opcional)
 * @returns {string} Monto formateado
 */
function formatCurrency(amount, decimals = 2) {
    return 'Bs ' + parseFloat(amount).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Muestra una notificación toast usando SweetAlert2
 * 
 * @param {string} message - Mensaje a mostrar
 * @param {string} icon - Icono (success, error, warning, info)
 * @param {number} timer - Tiempo en milisegundos (opcional)
 */
function showToast(message, icon = 'success', timer = 3000) {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: icon,
            title: message
        });
    } else {
        // Fallback si Swal no está disponible
        alert(message);
    }
}

/**
 * Realiza una solicitud AJAX simplificada
 * 
 * @param {string} url - URL de la solicitud
 * @param {string} method - Método HTTP (GET, POST)
 * @param {object} data - Datos a enviar
 * @param {function} successCallback - Función de éxito
 * @param {function} errorCallback - Función de error (opcional)
 */
function ajaxRequest(url, method = 'GET', data = {}, successCallback, errorCallback = null) {
    $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (response) {
            if (successCallback && typeof successCallback === 'function') {
                successCallback(response);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error AJAX:', error);

            if (errorCallback && typeof errorCallback === 'function') {
                errorCallback(xhr, status, error);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error en la comunicación con el servidor'
                });
            }
        }
    });
}