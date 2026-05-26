/**
 * index-dashboard.js - Dashboard metrics charts
 *
 * Renders Chart.js visualizations reading data from data-* attributes
 * set by PHP on each canvas element. No HTML is generated here.
 * Re-renders automatically when the dark/light theme changes.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\Dashboard
 * @author Jandres25
 * @version 1.1
 */

/** References to active Chart instances for destroy-on-theme-change. */
const _charts = {};

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    renderAllCharts();
    watchThemeChanges();
});

/**
 * Returns true if dark mode is currently active.
 * @returns {boolean}
 */
function isDarkMode() {
    return document.documentElement.classList.contains('dark-mode');
}

/**
 * Applies Chart.js v2 global defaults based on the active theme.
 * Must be called before rendering any chart.
 */
function applyChartTheme() {
    const dark      = isDarkMode();
    const textColor = dark ? '#e9ecef' : '#666';
    const gridColor = dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

    // Chart.js v2 global defaults
    Chart.defaults.global.defaultFontColor                          = textColor;
    Chart.defaults.global.legend.labels.fontColor                   = textColor;
    Chart.defaults.scale.gridLines                                  = Chart.defaults.scale.gridLines || {};
    Chart.defaults.scale.gridLines.color                            = gridColor;
    Chart.defaults.scale.ticks                                      = Chart.defaults.scale.ticks || {};
    Chart.defaults.scale.ticks.fontColor                            = textColor;
}

/**
 * Destroys all active chart instances and re-renders them.
 * Called on initial load and on every theme change.
 */
function renderAllCharts() {
    Object.keys(_charts).forEach(function (key) {
        if (_charts[key]) {
            _charts[key].destroy();
            delete _charts[key];
        }
    });

    applyChartTheme();
    initUserStatusChart();
    initTopPermissionsChart();
    initUsersByMonthChart();
}

/**
 * Observes class changes on <html> and re-renders charts
 * when the dark-mode class is added or removed.
 */
function watchThemeChanges() {
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'class') {
                renderAllCharts();
            }
        });
    });

    observer.observe(document.documentElement, { attributes: true });
}

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

    _charts.userStatus = new Chart(canvas, {
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

    _charts.topPermissions = new Chart(canvas, {
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

    _charts.usersByMonth = new Chart(canvas, {
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
