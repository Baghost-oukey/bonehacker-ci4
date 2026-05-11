import { Tooltip } from "chart.js";

/**
 * FinanceAnalytics - Professional State-Driven Module
 */
const FinanceAnalytics = {
    state: {
        charts: { trend: null, category: null },
        filters: {
            days: 7,
            region: $('#chartRegionFilter').length ? $('#chartRegionFilter').val() : 'all'
        },
        isLoading: false
    },

    init() {
        if (!document.getElementById('trendChart')) return;
        this.bindEvents();
        this.loadData();
    },

    bindEvents() {
        // Time Filter (Buttons)
        $('.time-filter-btn').on('click', (e) => {
            const $btn = $(e.currentTarget);
            $('.time-filter-btn').removeClass('active-filter bg-white text-slate-900 shadow-sm').addClass('text-slate-500 hover:text-slate-700');
            $btn.addClass('active-filter bg-white text-slate-900 shadow-sm').removeClass('text-slate-500 hover:text-slate-700');

            this.state.filters.days = $btn.data('value'); // Update state
            this.loadData();
        });

        // Region Filter (Dropdown)
        $('#chartRegionFilter').on('change', (e) => {
            this.state.filters.region = $(e.currentTarget).val(); // Update state
            this.loadData();
        });

        $('#btnRefreshChart').on('click', (e) => {
            if (this.state.isLoading) return;
            $(e.currentTarget).find('i').addClass('animate-spin');
            this.loadData(() => $(e.currentTarget).find('i').removeClass('animate-spin'));
        });

        // Export PDF
        $('#btnExportPdf').on('click', () => {
            const params = $.param(this.state.filters);
            window.open(`${window.financeStatsConfig.exportPdfUrl}?${params}`, '_blank');
        });

        // Export Excel
        $('#btnExportExcel').on('click', () => {
            const params = $.param(this.state.filters);
            window.open(`${window.financeStatsConfig.exportExcelUrl}?${params}`, '_blank');
        });
    },

    async loadData(callback = null) {
        this.state.isLoading = true;

        try {
            // Gunakan parameter langsung dari this.state.filters
            const response = await $.getJSON(window.financeStatsConfig.url, this.state.filters);

            if (response.status === 'success') {
                this.renderTrend(response.trend);
                this.renderCategory(response.structure);
                this.updateUI();
            }
        } catch (err) {
            console.error("FinanceAnalytics Error:", err);
        } finally {
            this.state.isLoading = false;
            if (callback) callback();
        }
    },

    renderTrend(data) {
        const isEmpty = !data || data.length === 0;
        this.toggleEmptyState('trend', isEmpty);
        if (isEmpty) return;

        const ctx = document.getElementById('trendChart').getContext('2d');
        if (this.state.charts.trend) this.state.charts.trend.destroy();

        const gradIn = ctx.createLinearGradient(0, 0, 0, 400);
        gradIn.addColorStop(0, 'rgba(20, 184, 166, 0.15)');
        gradIn.addColorStop(1, 'rgba(20, 184, 166, 0)');

        this.state.charts.trend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => this.formatDate(d.tanggal)),
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: data.map(d => d.pemasukan),
                        borderColor: '#14b8a6',
                        backgroundColor: gradIn,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 2
                    },
                    {
                        label: 'Pengeluaran',
                        data: data.map(d => d.pengeluaran),
                        borderColor: '#f43f5e',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0
                    }
                ]
            },
            options: this.getChartOptions()
        });
    },

    renderCategory(data) {
        const isEmpty = !data || data.length === 0;
        this.toggleEmptyState('category', isEmpty);
        if (isEmpty) return;

        const ctx = document.getElementById('categoryChart').getContext('2d');
        if (this.state.charts.category) this.state.charts.category.destroy();

        this.state.charts.category = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.kategori),
                datasets: [{
                    data: data.map(d => d.total),
                    backgroundColor: ['#0ea5e9', '#f43f5e', '#14b8a6', '#f59e0b', '#8b5cf6', '#64748b'],
                    borderWidth: 4,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: '600' } } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` Total: Rp ${new Intl.NumberFormat('id-ID').format(ctx.raw)}`
                        }
                    }
                }
            }
        });
    },

    toggleEmptyState(type, isEmpty) {
        const stateEl = type === 'trend' ? '#emptyTrendState' : '#emptyCategoryState';
        const canvasEl = type === 'trend' ? '#trendChart' : '#categoryChart';
        if (isEmpty) {
            $(stateEl).removeClass('hidden');
            $(canvasEl).addClass('opacity-0');
        } else {
            $(stateEl).addClass('hidden');
            $(canvasEl).removeClass('opacity-0');
        }
    },

    getChartOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(ctx.raw)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => 'Rp ' + Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) }
                },
                x: { grid: { display: false } }
            }
        };
    },

    formatDate(str) {
        return new Date(str).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    },

    updateUI() {
        $('#chartPeriodLabel').text(`Periode ${this.state.filters.days} Hari Terakhir`);
    }
};

$(document).ready(() => FinanceAnalytics.init());