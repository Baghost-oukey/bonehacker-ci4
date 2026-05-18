// --- INIT SCRIPT ---
if (window.$) {
    window.$(document).ready(function ($) {
        if (!$('#table-marketing-body').length) return;
        var config = window.resourceConfig;
        if (!config) return;
        let myChart = null;

        moment.locale('id');
        $('#filter_region').select2({
            width: '100%'
        });

        let start = moment().startOf('month');
        let end = moment().endOf('month');

        function cb(startDate, endDate) {
            $('#reportrange span').html(startDate.format('D MMMM YYYY') + ' - ' + endDate.format('D MMMM YYYY'));
        }

        // --- MODAL DATEPICKER ---
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            showDropdowns: true,
            linkedCalendars: false,
            alwaysShowCalendars: false,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
            },
            locale: {
                format: 'YYYY-MM-DD',
                separator: " - ",
                applyLabel: "Pilih",
                cancelLabel: "Batal",
                customRangeLabel: "Rentang Custom",
                daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
                monthNames: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
                firstDay: 1
            }
        }, function (start, end) {
            cb(start, end);
            loadMarketingData(start, end);
        });
        cb(start, end);
        loadMarketingData(start, end);

        // --- BUTTON UPDATE ---
        $('#btn-update-analysis').on('click', function (e) {
            e.preventDefault();
            loadMarketingData();
        });

        // --- LOAD DATA ---
        function loadMarketingData(s = null, e = null) {
            let startDate, endDate;
            let drp = $('#reportrange').data('daterangepicker');

            // Pastikan ambil dari picker jika parameter s/e tidak ada
            startDate = (s) ? s.format('YYYY-MM-DD') : drp.startDate.format('YYYY-MM-DD');
            endDate = (e) ? e.format('YYYY-MM-DD') : drp.endDate.format('YYYY-MM-DD');

            $.ajax({
                url: config.fetchUrl,
                type: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    region_id: $('#filter_region').val()
                },
                dataType: "JSON",
                success: function (response) {
                    if (response.status == 'success') {
                        renderContent(response);
                    }
                }
            });
        }

        // --- TABLE DATA PASIEN ---
        function renderContent(response) {
            let labels = [],
                values = [],
                tableHtml = '';
            let totalAll = response.total_all_patients || 0;

            if (response.details && response.details.length > 0 && totalAll > 0) {
                $('#chart-container').removeClass('d-none hidden').addClass('d-block block');
                $('#no-data-message').removeClass('d-flex flex').addClass('d-none hidden');
                let mTableHtml = '';
                response.details.forEach(item => {
                    let label = item.saluran || 'Lainnya';
                    let percent = totalAll > 0 ? ((item.total_pasien / totalAll) * 100).toFixed(1) : 0;
                    labels.push(label);
                    values.push(item.total_pasien);
                    
                    // --- RENDER DESKTOP ROW ---
                    tableHtml += `
                    <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-0">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-5 text-center shrink-0 text-lg">
                                    ${getSocialIcon(label)}
                                </div>
                                <span class="font-medium text-slate-700 text-[13px]">${label}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-center text-slate-700 font-normal text-[13px]">
                            ${item.total_pasien} Pasien
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 text-[11px] font-medium tracking-wide">
                                ${percent}%
                             </span>
                        </td>
                    </tr>`;

                    // --- RENDER MOBILE CARD ---
                    mTableHtml += `
                        <div class="p-4 flex items-center justify-between gap-4 bg-white">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 shrink-0 text-xl shadow-sm">
                                    ${getSocialIcon(label)}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[13px] font-black text-slate-900 uppercase tracking-tight truncate">${label}</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">${item.total_pasien} Pasien</span>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[11px] font-black tracking-wide">
                                    ${percent}%
                                </span>
                            </div>
                        </div>
                    `;
                });
                $('#table-marketing-body').html(tableHtml);
                $('#mobile-marketing-container').html(mTableHtml);
                updateChart(labels, values);
            } else {
                renderEmptyState();
            }
        }

        // --- JIKA DATA KOSONG ---
        function renderEmptyState(msg = "Belum ada pasien di rentang ini.") {
            $('#chart-container').removeClass('d-block block').addClass('d-none hidden');
            $('#no-data-message').removeClass('d-none hidden').addClass('d-flex flex');

            const emptyRow = `
                <tr class="hover:bg-slate-50 transition">
                    <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-inbox mr-2 text-slate-300"></i>
                        Belum ada data efektivitas saluran.
                    </td>
                </tr>
            `;
            const mEmptyCard = `
                <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                    <i class="fas fa-inbox mr-2 text-slate-300"></i>
                    Belum ada data.
                </div>
            `;
            $('#table-marketing-body').html(emptyRow);
            $('#mobile-marketing-container').html(mEmptyCard);
            if (myChart) {
                myChart.destroy();
                myChart = null;
            }
        }
        // --- CHART DATA ---
        function getSocialIcon(label) {
            label = label.toLowerCase();
            // --- BRAND ICONS ---
            if (label.includes('whatsapp')) return '<i class="fab fa-whatsapp text-[#25d366]"></i>';
            if (label.includes('instagram')) return '<i class="fab fa-instagram text-[#e1306c]"></i>';
            if (label.includes('tiktok')) return '<i class="fab fa-tiktok text-slate-900"></i>';
            if (label.includes('facebook')) return '<i class="fab fa-facebook text-[#1877f2]"></i>';
            if (label.includes('google maps')) return '<i class="fas fa-map-marker-alt text-amber-300"></i>'; 
            if (label.includes('google')) return '<i class="fab fa-google text-[#001a44]"></i>'; // Logo G Google Biru
            if (label.includes('teman') || label.includes('kerabat')) return '<i class="fas fa-user-friends text-purple-600"></i>'; // Ikon Dua Orang
            if (label.includes('media') || label.includes('sosial')) return '<i class="fas fa-share-alt text-indigo-400"></i>'; // Ikon Jaringan/Share
            return '<i class="fas fa-link text-slate-400"></i>';
        }

        function updateChart(labels, values) {
            const ctx = document.getElementById('marketingPieChart').getContext('2d');
            if (myChart) {
                myChart.destroy();
            }
            myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['#6777ef', '#ffa426', '#fc544b', '#63ed7a', '#34395e', '#47c363'],
                        borderWidth: 2
                    }]
                },
                options: {
                    animation: false,
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
}