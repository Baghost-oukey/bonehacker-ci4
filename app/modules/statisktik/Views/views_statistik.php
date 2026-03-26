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
                            <div class="d-flex align-item-center">
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

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#region_id').select2({
            width: '100%'
        });

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
            let finalStart = startDate.clone();
            let finalEnd = endDate.clone();

            if (filter === 'yearly') {
                finalStart.startOf('year');
                finalEnd.endOf('year');
            } else if (filter === 'monthly') {
                finalStart.startOf('month');
                finalEnd.endOf('month');
            }

            // if (filter === 'yearly') {
            //     // Ubah startDate menjadi awal tahun dan endDate menjadi akhir tahun
            //     startDate = moment(startDate).startOf('year'); // Awal tahun dari startDate
            //     endDate = moment(endDate).endOf('year'); // Akhir tahun dari endDate
            // }

            $.ajax({
                url: '<?= site_url('statistik/fetch_statistics') ?>',
                method: 'GET',
                data: {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    filter: filter,
                    region_id: regionId
                },

                dataType: 'json',
                success: function(data) {
                    console.log('Data yang diterima untuk weekly:', data);

                    var dateLabels = [];
                    var dateMap = {};
                    var totalCount = 0;
                    var oldPatientsCount = 0;
                    var newPatientsCount = 0;
                    var currentDate = moment(startDate);

                    // Populate date labels based on filter
                    if (filter === 'daily') {
                        while (currentDate.isSameOrBefore(endDate)) {
                            var formattedDate = currentDate.format('YYYY-MM-DD');
                            dateLabels.push(formattedDate);
                            dateMap[formattedDate] = 0;
                            currentDate.add(1, 'days');
                        }
                    } else if (filter === 'weekly') {
                        var tempStartDate = startDate.clone().startOf('isoWeek');
                        var tempEndDate = endDate.clone().endOf('isoWeek');
                        while (tempStartDate.isSameOrBefore(tempEndDate)) {
                            var weekStart = tempStartDate.clone().format('YYYY-MM-DD');
                            var weekEnd = tempStartDate.clone().add(6, 'days').format('YYYY-MM-DD');
                            var weekKey = weekStart + ' - ' + weekEnd;
                            if (!dateMap.hasOwnProperty(weekKey)) {
                                dateLabels.push(weekKey);
                                dateMap[weekKey] = 0;
                            }
                            tempStartDate.add(7, 'days');
                        }
                    } else if (filter === 'monthly') {
                        var tempDate = startDate.clone().startOf('month');
                        var endOfMonth = endDate.clone().endOf('month');
                        while (tempDate.isSameOrBefore(endOfMonth)) {
                            var formattedMonth = tempDate.format('YYYY-MM');
                            if (!dateLabels.includes(formattedMonth)) {
                                dateLabels.push(formattedMonth);
                                dateMap[formattedMonth] = 0;
                            }
                            tempDate.add(1, 'month').startOf('month');
                        }
                    } else if (filter === 'yearly') {
                        var tempDate = startDate.clone().startOf(
                            'year'); // Awal tahun dari startDate
                        var endOfYear = endDate.clone().endOf('year'); // Akhir tahun dari endDate

                        // Loop through each year from startDate to endDate
                        while (tempDate.isSameOrBefore(endOfYear)) {
                            var formattedYear = tempDate.format('YYYY');
                            if (!dateLabels.includes(formattedYear)) {
                                dateLabels.push(formattedYear);
                                dateMap[formattedYear] = 0;
                            }
                            tempDate.add(1, 'year'); // Tambah 1 tahun
                        }
                    }

                    // Process fetched data and map to the appropriate date ranges
                    data.forEach(function(item) {
                        console.log('Data yang diterima:', item);
                        var itemDate = moment(item.date, filter === 'weekly' ? 'YYYY-0WWW' :
                            'YYYY-MM-DD');
                        var key = item.date;

                        oldPatientsCount += parseInt(item.oldPatientsCount);
                        newPatientsCount += parseInt(item.newPatientsCount);

                        if (filter === 'daily') {
                            key = itemDate.format('YYYY-MM-DD');
                        } else if (filter === 'weekly') {
                            var weekStart = moment(item.date, 'YYYY-WW').startOf('isoWeek')
                                .format('YYYY-MM-DD');
                            var weekEnd = moment(item.date, 'YYYY-WW').endOf('isoWeek')
                                .format('YYYY-MM-DD');
                            key = weekStart + ' - ' + weekEnd;
                        } else if (filter === 'monthly') {
                            key = itemDate.format('YYYY-MM');
                        } else if (filter === 'yearly') {
                            key = itemDate.format('YYYY');
                        }

                        if (key && dateMap.hasOwnProperty(key)) {
                            var roundedValue = Math.round(parseInt(item.total));
                            dateMap[key] += roundedValue; // Add the data to the dateMap
                            totalCount += roundedValue; // Increment the total count


                        }
                    });

                    // Display total count
                    $('#totalCount').text('Total Rekam Medis: ' + totalCount);
                    // Display old and new patients count
                    $('#oldPatientsCount').text('Jumlah Pasien Lama: ' + oldPatientsCount);
                    $('#newPatientsCount').text('Jumlah Pasien Baru: ' + newPatientsCount);

                    // Prepare labels and values for the chart
                    var labels = dateLabels.map(function(date) {
                        if (filter === 'daily') {
                            return moment(date).format('D MMM');
                        } else if (filter === 'weekly') {
                            var dates = date.split(' - ');
                            var start = moment(dates[0]).format('D MMM');
                            var end = moment(dates[1]).format('D MMM');
                            return start + ' - ' + end;
                        } else if (filter === 'monthly') {
                            return moment(date, 'YYYY-MM').format('MMM YYYY');
                        } else if (filter === 'yearly') {
                            return date;
                        }
                    });

                    var values = dateLabels.map(function(date) {
                        return dateMap[date];
                    });

                    // Create or update the chart
                    var ctx = document.getElementById('statisticChart').getContext('2d');
                    var chartExists = Chart.getChart('statisticChart');
                    if (chartExists) {
                        chartExists.destroy();
                    }

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Jumlah Rekam Medis',
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
                                    display: false,
                                    position: 'top'
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Failed to fetch data:', error);
                }
            });
        }

    });
</script>
<?= $this->endSection() ?>