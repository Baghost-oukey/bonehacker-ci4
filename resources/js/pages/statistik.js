
// Ambil URL dari bridge PHP
if (window.$) {
    window.$(document).ready(function ($) {
        if (!$('#tbody-analysis').length) return;
        const config = window.statistikPasienConfig;
        if (!config || typeof window.moment === "undefined") return;

        let start = window.moment().startOf('month');
        let end = window.moment().endOf('month');
        let myChart = null;

        $('#region_id').select2();
        window.moment.locale('id');

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end);
        }

        // --- FUNGSI DATEPICKER ---
        const drpOptions = {
            ranges: {
                'Hari Ini': [window.moment(), window.moment()],
                'Kemarin': [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
                'Minggu Lalu': [window.moment().subtract(1, 'weeks').startOf('week'), window.moment().subtract(1, 'weeks').endOf('week')],
                'Bulan Ini': [window.moment().startOf('month'), window.moment().endOf('month')],
                'Bulan Lalu': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [window.moment().startOf('year'), window.moment().endOf('year')],
            },
            startDate: start,
            endDate: end,
            linkedCalendars: false,
            showDropdowns: true,
            alwaysShowCalendars: true,
            locale: {
                format: 'DD/MM/YYYY',
                separator: " - ",
                applyLabel: "Pilih",
                cancelLabel: "Batal",
                fromLabel: "Dari",
                toLabel: "Sampai",
                customRangeLabel: "Custom Range",
                weekLabel: "M",
                daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
                monthNames: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
                firstDay: 1
            }
        };

        $('#reportrange').daterangepicker(drpOptions, cb);
        cb(drpOptions.startDate, drpOptions.endDate);

        $('#region_id').on('change', function () {
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate);
        });

        // --- FUNGSI AMBIL DATA ---
        function fetchStatistics(startDate, endDate) {
            var regionId = $('#region_id').val();

            $.ajax({
                url: config.fetchUrl,
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    region_id: regionId
                },
                dataType: 'json',
                success: function (data) {
                    $('#totalCount').text(data.summary.total.toLocaleString());
                    $('#newPatientsCount').text(data.summary.baru.toLocaleString());
                    $('#oldPatientsCount').text(data.summary.lama.toLocaleString());
                    $('#avgPerDay').text(data.summary.avg_per_day);

                    let pB = data.summary.total > 0 ? ((data.summary.baru / data.summary.total) * 100).toFixed(1) : 0;
                    let pL = data.summary.total > 0 ? ((data.summary.lama / data.summary.total) * 100).toFixed(1) : 0;
                    $('#percBaru').text(pB + '%  Pasein Baru');
                    $('#percLama').text(pL + '% Pasein Lama');

                    renderTable(data.details, regionId, startDate, endDate);
                    renderChart(data.details);
                }
            });
        }

        // --- FUNGSI RENDER TABLE ---
        // --- FUNGSI RENDER TABLE ---
        function renderTable(details, selectedRegion, start, end) {
            let html = '';
            let diff = end.diff(start, 'days') + 1;

            // Saring data berdasarkan region yang dipilih (LOGIKA TETAP)
            let filteredData = details;
            if (selectedRegion && selectedRegion !== "") {
                filteredData = details.filter(item => item.id == selectedRegion);
            }

            if (!filteredData || filteredData.length === 0 || (filteredData.length === 1 && filteredData[0].total_pasien == 0)) {
                // EMPTY STATE (Sesuai referensi Gambar 2)
                html = `<tr class="hover:bg-slate-50 transition">
                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-inbox mr-2 text-slate-300"></i>
                        Tidak ada data ditemukan untuk periode/wilayah ini.
                    </td>
                </tr>`;
            } else {
                filteredData.forEach(i => {
                    if (i.total_pasien > 0) {
                        // LOGIKA PERHITUNGAN TETAP
                        let pBaru = i.total_pasien > 0 ? ((i.pasien_baru / i.total_pasien) * 100).toFixed(1) : 0;
                        let pLama = i.total_pasien > 0 ? ((i.pasien_lama / i.total_pasien) * 100).toFixed(1) : 0;
                        let avg = (i.total_pasien / diff).toFixed(1);

                        // RENDER BARIS (Gaya Clean Minimalis ala Gambar 2)
                        html += `<tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-3.5 font-medium text-slate-800">${i.cabang.toUpperCase()}</td>
                    <td class="px-6 py-3.5 text-center text-slate-500">${avg}</td>
                    <td class="px-6 py-3.5 text-center text-slate-800">${i.total_pasien}</td>
                    <td class="px-6 py-3.5 text-center text-slate-500">${i.pasien_lama}</td>
                    <td class="px-6 py-3.5 text-center text-slate-500">${i.pasien_baru}</td>
                    <td class="px-6 py-3.5 text-center text-slate-500">${pLama}%</td>
                    <td class="px-6 py-3.5 text-center text-slate-500">${pBaru}%</td>
                </tr>`;
                    }
                });

                if (html === '') {
                    html = `<tr class="hover:bg-slate-50 transition">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-inbox mr-2 text-slate-300"></i>
                            Wilayah ini tidak memiliki aktivitas pasien pada rentang waktu ini.
                        </td>
                    </tr>`;
                }
            }
            $('#tbody-analysis').html(html);
        }

        // --- FUNGSI RENDER CHART ---
        function renderChart(details) {
            var labels = details.map(i => i.cabang.toUpperCase());
            var values = details.map(i => i.total_pasien);
            var ctx = document.getElementById('statisticChart').getContext('2d');
            var isHorizontal = labels.length > 10;
            var gradient = isHorizontal
                ? ctx.createLinearGradient(0, 0, 800, 0)
                : ctx.createLinearGradient(0, 400, 0, 0);

            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.9)');
            gradient.addColorStop(1, 'rgba(165, 180, 252, 0.2)');

            if (window.myChart) {
                window.myChart.destroy();
            }

            window.myChart = new window.Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Pasien',
                        data: values,
                        backgroundColor: gradient,
                        hoverBackgroundColor: '#4f46e5',
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 32,
                        categoryPercentage: 0.8,
                        barPercentage: 0.9
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: isHorizontal ? 'y' : 'x',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { size: 12, family: "'Inter', sans-serif", weight: 'normal' },
                            titleColor: '#94a3b8',
                            bodyFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return context.raw + ' Pasien';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                display: isHorizontal,
                                color: '#f1f5f9',
                                drawBorder: false,
                                borderDash: [4, 4]
                            },
                            ticks: {
                                color: '#64748b',
                                font: { size: 10, weight: '600', family: "'Inter', sans-serif" },
                                autoSkip: true,
                                maxRotation: 0
                            },
                            border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: !isHorizontal,
                                color: '#f1f5f9',
                                drawBorder: false,
                                borderDash: [4, 4]
                            },
                            ticks: {
                                color: '#64748b',
                                font: { size: 10, weight: '600', family: "'Inter', sans-serif" },
                                padding: 8
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