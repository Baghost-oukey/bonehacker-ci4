
// Ambil URL dari bridge PHP
if (window.$) {
    window.$(document).ready(function ($) {
        if (!$('#tbody-analysis').length) return;
        const config = window.statistikPasienConfig;
        if (!config || typeof window.moment === "undefined") return;

        let start = window.moment().startOf('month');
        let end = window.moment().endOf('month');
        let myChart = null;
        $('#region_id').select2({
            width: '100%'
        });
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
            const progressionCircle = '<i class="fas fa-circle-notch fa-spin text-slate-300"></i>';
            $('#totalCount, #newPatientsCount, #oldPatientsCount, #avgPerDay').html(progressionCircle);
            $('#percBaru, #percLama').text('Menghitung...');

            const loadingHtml = `
                <tr class="hover:bg-slate-50 transition">
                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-circle-notch fa-spin mr-2 text-indigo-500 text-xl"></i>
                        Sedang menyinkronkan data...
                    </td>
                </tr>
            `;
            const mobileLoadingHtml = `
                <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                    <i class="fas fa-circle-notch fa-spin mr-2 text-indigo-500 text-xl"></i>
                    Sedang menyinkronkan data...
                </div>
            `;
            $('#tbody-analysis').html(loadingHtml);
            $('#mobile-analysis-container').html(mobileLoadingHtml);
            let requestData = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD'),
                region_id: regionId
            };
            requestData[config.csrfTokenName] = config.csrfHash;
            $.ajax({
                url: config.fetchUrl,
                method: 'GET',
                data: requestData,
                dataType: 'json',
                success: function (data) {
                    if (data.csrf_hash) config.csrfHash = data.csrf_hash;
                    $('#totalCount').text(data.summary.total.toLocaleString());
                    $('#newPatientsCount').text(data.summary.baru.toLocaleString());
                    $('#oldPatientsCount').text(data.summary.lama.toLocaleString());
                    $('#avgPerDay').text(data.summary.avg_per_day);

                    let pB = data.summary.total > 0 ? ((data.summary.baru / data.summary.total) * 100).toFixed(1) : 0;
                    let pL = data.summary.total > 0 ? ((data.summary.lama / data.summary.total) * 100).toFixed(1) : 0;
                    $('#percBaru').text(pB + '% Pasien Baru');
                    $('#percLama').text(pL + '% Pasien Lama');

                    renderTable(data.details, regionId, startDate, endDate);
                    renderChart(data.details);
                }
            });
        }

        // --- FUNGSI RENDER TABLE ---
        function renderTable(details, selectedRegion, start, end) {
            let html = '';
            let mHtml = '';
            let diff = end.diff(start, 'days') + 1;
            let filteredData = details;
            if (selectedRegion && selectedRegion !== "") {
                filteredData = details.filter(item => item.id == selectedRegion);
            }
            if (!filteredData || filteredData.length === 0 || (filteredData.length === 1 && filteredData[0].total_pasien == 0)) {
                const emptyHtml = `
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-inbox mr-2 text-slate-300"></i>
                            Tidak ada data ditemukan untuk periode/wilayah ini.
                        </td>
                    </tr>
                `;
                const mEmptyHtml = `
                    <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-inbox mr-2 text-slate-300"></i>
                        Tidak ada data ditemukan.
                    </div>
                `;
                html = emptyHtml;
                mHtml = mEmptyHtml;
            } else {
                filteredData.forEach(i => {
                    if (i.total_pasien > 0) {
                        // LOGIKA PERHITUNGAN TETAP
                        let pBaru = i.total_pasien > 0 ? ((i.pasien_baru / i.total_pasien) * 100).toFixed(1) : 0;
                        let pLama = i.total_pasien > 0 ? ((i.pasien_lama / i.total_pasien) * 100).toFixed(1) : 0;
                        let avg = (i.total_pasien / diff).toFixed(1);

                        // RENDER BARIS DESKTOP
                        html += `<tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-0">
                            <td class="px-6 py-4 font-bold text-slate-800 text-xs uppercase">${i.cabang}</td>
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">${avg}</td>
                            <td class="px-6 py-4 text-center text-slate-900 font-black">${i.total_pasien}</td>
                            <td class="px-6 py-4 text-center text-slate-500">${i.pasien_lama}</td>
                            <td class="px-6 py-4 text-center text-slate-500">${i.pasien_baru}</td>
                            <td class="px-6 py-4 text-center text-indigo-600 font-bold">${pLama}%</td>
                            <td class="px-6 py-4 text-center text-emerald-600 font-bold">${pBaru}%</td>
                        </tr>`;

                        // RENDER MOBILE CARDS
                        mHtml += `
                            <div class="p-4 space-y-3 bg-white">
                                <div class="flex items-center justify-between">
                                    <span class="text-[13px] font-black text-slate-900 uppercase tracking-tight">${i.cabang}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Total:</span>
                                        <span class="text-[14px] font-black text-slate-900">${i.total_pasien}</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block mb-1">Rerata / Hari</span>
                                        <span class="text-[12px] font-black text-slate-700">${avg}</span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block mb-1">Pasien Lama</span>
                                        <span class="text-[12px] font-black text-indigo-600">${i.pasien_lama} <span class="text-[9px] text-slate-400 ml-1">(${pLama}%)</span></span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block mb-1">Pasien Baru</span>
                                        <span class="text-[12px] font-black text-emerald-600">${i.pasien_baru} <span class="text-[9px] text-slate-400 ml-1">(${pBaru}%)</span></span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex items-center justify-center">
                                         <i class="fas fa-chart-line text-slate-200 text-xl"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });

                if (html === '') {
                    const noActivityHtml = `
                        <tr class="hover:bg-slate-50 transition">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                <i class="fas fa-inbox mr-2 text-slate-300"></i>
                                Wilayah ini tidak memiliki aktivitas pasien pada rentang waktu ini.
                            </td>
                        </tr>
                    `;
                    const mNoActivityHtml = `
                        <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-inbox mr-2 text-slate-300"></i>
                            Tidak ada aktivitas.
                        </div>
                    `;
                    html = noActivityHtml;
                    mHtml = mNoActivityHtml;
                }
            }
            $('#tbody-analysis').html(html);
            $('#mobile-analysis-container').html(mHtml);
        }


        // --- FUNGSI RENDER CHART ---
        function renderChart(details) {
            var filteredData = details.filter(i => i.total_pasien > 0);
            var ctx = document.getElementById('statisticChart').getContext('2d');
            if (window.myChart) {
                window.myChart.destroy();
            }
            if (filteredData.length === 0) {
                return;
            }
            var labels = filteredData.map(i => i.cabang.toUpperCase());
            var values = filteredData.map(i => i.total_pasien);

            window.myChart = new window.Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Pasien',
                        data: values,
                        backgroundColor: '#3b82f6', 
                        hoverBackgroundColor: '#2563eb',
                        borderRadius: 4,
                        maxBarThickness: 32,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', 
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
                            title: {
                                display: true,
                                text: 'Total Pasien', 
                                color: '#64748b',
                                font: { size: 12, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: { top: 10 }
                            },
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false,
                                borderDash: [4, 4] 
                            },
                            ticks: {
                                color: '#64748b',
                                font: { size: 10, weight: '600', family: "'Inter', sans-serif" },
                                precision: 0 
                            },
                            border: { display: false }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Cabang', 
                                color: '#64748b',
                                font: { size: 12, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: { bottom: 10 }
                            },
                            grid: {
                                display: false 
                            },
                            ticks: {
                                color: '#64748b',
                                font: { size: 10, weight: '600', family: "'Inter', sans-serif" }
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