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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Statistik Jenis Kelamin</h5>
                        <div>
                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                            <div>
                                <select id="statisticFilter" style="width : 150px; padding : 5px; margin-top : 5px; cursor: pointer">
                                    <option value="daily">Hari</option>
                                    <option value="weekly">Minggu</option>
                                    <option value="monthly">Bulan</option>
                                    <option value="yearly">Tahun</option>
                                </select>
                                <select id="region_id" style="width: 150px; padding: 5px; margin-top: 5px; cursor: pointer">
                                    <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php if (in_array($value->id, (array)$regions_patient)): ?>
                                                <option value="<?= $value->id ?>" selected><?= $value->name ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <option value="<?= $value->id ?>"><?= $value->name ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title" id="chartTitle"></h5>
                        <div id="chartContainer" style="height: 450px; margin: auto">
                            <canvas id="statisticChart"></canvas>
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
        $('#region_id').select2();
        var currentFilter = 'daily';
        moment.locale('id');

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end, currentFilter);
        }

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
        }, cb);

        cb(moment().subtract(6, 'days'), moment());

        $('#statisticFilter, #region_id').on('change', function() {
            currentFilter = $('#statisticFilter').val();
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate, currentFilter);
        });

        function fetchStatistics(startDate, endDate, filter) {
            $.ajax({
                url: '<?= base_url('statistikgender/fetch_statistics') ?>',
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    filter: filter,
                    region_id: $('#region_id').val()
                },
                dataType: 'json',
                success: function(response) {
                    renderChart(response);
                }
            });
        }

        function renderChart(data) {
            var labels = data.map(item => item.date);
            var male = data.map(item => parseInt(item.total_male));
            var female = data.map(item => parseInt(item.total_female));

            var ctx = document.getElementById('statisticChart').getContext('2d');
            if (window.myChart instanceof Chart) window.myChart.destroy();

            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Laki-laki',
                            data: male,
                            backgroundColor: 'rgba(0, 123, 255, 0.5)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Perempuan',
                            data: female,
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                generateLabels: function(chart) {
                                    const original = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                    original[0].text = 'Laki-laki: ' + male.reduce((a, b) => a + b, 0);
                                    original[1].text = 'Perempuan: ' + female.reduce((a, b) => a + b, 0);
                                    return original;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>