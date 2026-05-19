/**
 * index-dashboard.js - Dashboard metrics charts
 *
 * Renders Chart.js visualizations reading data from data-* attributes
 * set by PHP on each canvas element. No HTML is generated here.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Dashboard
 * @author Jandres25
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    initUserStatusChart();
    initTopPermissionsChart();
    initUsersByMonthChart();
});

/**
 * Donut chart — active vs inactive users.
 * Reads data-active and data-inactive from the canvas element.
 */
function initUserStatusChart() {
    const canvas   = document.getElementById('chartUserStatus');
    const fallback = document.getElementById('chartUserStatusFallback');

    if (!canvas || !fallback) return;

    const active   = parseInt(canvas.dataset.active,   10) || 0;
    const inactive = parseInt(canvas.dataset.inactive, 10) || 0;

    if (active === 0 && inactive === 0) {
        canvas.style.display   = 'none';
        fallback.style.display = 'block';
        return;
    }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [active, inactive],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });
}

/**
 * Bar chart — top 5 permissions by assigned users.
 * Reads data-chart (JSON array) from the canvas element.
 */
function initTopPermissionsChart() {
    const canvas   = document.getElementById('chartTopPermissions');
    const fallback = document.getElementById('chartTopPermissionsFallback');

    if (!canvas || !fallback) return;

    const dataset = JSON.parse(canvas.dataset.chart || '[]');

    if (!dataset.length) {
        canvas.style.display   = 'none';
        fallback.style.display = 'block';
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: dataset.map(p => p.name),
            datasets: [{
                label: 'Assigned users',
                data: dataset.map(p => p.total_users),
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderColor:     'rgba(0, 123, 255, 1)',
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                },
            },
        },
    });
}

/**
 * Line chart — user registrations per month (last 6 months).
 * Reads data-chart (JSON array) from the canvas element.
 */
function initUsersByMonthChart() {
    const canvas   = document.getElementById('chartUsersByMonth');
    const fallback = document.getElementById('chartUsersByMonthFallback');

    if (!canvas || !fallback) return;

    const dataset = JSON.parse(canvas.dataset.chart || '[]');
    const hasData = dataset.some(e => e.total > 0);

    if (!hasData) {
        canvas.style.display   = 'none';
        fallback.style.display = 'block';
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: dataset.map(e => formatYearMonth(e.ym)),
            datasets: [{
                label: 'Registered users',
                data: dataset.map(e => e.total),
                borderColor:     'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                },
            },
        },
    });
}

/**
 * Formats 'YYYY-MM' as 'Mon YYYY' using the browser locale.
 * @param {string} ym  e.g. '2025-11'
 * @returns {string}   e.g. 'Nov 2025'
 */
function formatYearMonth(ym) {
    const [year, month] = ym.split('-');
    const date = new Date(parseInt(year, 10), parseInt(month, 10) - 1, 1);
    return date.toLocaleDateString(undefined, { month: 'short', year: 'numeric' });
}
