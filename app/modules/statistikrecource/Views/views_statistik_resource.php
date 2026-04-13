<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<style>
    :root {
        --primary-color: #6777ef;
        --dark-bg: #34395e;
    }

    .card-analysis {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.2s;
    }



    .table-modern thead th {
        background: #f8f9fa;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        color: #98a6ad;
        border: none;
    }

    .badge-percent {
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 30px;
        background: rgba(103, 119, 239, 0.1);
        color: var(--primary-color);
    }

    /* Ikon Sosmed Colors */
    .icon-instagram {
        color: #e1306c;
    }

    .icon-tiktok {
        color: #000000;
    }

    .icon-whatsapp {
        color: #25d366;
    }

    .icon-facebook {
        color: #4267B2;
    }

    /* Container utama pesan kosong */
    #no-data-message {
        height: 300px;
        /* Samakan dengan tinggi chart-container Anda */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-state {
        text-align: center;
    }

    .empty-state img {
        width: 100px;
        height: auto;
        opacity: 0.3;
        /* Sedikit lebih jelas dibanding 0.25 agar tetap terlihat profesional */
        display: block;
        margin: 0 auto 15px;
        /* Tengahkan gambar dan beri jarak ke teks */
    }

    .empty-state p {
        color: #98a6ad;
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }
</style>

<section class="section">
    <div class="section-header shadow-sm mb-4">
        <h1><?= $title ?></h1>
    </div>

    <div class="section-body">
        <div class="card card-analysis mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Rentang Tanggal Analysis</label>
                            <div id="reportrange" class="form-control d-flex align-items-center justify-content-between" style="cursor: pointer;">
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Lokasi Cabang</label>
                            <select class="form-control select2" id="filter_region">
                                <option value="">Semua Wilayah</option>
                                <?php foreach ($wilayah as $w): ?>
                                    <option value="<?= $w->id ?>"><?= $w->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-lg btn-block shadow-primary" onclick="loadMarketingData()">
                            <i class="fas fa-sync-alt mr-2"></i> Update Analisis
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card card-analysis h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h4 class="m-0">Proporsi Saluran</h4>
                    </div>
                    <div class="card-body">
                        <div id="chart-container" style="height: 300px;">
                            <canvas id="marketingPieChart"></canvas>
                        </div>
                        <div id="no-data-message" class="d-none">
                            <div class="empty-state">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="No Data">
                                <p>Data Masih Kosong</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-analysis h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h4 class="m-0">Ranking Efektivitas Saluran</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-4">Saluran / Media</th>
                                        <th class="text-center">Total Pasien</th>
                                        <th class="text-center">Kontribusi</th>
                                    </tr>
                                </thead>
                                <tbody id="table-marketing-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // 1. Variabel Global
    let myChart = null;

    $(document).ready(function() {
        moment.locale('id');
        $('#filter_region').select2();

        // Inisialisasi Tanggal Awal (Bulan Ini)
        let start = moment().startOf('month');
        let end = moment().endOf('month');

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
        }

        // Konfigurasi DateRangePicker
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
        }, function(start, end) {
            // Dipicu saat klik 'Pilih' atau klik menu Range
            cb(start, end);
            loadMarketingData(start, end);
        });

        // Set label pertama kali
        cb(start, end);
        // Load data pertama kali
        loadMarketingData(start, end);
    });

    // 2. Fungsi Load Data
    function loadMarketingData(s = null, e = null) {
        let startDate, endDate;
        let drp = $('#reportrange').data('daterangepicker');

        // Pastikan ambil dari picker jika parameter s/e tidak ada
        startDate = (s) ? s.format('YYYY-MM-DD') : drp.startDate.format('YYYY-MM-DD');
        endDate = (e) ? e.format('YYYY-MM-DD') : drp.endDate.format('YYYY-MM-DD');

        $.ajax({
            url: "<?= base_url('statistikresource/get_marketing_data') ?>",
            type: "GET",
            data: {
                start_date: startDate,
                end_date: endDate,
                region_id: $('#filter_region').val()
            },
            dataType: "JSON",
            success: function(response) {
                if (response.status == 'success') {
                    renderContent(response);
                }
            }
        });
    }

    function renderContent(response) {
        let labels = [],
            values = [],
            tableHtml = '';
        let totalAll = response.total_all_patients || 0;

        if (response.details && response.details.length > 0 && totalAll > 0) {
            $('#chart-container').removeClass('d-none');
            $('#no-data-message').addClass('d-none');

            response.details.forEach(item => {
                let label = item.saluran || 'Lainnya';
                let percent = totalAll > 0 ? ((item.total_pasien / totalAll) * 100).toFixed(1) : 0;

                labels.push(label);
                values.push(item.total_pasien);

                tableHtml += `
                    <tr>
                        <td class="px-4 font-weight-bold text-dark small">
                            ${getSocialIcon(label)} ${label.toUpperCase()}
                        </td>
                        <td class="text-center small">${item.total_pasien} Pasien</td>
                        <td class="text-center small">
                            <span class="badge-percent">${percent}%</span>
                        </td>
                    </tr>`;
            });

            $('#table-marketing-body').html(tableHtml);
            updateChart(labels, values);
        } else {
            $('#chart-container').addClass('d-none');
            $('#no-data-message').removeClass('d-none').addClass('d-flex');
            $('#table-marketing-body').html('<tr><td colspan="3" class="text-center py-4 small">Belum ada data</td></tr>');

            if (myChart) {
                myChart.destroy();
                myChart = null;
            }
        }
    }

    // 4. Fungsi Ikon
    function getSocialIcon(label) {
        label = label.toLowerCase();
        if (label.includes('instagram')) return '<i class="fab fa-instagram icon-instagram mr-1"></i>';
        if (label.includes('tiktok')) return '<i class="fab fa-tiktok icon-tiktok mr-1"></i>';
        if (label.includes('whatsapp')) return '<i class="fab fa-whatsapp icon-whatsapp mr-1"></i>';
        if (label.includes('facebook')) return '<i class="fab fa-facebook icon-facebook mr-1"></i>';
        return '<i class="fas fa-link mr-1 text-muted"></i>';
    }

    // 5. Fungsi Update Chart (Ringan & Tanpa Animasi)
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
                animation: false, // MATIKAN ANIMASI agar ringan
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
</script>
<?= $this->endSection() ?>