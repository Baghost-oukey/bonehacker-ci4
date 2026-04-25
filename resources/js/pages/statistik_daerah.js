/**
 * Statistik Daerah Page Script
 * Lokasi: resource/app/js/pages/statistikdaerah.js
 */

if (window.$) {
    window.$(document).ready(function ($) {

        // Guard: pastikan elemen utama ada
        if (!$('#statisticChart').length) return;

        const config = window.statistikDaerahConfig;
        if (!config || typeof window.moment === "undefined") return;

        // Initialize Select2
        $('.select2').select2({ width: '100%' });
        $('#region_id').select2({ width: '100%' });

        var currentFilter = 'daily';
        window.moment.locale('id');

        // Date Range Picker Initialization
        $('#reportrange').daterangepicker({
            startDate: window.moment().subtract(6, 'days'),
            endDate: window.moment(),
            opens: 'left',
            ranges: {
                'Hari Ini': [window.moment(), window.moment()],
                'Kemarin': [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
                '7 Hari Terakhir': [window.moment().subtract(6, 'days'), window.moment()],
                '30 Hari Terakhir': [window.moment().subtract(29, 'days'), window.moment()],
                'Bulan Ini': [window.moment().startOf('month'), window.moment().endOf('month')],
                'Bulan Lalu': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')]
            }
        }, function (start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end, currentFilter);
        });

        // Set Initial Date Display
        var initialStart = window.moment().subtract(6, 'days');
        var initialEnd = window.moment();
        $('#reportrange span').html(initialStart.format('D MMMM YYYY') + ' - ' + initialEnd.format('D MMMM YYYY'));

        // Load Initial Data
        fetchKabupaten();
        fetchStatistics(initialStart, initialEnd, currentFilter);

        // --- Event Listeners ---
        $('#statisticFilter, #region_id').on('change', function () {
            currentFilter = $('#statisticFilter').val();
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate, currentFilter);
        });

        $('#kabupaten_id').on('change', function () {
            var kabId = $(this).val();
            if (kabId) {
                fetchKecamatan(kabId);
                $('#kecamatan_id').prop('disabled', false);
            } else {
                $('#kecamatan_id').prop('disabled', true).html('<option value="">Pilih Kecamatan</option>');
                $('#desa_id').prop('disabled', true).html('<option value="">Pilih Desa/Kelurahan</option>');
            }
            triggerReload();
        });

        $('#kecamatan_id').on('change', function () {
            var kecId = $(this).val();
            if (kecId) {
                fetchDesa(kecId);
                $('#desa_id').prop('disabled', false);
            } else {
                $('#desa_id').prop('disabled', true).html('<option value="">Pilih Desa/Kelurahan</option>');
            }
            triggerReload();
        });

        $('#desa_id').on('change', function () {
            triggerReload();
        });

        function triggerReload() {
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate, currentFilter);
        }

        // --- AJAX Functions ---
        function fetchKabupaten() {
            $.ajax({
                url: config.fetchKabupatenUrl,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    var options = '<option value="">Pilih Kabupaten/Kota</option>';
                    response.forEach(function (row) {
                        options += `<option value="${row.kabupaten_id}">${row.kabupaten_nama}</option>`;
                    });
                    $('#kabupaten_id').html(options);
                }
            });
        }

        function fetchKecamatan(kabId) {
            $.ajax({
                url: config.fetchKecamatanUrl,
                method: 'GET',
                data: { kabupaten_id: kabId },
                dataType: 'json',
                success: function (response) {
                    var options = '<option value="">Pilih Kecamatan</option>';
                    response.forEach(function (row) {
                        options += `<option value="${row.kecamatan_id}">${row.kecamatan_nama}</option>`;
                    });
                    $('#kecamatan_id').html(options);
                }
            });
        }

        function fetchDesa(kecId) {
            $.ajax({
                url: config.fetchDesaUrl,
                method: 'GET',
                data: { kecamatan_id: kecId },
                dataType: 'json',
                success: function (response) {
                    var options = '<option value="">Pilih Desa/Kelurahan</option>';
                    response.forEach(function (row) {
                        options += `<option value="${row.desa_id}">${row.desa_nama}</option>`;
                    });
                    $('#desa_id').html(options);
                }
            });
        }

        function fetchStatistics(startDate, endDate, filter) {
            var finalStart = startDate.clone();
            var finalEnd = endDate.clone();
            $.ajax({
                url: config.fetchStatisticsUrl,
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    filter: filter,
                    region_id: $('#region_id').val(),
                    kabupaten_id: $('#kabupaten_id').val(),
                    kecamatan_id: $('#kecamatan_id').val(),
                    desa_id: $('#desa_id').val()
                },
                dataType: 'json',
                success: function (response) {
                    var labels = [];
                    var chartData = {};
                    var iter = finalStart.clone();
                    while (iter.isSameOrBefore(finalEnd, (filter === 'daily' ? 'day' : (filter === 'monthly' ? 'month' : 'year')))) {
                        var key = (filter === 'daily' ? iter.format('YYYY-MM-DD') : (filter === 'monthly' ? iter.format('YYYY-MM') : iter.format('YYYY')));
                        labels.push(key);
                        chartData[key] = 0;
                        iter.add(1, (filter === 'daily' ? 'days' : (filter === 'monthly' ? 'months' : 'years')));
                    }
                    if (Array.isArray(response)) {
                        response.forEach(function (item) {
                            if (chartData.hasOwnProperty(item.date)) {
                                chartData[item.date] = parseInt(item.total);
                            }
                        });
                    }
                    var finalValues = labels.map(l => chartData[l]);
                    renderChart(labels, finalValues, filter);
                }
            });
        }

        function renderChart(labels, values, filter) {
            var ctx = document.getElementById('statisticChart').getContext('2d');
            var displayLabels = labels.map(function (l) {
                if (filter === 'daily') return window.moment(l).format('D MMM');
                if (filter === 'monthly') return window.moment(l, 'YYYY-MM').format('MMM YYYY');
                return l;
            });

            if (window.myChart instanceof Chart) window.myChart.destroy();

            window.myChart = new window.Chart(ctx, {
                type: 'bar',
                data: {
                    labels: displayLabels,
                    datasets: [{
                        label: "Jumlah Rekam Medis",
                        data: values,
                        backgroundColor: 'rgba(79, 70, 229, 0.6)', // Indigo modern
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                generateLabels: function (chart) {
                                    const total = values.reduce((a, b) => a + b, 0);
                                    const original = window.Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                    original[0].text = `Total Rekam Medis: ${total}`;
                                    return original;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#64748b', autoSkip: false } }
                    }
                }
            });
        }
    });
}