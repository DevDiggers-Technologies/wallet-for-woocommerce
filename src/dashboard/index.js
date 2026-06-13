/**
 * Dashboard Scripts
 * 
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

import './dashboard.less';
import Chart from 'chart.js/auto';

class DDWCWM_Dashboard {
    constructor() {
        this.init();
    }

    init() {
        if (typeof ddwcwmDashboardData === 'undefined') {
            return;
        }
        
        this.initDateRangeToggle();
        this.initCharts();
    }

    /**
     * Initialize date range toggle functionality
     */
    initDateRangeToggle() {
        const picker = document.getElementById('ddwcwm-date-range-picker');
        const dropdown = document.getElementById('ddwcwm-date-range-dropdown');
        const presets = document.querySelectorAll('.ddwcwm-date-preset');
        const applyCustom = document.querySelector('.ddwcwm-apply-custom-range');
        const inputField = document.getElementById('ddwcwm-selected-range');
        const form = document.querySelector('.ddwcwm-date-filter-form');

        if (picker && dropdown) {
            picker.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!picker.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });

            presets.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const range = btn.getAttribute('data-range');
                    if (inputField) inputField.value = range;
                    if (form) form.submit();
                });

                // Mark current preset as active
                if (btn.getAttribute('data-range') === ddwcwmDashboardData.dateRange.type) {
                    btn.classList.add('active');
                }
            });

            if (applyCustom) {
                applyCustom.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (inputField) inputField.value = 'custom';
                    if (form) form.submit();
                });
            }
        }
    }

    /**
     * Initialize charts
     */
    initCharts() {
        this.renderTransactionsChart();
        this.renderTypeBreakdownChart();
    }

    /**
     * Format date for chart labels
     */
    formatDate(dateStr, format) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        
        if (format === 'quarter') {
            const quarter = Math.ceil((date.getMonth() + 1) / 3);
            return `Q${quarter} ${date.getFullYear()}`;
        }
        
        if (format === 'month') {
            return new Intl.DateTimeFormat('en-US', { month: 'short', year: 'numeric' }).format(date);
        }
        
        return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' }).format(date);
    }

    /**
     * Determine date format based on range
     */
    getDateFormat() {
        const range = ddwcwmDashboardData.dateRange || {};
        const start = new Date(range.from);
        const end = new Date(range.to);
        const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        
        if (diffDays > 365) return 'quarter';
        if (diffDays > 90) return 'month';
        return 'day';
    }

    /**
     * Render Transactions Line Chart
     */
    renderTransactionsChart() {
        const ctx = document.getElementById('ddwcwm-transactions-chart');
        if (!ctx) return;

        const chartData = ddwcwmDashboardData.transactionsChart || [];
        
        if (chartData.length === 0 || chartData.every(item => item.count === 0)) {
            this.renderEmptyState(ctx, ddwcwmDashboardData.i18n.noData);
            return;
        }

        const format = this.getDateFormat();
        const labels = chartData.map(item => this.formatDate(item.date, format));
        const countData = chartData.map(item => item.count);
        const amountData = chartData.map(item => item.amount);
        const currency = ddwcwmDashboardData.currency || '';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: ddwcwmDashboardData.i18n.transactions,
                        data: countData,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.05)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y',
                    },
                    {
                        label: ddwcwmDashboardData.i18n.amount,
                        data: amountData,
                        borderColor: '#0256ff',
                        backgroundColor: 'rgba(2, 86, 255, 0.05)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#0256ff',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'center',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 6,
                            boxHeight: 6,
                            padding: 20,
                            font: { size: 11, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        cornerRadius: 15,
                        displayColors: true,
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        boxPadding: 4,
                        padding: 15,
                        titleFont: {
                            size: 15,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 14,
                            weight: '400'
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                let value = context.parsed.y;
                                if (context.datasetIndex === 1) {
                                    return label + currency + value.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return label + value.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af', font: { size: 11 } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: '#f3f4f6', drawBorder: false },
                        ticks: { color: '#9ca3af', font: { size: 11 } }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: {
                            color: '#9ca3af',
                            font: { size: 11 },
                            callback: value => currency + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }

    /**
     * Render Transaction Type Breakdown Doughnut Chart
     */
    renderTypeBreakdownChart() {
        const ctx = document.getElementById('ddwcwm-type-breakdown-chart');
        if (!ctx) return;

        const chartData = ddwcwmDashboardData.typeBreakdownChart || [];
        
        if (chartData.length === 0) {
            this.renderEmptyState(ctx, ddwcwmDashboardData.i18n.noData);
            return;
        }

        const labels   = chartData.map(item => item.type.replace(/_/g, ' ').toUpperCase());
        const data     = chartData.map(item => item.count);
        const amounts  = chartData.map(item => item.amount);
        const currency = ddwcwmDashboardData.currency || '';
        const colors   = [
            '#0256ff',  '#60a5fa', '#93c5fd', 
            '#bfdbfe', '#dbeafe', '#eff6ff'
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    hoverOffset: 10,
                    borderWidth: 0,
                    amounts: amounts // Store amounts in dataset for tooltip
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 6,
                            boxHeight: 6,
                            padding: 20,
                            font: { size: 11, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        cornerRadius: 15,
                        displayColors: true,
                        rtl: true,
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        boxPadding: 4,
                        padding: 15,
                        callbacks: {
                            title: function() {
                                return '';
                            },
                            label: function(context) {
                                return context.label || '';
                            },
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const count = context.parsed;
                                const amount = context.dataset.amounts[index];
                                return [
                                    `${ddwcwmDashboardData.i18n.transactions}: ${count}`,
                                    `${ddwcwmDashboardData.i18n.amount}: ${currency}${amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Render empty state in chart container
     */
    renderEmptyState(canvas, message) {
        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="ddwcwm-empty-state">
                <div class="ddwcwm-empty-content">
                    <p class="ddwcwm-empty-title">${message}</p>
                </div>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new DDWCWM_Dashboard();
});
