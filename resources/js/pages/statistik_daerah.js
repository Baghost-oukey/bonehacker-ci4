/**
 * Statistik Daerah Page Script
 * Lokasi: resource/app/js/pages/statistikdaerah.js
 */

// --- INIT SCRIPT --- 
if (window.$) {
    window.$(document).ready(function ($) {
        if (!$('#statisticChart').length) return;
        const config = window.statistikDaerahConfig;
        if (!config || typeof window.moment === "undefined") return;
        $('.select2').select2({ width: '100%' });
        $('#region_id').select2({ width: '100%' });
        var currentFilter = 'daily';
        window.moment.locale('id');

        // --- DATEPICKER ---
        $('#reportrange').daterangepicker({
            startDate: window.moment().subtract(29, 'days'),
            endDate: window.moment(),
            opens: 'left',
            linkedCalendars: false, 
            showDropdowns: true,    
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
        var initialStart = window.moment().subtract(29, 'days');
        var initialEnd = window.moment();
        $('#reportrange span').html(initialStart.format('D MMMM YYYY') + ' - ' + initialEnd.format('D MMMM YYYY'));
        fetchKabupaten();
        fetchStatistics(initialStart, initialEnd, currentFilter);


        // --- Event Listeners ---
        $('#statisticFilter, #region_id').on('change', function () {
            currentFilter = $('#statisticFilter').val();
            triggerReload();
        });

        $('#kabupaten_id').on('change', function () {
            var kabId = $(this).val();
            if (kabId) {
                fetchKecamatan(kabId);
                $('#kecamatan_id').prop('disabled', false);
            } else {
                $('#kecamatan_id').prop('disabled', true).empty().append(new Option('Pilih Kecamatan', ''));
                $('#desa_id').prop('disabled', true).empty().append(new Option('Pilih Desa/Kelurahan', ''));
            }
            triggerReload();
        });

        $('#kecamatan_id').on('change', function () {
            var kecId = $(this).val();
            if (kecId) {
                fetchDesa(kecId);
                $('#desa_id').prop('disabled', false);
            } else {
                $('#desa_id').prop('disabled', true).empty().append(new Option('Pilih Desa/Kelurahan', ''));
            }
            triggerReload();
        });

        $('#desa_id').on('change', function () {
            triggerReload();
        });


        // --- RELOAD FILTERED DATA ---
        function triggerReload() {
            var drp = $('#reportrange').data('daterangepicker');
            if (drp) {
                fetchStatistics(drp.startDate, drp.endDate, currentFilter);
            }
        }


        // --- AJAX Functions ---
        // --- FETCH DATA UNTUK SELECT KABUPATEN, KECAMATAN, DESA ---
        function fetchKabupaten() {
            $.ajax({
                url: config.fetchKabupatenUrl,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    var $select = $('#kabupaten_id');
                    $select.empty().append(new Option('Pilih Kabupaten/Kota', ''));
                    response.forEach(function (row) {
                        $select.append(new Option(row.kabupaten_nama, row.kabupaten_id));
                    });
                },
                error: function (xhr, status, error) { console.error("Gagal load Kabupaten:", error); }
            });
        }

        function fetchKecamatan(kabId) {
            $.ajax({
                url: config.fetchKecamatanUrl,
                method: 'GET',
                data: { kabupaten_id: kabId },
                dataType: 'json',
                success: function (response) {
                    var $select = $('#kecamatan_id');
                    $select.empty().append(new Option('Pilih Kecamatan', ''));
                    response.forEach(function (row) {
                        $select.append(new Option(row.kecamatan_nama, row.kecamatan_id));
                    });
                },
                error: function (xhr, status, error) { console.error("Gagal load Kecamatan:", error); }
            });
        }

        function fetchDesa(kecId) {
            $.ajax({
                url: config.fetchDesaUrl,
                method: 'GET',
                data: { kecamatan_id: kecId },
                dataType: 'json',
                success: function (response) {
                    var $select = $('#desa_id');
                    $select.empty().append(new Option('Pilih Desa/Kelurahan', ''));
                    response.forEach(function (row) {
                        $select.append(new Option(row.desa_nama, row.desa_id));
                    });
                },
                error: function (xhr, status, error) { console.error("Gagal load Desa:", error); }
            });
        }

        function fetchStatistics(startDate, endDate, filter) {
            var finalStart = startDate.clone();
            var finalEnd = endDate.clone();
            var selectedLabel = "Jumlah Rekam Medis";
            if ($('#desa_id').val()) {
                selectedLabel += " Desa " + $('#desa_id option:selected').text();
            } else if ($('#kecamatan_id').val()) {
                selectedLabel += " Kecamatan " + $('#kecamatan_id option:selected').text();
            } else if ($('#kabupaten_id').val()) {
                selectedLabel += " Kabupaten " + $('#kabupaten_id option:selected').text();
            }

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
                    renderChart(labels, finalValues, filter, selectedLabel);
                },
                error: function (xhr, status, error) { console.error("Gagal load Statistik:", error); }
            });
        }


        // --- CHART RENDERING FUNCTION ---
        function renderChart(labels, values, filter, datasetLabel) {
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
                        label: datasetLabel, 
                        data: values,
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
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
                                    // Membuat label legend menjadi lebih deskriptif
                                    original[0].text = `${datasetLabel}: ${total} Pasien`;
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