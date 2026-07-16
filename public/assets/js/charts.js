/* ============================================
   YouTube Clone - Chart.js Helper Functions
   ============================================ */

'use strict';

const ChartPalette = {
    colors: ['#FF0000', '#3ea6ff', '#2ba640', '#f59e0b', '#dc3545', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'],
    dark: {
        textColor: '#aaaaaa',
        gridColor: 'rgba(255,255,255,0.06)',
        bgColor: '#272727'
    },
    light: {
        textColor: '#606060',
        gridColor: 'rgba(0,0,0,0.06)',
        bgColor: '#ffffff'
    },
    getCurrent() {
        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        return theme === 'dark' ? this.dark : this.light;
    }
};

function initLineChart(canvasId, labels, datasets, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const theme = ChartPalette.getCurrent();

    const defaultDatasets = datasets.map((ds, i) => ({
        label: ds.label || '',
        data: ds.data || [],
        borderColor: ds.color || ChartPalette.colors[i % ChartPalette.colors.length],
        backgroundColor: ds.backgroundColor || (ds.color || ChartPalette.colors[i % ChartPalette.colors.length]) + '20',
        borderWidth: ds.borderWidth || 2,
        fill: ds.fill !== undefined ? ds.fill : true,
        tension: ds.tension || 0.4,
        pointRadius: ds.pointRadius !== undefined ? ds.pointRadius : 0,
        pointHoverRadius: ds.pointHoverRadius || 4,
        ...ds
    }));

    return new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: defaultDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: datasets.length > 1, labels: { color: theme.textColor, font: { size: 12 } } },
                tooltip: { backgroundColor: '#323232', titleColor: '#fff', bodyColor: '#ddd', cornerRadius: 8, padding: 10 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: theme.textColor, font: { size: 10 }, maxTicksLimit: 7 } },
                y: { beginAtZero: true, grid: { color: theme.gridColor }, ticks: { color: theme.textColor, font: { size: 10 } } }
            },
            ...options
        }
    });
}

function initBarChart(canvasId, labels, datasets, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const theme = ChartPalette.getCurrent();

    const defaultDatasets = datasets.map((ds, i) => ({
        label: ds.label || '',
        data: ds.data || [],
        backgroundColor: ds.backgroundColor || ChartPalette.colors[i % ChartPalette.colors.length] + '80',
        borderColor: ds.borderColor || ChartPalette.colors[i % ChartPalette.colors.length],
        borderWidth: ds.borderWidth || 1,
        borderRadius: ds.borderRadius || 4,
        ...ds
    }));

    return new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: defaultDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: datasets.length > 1, labels: { color: theme.textColor, font: { size: 12 } } },
                tooltip: { backgroundColor: '#323232', titleColor: '#fff', bodyColor: '#ddd', cornerRadius: 8, padding: 10 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: theme.textColor, font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: theme.gridColor }, ticks: { color: theme.textColor, font: { size: 10 } } }
            },
            ...options
        }
    });
}

function initDoughnutChart(canvasId, labels, data, colors = null) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const theme = ChartPalette.getCurrent();
    const chartColors = colors || ChartPalette.colors.slice(0, data.length);

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: chartColors.map(c => c + 'CC'),
                borderColor: chartColors,
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: theme.textColor, font: { size: 12 }, padding: 16, usePointStyle: true, pointStyleWidth: 10 }
                },
                tooltip: { backgroundColor: '#323232', titleColor: '#fff', bodyColor: '#ddd', cornerRadius: 8, padding: 10 }
            }
        }
    });
}

function initAreaChart(canvasId, labels, datasets, options = {}) {
    return initLineChart(canvasId, labels, datasets.map(ds => ({ ...ds, fill: true })), options);
}

function initPieChart(canvasId, labels, data, colors = null) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const theme = ChartPalette.getCurrent();
    const chartColors = colors || ChartPalette.colors.slice(0, data.length);

    return new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: chartColors.map(c => c + 'CC'),
                borderColor: chartColors,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: theme.textColor, font: { size: 12 }, padding: 16, usePointStyle: true }
                },
                tooltip: { backgroundColor: '#323232', titleColor: '#fff', bodyColor: '#ddd', cornerRadius: 8 }
            }
        }
    });
}

function destroyChart(chartInstance) {
    if (chartInstance && typeof chartInstance.destroy === 'function') {
        chartInstance.destroy();
    }
}
