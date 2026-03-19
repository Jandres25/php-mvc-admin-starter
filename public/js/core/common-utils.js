/**
 * common-utils.js - Basic JavaScript utilities for the system
 *
 * Contains helper functions for initializing common components
 * in the project.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Core
 * @author Jandres25
 * @version 1.0
 */

/**
 * Initializes Select2 on selected elements without modifying their options
 *
 * @param {string} selector - Selector for elements to initialize (optional)
 * @param {object} options  - Additional Select2 options (optional)
 */
function initializeSelect2(selector = '.select2', options = {}) {
    const defaultOptions = {
        theme: 'bootstrap4',
        width: '100%',
        allowClear: false,
        minimumResultsForSearch: 7,
        closeOnSelect: true,
        dropdownAutoWidth: true,
        language: {
            noResults: function () {
                return "No results found";
            },
            searching: function () {
                return "Searching...";
            },
            inputTooShort: function (args) {
                var remaining = args.minimum - args.input.length;
                return "Please enter " + remaining + " more character" + (remaining === 1 ? "" : "s");
            },
            loadingMore: function () {
                return "Loading more results...";
            },
            removeAllItems: function () {
                return "Remove all items";
            }
        }
    };

    const mergedOptions = $.extend(true, {}, defaultOptions, options);

    $(selector).each(function () {
        if ($(this).data('select2')) {
            $(this).select2('destroy');
        }
        $(this).select2(mergedOptions);
    });
}

/**
 * Refreshes a Select2 element after its options have changed
 *
 * @param {string}        selector      - Select element selector
 * @param {string|number} valueToSelect - Value to select after refresh (optional)
 * @param {object}        options       - Additional Select2 options (optional)
 */
function refreshSelect2(selector, valueToSelect = null, options = {}) {
    if ($(selector).data('select2')) {
        $(selector).select2('destroy');
    }

    initializeSelect2(selector, options);

    if (valueToSelect !== null) {
        $(selector).val(valueToSelect).trigger('change');
    }
}

/**
 * Initializes tooltips with basic mobile device support
 */
function initializeTooltips() {
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    $('[data-toggle="tooltip"]').tooltip({
        trigger: isTouchDevice ? 'click' : 'hover',
        placement: 'auto',
        delay: isTouchDevice ? { show: 0, hide: 2000 } : { show: 50, hide: 100 }
    });

    if (isTouchDevice) {
        $('.tooltip').addClass('tooltip-touch');

        $('a[data-toggle="tooltip"]').each(function () {
            const $link = $(this);

            if ($link.find('.info-btn').length === 0) {
                $link.append('<span class="info-btn ml-1"><i class="fas fa-info-circle text-info"></i></span>');

                $link.find('.info-btn').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $link.tooltip('show');
                });
            }
        });

        $(document).on('touchstart', function (e) {
            if (!$(e.target).closest('[data-toggle="tooltip"], .tooltip').length) {
                $('[data-toggle="tooltip"]').tooltip('hide');
            }
        });
    }
}

/**
 * Formats a date into a readable string (MM/DD/YYYY)
 *
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date
 */
function formatDate(date) {
    if (!date) return '';

    const d = new Date(date);
    if (isNaN(d.getTime())) return '';

    return `${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}/${d.getFullYear()}`;
}

/**
 * Formats a time string into a readable string (HH:MM)
 *
 * @param {string} time - Time to format
 * @returns {string} Formatted time
 */
function formatTime(time) {
    if (!time) return '';

    if (time.length <= 5) return time;

    if (time.includes('T')) {
        const d = new Date(time);
        if (isNaN(d.getTime())) return '';
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }

    return time.substring(0, 5);
}

/**
 * Formats a number as currency (USD)
 *
 * @param {number} amount   - Amount to format
 * @param {number} decimals - Number of decimal places (optional)
 * @returns {string} Formatted amount
 */
function formatCurrency(amount, decimals = 2) {
    return '$ ' + parseFloat(amount).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Shows a toast notification using SweetAlert2
 *
 * @param {string} message - Message to display
 * @param {string} icon    - Icon type (success, error, warning, info)
 * @param {number} timer   - Duration in milliseconds (optional)
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
        alert(message);
    }
}

/**
 * Performs a simplified AJAX request
 *
 * @param {string}   url             - Request URL
 * @param {string}   method          - HTTP method (GET, POST)
 * @param {object}   data            - Data to send
 * @param {function} successCallback - Success callback
 * @param {function} errorCallback   - Error callback (optional)
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
            console.error('AJAX error:', error);

            if (errorCallback && typeof errorCallback === 'function') {
                errorCallback(xhr, status, error);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'A communication error occurred with the server.'
                });
            }
        }
    });
}
