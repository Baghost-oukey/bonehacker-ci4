<?= $this->extend('layout\layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h5>Statistik Daerah</h5>
                        <div class="flex-grow-1 d-flex flex-column align-items-end">
                            <div id="reportrange" class="w-90 d-md-inline-block"
                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>

                            <div class="mt-2 d-flex flex-wrap gap-2 justify-content-end">
                                <select id="kabupaten_id" class="form-control select2 w-auto" style="cursor: pointer;">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>

                                <select id="kecamatan_id" class="form-control select2 w-auto" style="cursor: pointer;" disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>

                                <select id="desa_id" class="form-control select2 w-auto" style="cursor: pointer;" disabled>
                                    <option value="">Pilih Desa/Kelurahan</option>
                                </select>
                            </div>

                            <div class="mt-2 d-flex flex-wrap gap-2 justify-content-end">
                                <select id="statisticFilter" class="form-control w-auto" style="cursor: pointer;">
                                    <option value="daily">Hari</option>
                                    <option value="weekly">Minggu</option>
                                    <option value="monthly">Bulan</option>
                                    <option value="yearly">Tahun</option>
                                </select>

                                <select id="region_id" class="form-control w-auto" style="cursor: pointer;">
                                    <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php if (in_array($value->id, (array)$regions_patient)): ?>
                                                <option value="<?= $value->id ?>" selected><?= $value->name ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <option value="<?= $value->id ?>" <?= (isset($region) && $region == $value->id) ? 'selected' : '' ?>>
                                                <?= $value->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title" id="chartTitle"></h5>
                        <div id="chartContainer" style="height: 450px; margin: auto">
                            <canvas id="statisticChart" style="width: 100%; height: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();
        $('#region_id').select2();

        var currentFilter = 'daily';
        moment.locale('id');

        // Date Range Picker Initialization
        $('#reportrange').daterangepicker({
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end, currentFilter);
        });

        // Set Initial Date Display
        var initialStart = moment().subtract(6, 'days');
        var initialEnd = moment();
        $('#reportrange span').html(initialStart.format('D MMMM YYYY') + ' - ' + initialEnd.format('D MMMM YYYY'));

        // Load Initial Data
        fetchKabupaten();
        fetchStatistics(initialStart, initialEnd, currentFilter);

        // --- Event Listeners ---

        $('#statisticFilter, #region_id').on('change', function() {
            currentFilter = $('#statisticFilter').val();
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate, currentFilter);
        });

        $('#kabupaten_id').on('change', function() {
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

        $('#kecamatan_id').on('change', function() {
            var kecId = $(this).val();
            if (kecId) {
                fetchDesa(kecId);
                $('#desa_id').prop('disabled', false);
            } else {
                $('#desa_id').prop('disabled', true).html('<option value="">Pilih Desa/Kelurahan</option>');
            }
            triggerReload();
        });

        $('#desa_id').on('change', function() {
            triggerReload();
        });

        function triggerReload() {
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate, currentFilter);
        }

        // --- AJAX Functions ---

        function fetchKabupaten() {
            $.ajax({
                url: '<?= base_url('statistikdaerah/fetch_kabupaten') ?>',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Pilih Kabupaten/Kota</option>';
                    response.forEach(function(row) {
                        options += `<option value="${row.kabupaten_id}">${row.kabupaten_nama}</option>`;
                    });
                    $('#kabupaten_id').html(options);
                }
            });
        }

        function fetchKecamatan(kabId) {
            $.ajax({
                url: '<?= base_url('statistikdaerah/fetch_kecamatan') ?>',
                method: 'GET',
                data: {
                    kabupaten_id: kabId
                },
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Pilih Kecamatan</option>';
                    response.forEach(function(row) {
                        options += `<option value="${row.kecamatan_id}">${row.kecamatan_nama}</option>`;
                    });
                    $('#kecamatan_id').html(options);
                }
            });
        }

        function fetchDesa(kecId) {
            $.ajax({
                url: '<?= base_url('statistikdaerah/fetch_desa') ?>',
                method: 'GET',
                data: {
                    kecamatan_id: kecId
                },
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Pilih Desa/Kelurahan</option>';
                    response.forEach(function(row) {
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
                url: '<?= base_url('statistikdaerah/fetch_statistics') ?>',
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
                success: function(response) {
                    var labels = [];
                    var chartData = {};
                    var iter = finalStart.clone();
                    while (iter.isSameOrBefore(finalEnd, (filter === 'daily' ? 'day' : (filter === 'monthly' ? 'month' : 'year')))) {
                        var key = (filter === 'daily' ? iter.format('YYYY-MM-DD') : (filter === 'monthly' ? iter.format('YYYY-MM') : iter.format('YYYY')));
                        labels.push(key);
                        chartData[key] = 0; // Set awal 0
                        iter.add(1, (filter === 'daily' ? 'days' : (filter === 'monthly' ? 'months' : 'years')));
                    }
                    if (Array.isArray(response)) {
                        response.forEach(function(item) {
                            if (chartData.hasOwnProperty(item.date)) {
                                chartData[item.date] = parseInt(item.total);
                            }
                        });
                    }
                    var finalValues = labels.map(l => chartData[l]);

                    renderChart(labels, finalValues, filter);
                    // renderChart(response, startDate, endDate, filter);
                }
            });
        }

        function renderChart(labels, values, filter) {
            var ctx = document.getElementById('statisticChart').getContext('2d');

            // Format label sumbu X agar lebih cantik
            var displayLabels = labels.map(function(l) {
                if (filter === 'daily') return moment(l).format('D MMM');
                if (filter === 'monthly') return moment(l, 'YYYY-MM').format('MMM YYYY');
                return l;
            });

            if (window.myChart instanceof Chart) window.myChart.destroy();

            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: displayLabels,
                    datasets: [{
                        label: "Jumlah Rekam Medis",
                        data: values,
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                generateLabels: function(chart) {
                                    const total = values.reduce((a, b) => a + b,
                                        0);
                                    return Chart.defaults.plugins.legend.labels
                                        .generateLabels(chart).map((label,
                                            index) => {
                                            label.text =
                                                `${"Jumlah Rekam Medis"} : ${total}`;
                                            return label;
                                        });
                                }
                            },
                            onHover: function(event, legendItem) {
                                const canvas = event.chart.canvas;
                                canvas.style.cursor = 'pointer';
                            },
                            onLeave: function(event, legendItem) {
                                const canvas = event.chart.canvas;
                                canvas.style.cursor = 'default';
                            }
                        }
                    }
                },
            });
        }
    });
</script>
<?= $this->endSection() ?>