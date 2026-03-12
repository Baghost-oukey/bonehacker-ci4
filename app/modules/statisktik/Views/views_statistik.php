<?= $this->extend('layout/layout') ?>
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
                        <h5>Statistik Rekam Medis</h5>
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
                                            <?php if ($value->id == $regions_patient[0]): ?>
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
                    <div class="card-body col-12">
                        <h5 class="card-title" id="chartTitle"></h5>
                        <label id="totalCount" style="margin-left:50px; font-weight: bold;"></label><br>
                        <label id="oldPatientsCount" style="margin-left:50px; color: #666;"></label><br>
                        <label id="newPatientsCount" style="margin-left:50px; margin-bottom: 20px; color: #666;"></label>
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

<script type="text/javascript">
    $(document).ready(function() {
        $('#region_id').select2({});

        var currentFilter = 'daily';
        moment.locale('id');

        // Initialize Daterangepicker
        $('#reportrange').daterangepicker({
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            cb(start, end);
        });

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            fetchStatistics(start, end, currentFilter);
        }

        // Default Load
        var defaultStart = moment().subtract(6, 'days');
        var defaultEnd = moment();
        cb(defaultStart, defaultEnd);

        $('#statisticFilter, #region_id').on('change', function() {
            currentFilter = $('#statisticFilter').val();
            var drp = $('#reportrange').data('daterangepicker');
            fetchStatistics(drp.startDate, drp.endDate, currentFilter);
        });

        function fetchStatistics(startDate, endDate, filter) {
            var regionId = $('#region_id').val();
            
            // Penyesuaian Yearly seperti di logika Anda sebelumnya
            var finalStart = (filter === 'yearly') ? moment(startDate).startOf('year') : startDate;
            var finalEnd = (filter === 'yearly') ? moment(endDate).endOf('year') : endDate;

            $.ajax({
                url: '<?= site_url('statistik/fetch_statistics') ?>',
                method: 'GET',
                data: {
                    start_date: finalStart.format('YYYY-MM-DD'),
                    end_date: finalEnd.format('YYYY-MM-DD'),
                    filter: filter,
                    region_id: regionId
                },
                dataType: 'json',
                success: function(data) {
                    var dateLabels = [];
                    var dateMap = {};
                    var totalCount = 0;
                    var oldPatientsCount = 0;
                    var newPatientsCount = 0;

                    // 1. Generate Empty Slots (Agar chart tetap rapi meski data kosong)
                    var iterDate = finalStart.clone();
                    if (filter === 'daily') {
                        while (iterDate.isSameOrBefore(finalEnd)) {
                            var key = iterDate.format('YYYY-MM-DD');
                            dateLabels.push(key);
                            dateMap[key] = 0;
                            iterDate.add(1, 'days');
                        }
                    } else if (filter === 'weekly') {
                        var tempStart = finalStart.clone().startOf('isoWeek');
                        while (tempStart.isSameOrBefore(finalEnd)) {
                            var weekKey = tempStart.format('YYYY-MM-DD') + ' - ' + tempStart.clone().endOf('isoWeek').format('YYYY-MM-DD');
                            dateLabels.push(weekKey);
                            dateMap[weekKey] = 0;
                            tempStart.add(1, 'weeks');
                        }
                    } else if (filter === 'monthly') {
                        while (iterDate.isSameOrBefore(finalEnd, 'month')) {
                            var key = iterDate.format('YYYY-MM');
                            dateLabels.push(key);
                            dateMap[key] = 0;
                            iterDate.add(1, 'month');
                        }
                    } else if (filter === 'yearly') {
                        while (iterDate.isSameOrBefore(finalEnd, 'year')) {
                            var key = iterDate.format('YYYY');
                            dateLabels.push(key);
                            dateMap[key] = 0;
                            iterDate.add(1, 'year');
                        }
                    }

                    // 2. Map Data from Backend
                    data.forEach(function(item) {
                        var key = item.date;
                        
                        // Handle formatting untuk Weekly agar match dengan map label
                        if (filter === 'weekly') {
                            var parts = item.date.split('-');
                            var mWeek = moment().year(parts[0]).isoWeek(parts[1]);
                            key = mWeek.startOf('isoWeek').format('YYYY-MM-DD') + ' - ' + mWeek.endOf('isoWeek').format('YYYY-MM-DD');
                        }

                        if (dateMap.hasOwnProperty(key)) {
                            var val = parseInt(item.total);
                            dateMap[key] += val;
                        }
                        
                        totalCount += parseInt(item.total);
                        oldPatientsCount += parseInt(item.oldPatientsCount);
                        newPatientsCount += parseInt(item.newPatientsCount);
                    });

                    // 3. Update UI Labels
                    $('#totalCount').text('Total Rekam Medis: ' + totalCount);
                    $('#oldPatientsCount').text('Jumlah Pasien Lama: ' + oldPatientsCount);
                    $('#newPatientsCount').text('Jumlah Pasien Baru: ' + newPatientsCount);

                    // 4. Render Chart
                    renderChart(dateLabels, dateMap, filter);
                }
            });
        }

        function renderChart(labels, dateMap, filter) {
            var ctx = document.getElementById('statisticChart').getContext('2d');
            var displayLabels = labels.map(function(label) {
                if (filter === 'daily') return moment(label).format('D MMM');
                if (filter === 'monthly') return moment(label, 'YYYY-MM').format('MMM YYYY');
                if (filter === 'weekly') {
                    var parts = label.split(' - ');
                    return moment(parts[0]).format('D MMM') + ' - ' + moment(parts[1]).format('D MMM');
                }
                return label;
            });

            var chartExists = Chart.getChart('statisticChart');
            if (chartExists) chartExists.destroy();

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: displayLabels,
                    datasets: [{
                        label: 'Jumlah Rekam Medis',
                        data: labels.map(l => dateMap[l]),
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>