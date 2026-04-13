<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<style>
    :root {
        --primary-dark: #34395e;
        --accent-color: #6777ef;
        --bg-light: #f4f6f9;
    }

    /* Card Styling */
    .card-analysis {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
        background: #fff;
        margin-bottom: 25px;
    }

    .stat-label {
        text-transform: uppercase;
        font-weight: 800;
        font-size: 10px;
        color: #adb5bd;
        letter-spacing: 1.2px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--primary-dark);
    }

    /* Container Chart agar tidak berantakan */
    .chart-wrapper {
        position: relative;
        height: 350px;
        width: 100%;
        overflow-x: auto;
        /* Memungkinkan scroll jika cabang terlalu banyak */
    }

    /* Modern Table */
    .table-modern thead th {
        background: #fdfdff;
        color: #6c757d;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.5px;
        padding: 15px;
        border-bottom: 2px solid #f1f1f1 !important;
    }

    .table-modern tbody td {
        padding: 15px;
        border-bottom: 1px solid #f9f9f9;
        font-weight: 500;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #98a6ad;
    }

    .badge-soft {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-soft-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-soft-info {
        background: #dbeafe;
        color: #1e40af;
    }
</style>

<section class="section">
    <div class="section-header shadow-sm mb-4">
        <h1 class="text-dark">Riwayat Pasien</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card card-analysis p-3">
                    <div class="stat-label">Total Volume Pasien</div>
                    <div class="stat-value" id="totalCount">0</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-analysis p-3 border-left-success">
                    <div class="stat-label text-success">Pasien Baru</div>
                    <div class="stat-value" id="newPatientsCount">0</div>
                    <div><span class="badge-soft badge-soft-success" id="percBaru">0%</span></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-analysis p-3">
                    <div class="stat-label text-info">Pasien Lama</div>
                    <div class="stat-value" id="oldPatientsCount">0</div>
                    <div><span class="badge-soft badge-soft-info" id="percLama">0%</span></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-analysis p-3">
                    <div class="stat-label text-warning">Rata - Rata Pasien PerHari</div>
                    <div class="stat-value" id="avgPerDay">0</div>
                    <small class="text-muted">pasien / hari</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-analysis">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                        <h4 class="m-0 font-weight-bold" style="font-size: 16px;">Data pasien per Cabang</h4>
                        <div id="reportrange" class="bg-light border-0 py-2 px-3 rounded-pill" style="cursor: pointer; font-size: 13px;">
                            <i class="fa fa-calendar text-primary mr-2"></i><span></span> <i class="fa fa-caret-down ml-2"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="statisticChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-analysis">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                        <h4 class="m-0 font-weight-bold" style="font-size: 16px;">Detil Jumlah Pasein Per Wilayah</h4>
                        <select id="region_id" class="form-control select2" style="width: 250px;">
                            <option value="">-- Pilih Wilayah --</option>
                            <?php foreach ($wilayah as $v): ?>
                                <option value="<?= $v->id ?>"><?= $v->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern text-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-left">Wilayah</th>
                                        <th>Rata²/Hari</th>
                                        <th>Total Pasien</th>
                                        <th>Pasien Lama</th>
                                        <th>Pasein Baru</th>
                                        <th>Pasien Sering Datang</th>
                                        <th>Pertumbuhan Pasien Baru</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-analysis">
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            Silakan pilih wilayah untuk memuat data.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- <div class="card-footer bg-light border-0 py-2 text-right">
                        <button class="btn btn-sm btn-success rounded-pill px-3"><i class="fas fa-file-excel mr-2"></i> Export Analysis</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#region_id').select2();
        moment.locale('id');

        const drpOptions = {
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Minggu Lalu': [moment().subtract(1, 'weeks').startOf('week'), moment().subtract(1, 'weeks').endOf('week')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],

            },
            // Default yang tampil saat halaman dibuka (misal Hari Ini)
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            linkedCalendars: false,
            showDropdowns: true, // Memudahkan pilih tahun/bulan di dalam kalender custom
            alwaysShowCalendars: true, // Custom range otomatis terlihat lewat kalender
            locale: {
                format: 'DD/MM/YYYY',
                separator: " - ",
                applyLabel: "Pilih",
                cancelLabel: "Batal",
                fromLabel: "Dari",
                toLabel: "Sampai",
                customRangeLabel: "Custom Range", // Ini otomatis jadi 'Custom Range' di paling bawah
                weekLabel: "M",
                daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
                monthNames: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
                firstDay: 1
            }
        };
        $('#reportrange').daterangepicker(drpOptions, cb);

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end);
        }

        // PENTING: Jalankan fungsi cb secara manual saat halaman pertama kali dibuka
        cb(drpOptions.startDate, drpOptions.endDate);

        // Hanya refresh jika filter berubah
        $('#region_id').on('change', function() {
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate);
        });

        function fetchStatistics(startDate, endDate) {
            var regionId = $('#region_id').val();
            let start = startDate.format('YYYY-MM-DD');
            let end = endDate.format('YYYY-MM-DD');

            $.ajax({
                url: '<?= site_url('statistik/fetch_analysis') ?>',
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    region_id: regionId
                },
                dataType: 'json',
                success: function(data) {
                    // 1. UPDATE STAT CARDS
                    $('#totalCount').text(data.summary.total.toLocaleString());
                    $('#newPatientsCount').text(data.summary.baru.toLocaleString());
                    $('#oldPatientsCount').text(data.summary.lama.toLocaleString());
                    $('#avgPerDay').text(data.summary.avg_per_day);

                    let pB = data.summary.total > 0 ? ((data.summary.baru / data.summary.total) * 100).toFixed(1) : 0;
                    let pL = data.summary.total > 0 ? ((data.summary.lama / data.summary.total) * 100).toFixed(1) : 0;
                    $('#percBaru').text(pB + '%  Pasein Baru');
                    $('#percLama').text(pL + '% Pasein Lama');

                    renderTable(data.details, regionId, startDate, endDate);

                    // 3. UPDATE CHART
                    renderChart(data.details);
                }
            });
        }

        function renderTable(details, selectedRegion, start, end) {
            let html = '';
            let diff = end.diff(start, 'days') + 1;

            // Saring data berdasarkan region yang dipilih
            let filteredData = details;
            if (selectedRegion && selectedRegion !== "") {
                filteredData = details.filter(item => item.id == selectedRegion);
            }

            // Jika tidak ada data sama sekali dari server
            if (!filteredData || filteredData.length === 0 || (filteredData.length === 1 && filteredData[0].total_pasien == 0)) {
                html = `<tr><td colspan="7" class="empty-state">
                    Tidak ada data ditemukan untuk periode/wilayah ini.
                </td></tr>`;
            } else {
                filteredData.forEach(i => {
                    if (i.total_pasien > 0) {
                        let pBaru = i.total_pasien > 0 ? ((i.pasien_baru / i.total_pasien) * 100).toFixed(1) : 0;
                        let pLama = i.total_pasien > 0 ? ((i.pasien_lama / i.total_pasien) * 100).toFixed(1) : 0;
                        let avg = (i.total_pasien / diff).toFixed(1);

                        html += `<tr>
                    <td class="text-left font-weight-bold text-dark">${i.cabang.toUpperCase()}</td>
                    <td><span class="badge badge-light px-3">${avg}</span></td>
                    <td>${i.total_pasien}</td>
                    <td class="text-muted">${i.pasien_lama}</td>
                    <td class="text-muted">${i.pasien_baru}</td>
                    <td class="text-info"><strong>${pLama}%</strong></td>
                    <td class="text-success"><strong>${pBaru}%</strong></td>
                </tr>`;
                    }
                });

                // Jika setelah difilter ternyata semua 0 (misal filter wilayah baru)
                if (html === '') {
                    html = `<tr><td colspan="7" class="empty-state">Wilayah ini tidak memiliki aktivitas pasien pada rentang waktu ini.</td></tr>`;
                }
            }
            $('#tbody-analysis').html(html);
        }

        function renderChart(details) {
            var labels = details.map(i => i.cabang);
            var values = details.map(i => i.total_pasien);

            var ctx = document.getElementById('statisticChart').getContext('2d');
            if (window.myChart) {
                window.myChart.destroy();
            }

            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Volume Pasien',
                        data: values,
                        backgroundColor: '#393E46',
                        hoverBackgroundColor: '#6777ef',
                        borderRadius: 10,
                        barThickness: 'flex',
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#34395e',
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f8f9fa',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#adb5bd'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#495057',
                                font: {
                                    size: 10,
                                    weight: '600'
                                },
                                maxRotation: 45, // Miringkan label agar tidak bertumpuk
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>