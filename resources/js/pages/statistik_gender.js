// --- SCRIPT INIT ---
if (window.$) {
    window.$(document).ready(function ($) {
        const config = window.statistikGenderConfig;
        if (!config || !$('#statisticChart').length || typeof window.moment === "undefined") return;
        if ($('#region_id').length) {
            $('#region_id').select2({
                width: '100%',
                placeholder: "Pilih Wilayah"
            });
        }

        var currentFilter = 'daily';
        moment.locale('id');

        // --- DATEPICKER FUNGSI ---
        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end, currentFilter);
        }

        if ($('#reportrange').length) {
            $('#reportrange').daterangepicker({
                startDate: moment().subtract(6, 'days'),
                endDate: moment(),
                locale: { format: 'D MMMM YYYY' },
                opens: 'left',
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            cb(moment().subtract(6, 'days'), moment());
        }

        // --- FILTER REGION ---
        $('#statisticFilter, #region_id').on('change', function () {
            currentFilter = $('#statisticFilter').val();
            var drp = $('#reportrange').data('daterangepicker');
            if (drp) {
                fetchStatistics(drp.startDate, drp.endDate, currentFilter);
            }
        });

        // --- AMBIL DATA ---
        function fetchStatistics(startDate, endDate, filter) {
            $.ajax({
                url: config.fetchUrl,
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    filter: filter,
                    region_id: $('#region_id').val()
                },
                dataType: 'json',
                success: function (response) {
                    renderChart(response);
                },
                error: function (xhr, status, error) {
                    console.error("Gagal mengambil data statistik gender:", error);
                }
            });
        }

        // --- CHART STATISTIK DATA ---
        function renderChart(data) {
            if (!data || data.length === 0) return;
            var labels = data.map(item => item.date);
            var male = data.map(item => parseInt(item.total_male) || 0);
            var female = data.map(item => parseInt(item.total_female) || 0);
            var ctx = document.getElementById('statisticChart').getContext('2d');
            if (window.myChart instanceof Chart) {
                window.myChart.destroy();
            }

            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Laki-laki',
                            data: male,
                            backgroundColor: '#6366f1',
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 6,
                            borderSkipped: false,
                            maxBarThickness: 32
                        },
                        {
                            label: 'Perempuan',
                            data: female,
                            backgroundColor: '#ec4899',
                            hoverBackgroundColor: '#db2777',
                            borderRadius: 6,
                            borderSkipped: false,
                            maxBarThickness: 32
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { family: "'Inter', sans-serif", size: 12, weight: '600' },
                                color: '#64748b',
                                generateLabels: function (chart) {
                                    const original = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                    original[0].text = 'Laki-laki: ' + male.reduce((a, b) => a + b, 0);
                                    original[1].text = 'Perempuan: ' + female.reduce((a, b) => a + b, 0);
                                    return original;
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, family: "'Inter', sans-serif", weight: 'normal' },
                            titleColor: '#94a3b8',
                            bodyFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                            cornerRadius: 8,
                            displayColors: true,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: "'Inter', sans-serif", size: 11 },
                                color: '#64748b'
                            },
                            border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false,
                                borderDash: [4, 4]
                            },
                            ticks: {
                                font: { family: "'Inter', sans-serif", size: 11, weight: '600' },
                                color: '#94a3b8',
                                padding: 10
                            },
                            border: { display: false }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            });
        }
    });
}