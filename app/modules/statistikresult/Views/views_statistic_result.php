<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= esc($title) ?></h1>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 id="dynamicTitle">Statistik Hasil Pemeriksaan</h5>
                        </div>
                        <div>
                            <div id="rangefilter" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                            <div>
                                <select id="selectfilter" style="width: 150px; padding: 5px; margin-top: 5px; cursor: pointer">
                                    <option value="daily">Hari</option>
                                    <option value="monthly">Bulan</option>
                                    <option value="yearly">Tahun</option>
                                </select>
                                <select id="regionSelect" style="width: 150px; padding: 5px; margin-top: 5px; cursor: pointer">
                                    <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php if (in_array($value->id, (array)$regions_patient)): ?>
                                                <option value="<?= $value->id ?>" selected><?= esc($value->name) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <option value="<?= $value->id ?>" <?= (isset($selected_region) && $selected_region == $value->id) ? 'selected' : '' ?>>
                                                <?= esc($value->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chartContainer" style="height: auto; margin: auto">
                            <h5 class="card-title" id="heading"></h5>
                            <table id="statisticTable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th style="width:90%;">Nama Tag</th>
                                        <th style="width:10%;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
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
    $(document).ready(function() {
        $('#regionSelect').select2({
            placeholder: "Pilih Wilayah",
            allowClear: true
        });

        if ($('#statisticTable').length) {
            var currentFilter = 'daily';
            var previousStartDate = moment().subtract(6, 'days');
            var previousEndDate = moment();

            // Inisialisasi DataTable
            var table = $('#statisticTable').DataTable({
                "paging": true,
                "pageLength": 10,
                "lengthChange": false,
                "ordering": true,
                "info": true,
                "searching": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });

            // DateRangePicker Setup
            $('#rangefilter').daterangepicker({
                locale: {
                    format: 'D MMMM YYYY'
                },
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
                $('#rangefilter span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                previousStartDate = start;
                previousEndDate = end;
                updateHeading(start, end, currentFilter);
                fetchStatistics(start, end, currentFilter);
            }

            // Inisialisasi awal
            cb(previousStartDate, previousEndDate);

            $('#selectfilter').on('change', function() {
                currentFilter = $(this).val();
                let start, end;

                if (currentFilter === 'daily') {
                    start = previousStartDate;
                    end = previousEndDate;
                } else if (currentFilter === 'monthly') {
                    start = moment(previousStartDate).startOf('month');
                    end = moment(previousEndDate).endOf('month');
                } else if (currentFilter === 'yearly') {
                    start = moment(previousStartDate).startOf('year');
                    end = moment(previousEndDate).endOf('year');
                }

                $('#rangefilter').data('daterangepicker').setStartDate(start);
                $('#rangefilter').data('daterangepicker').setEndDate(end);
                cb(start, end);
            });

            $('#regionSelect').on('change', function() {
                fetchStatistics(previousStartDate, previousEndDate, currentFilter);
            });

            function updateHeading(startDate, endDate, filter) {
                let headingText = "";
                if (filter === 'daily') {
                    headingText = startDate.isSame(endDate, 'day') ? startDate.format('D MMM YYYY') : startDate.format('D MMM YYYY') + ' - ' + endDate.format('D MMM YYYY');
                } else if (filter === 'monthly') {
                    headingText = startDate.isSame(endDate, 'month') ? startDate.format('MMMM YYYY') : startDate.format('MMMM YYYY') + ' - ' + endDate.format('MMMM YYYY');
                } else if (filter === 'yearly') {
                    headingText = startDate.isSame(endDate, 'year') ? startDate.format('YYYY') : startDate.format('YYYY') + ' - ' + endDate.format('YYYY');
                }
                $('#heading').text(headingText);
            }

            function fetchStatistics(startDate, endDate, filter) {
                var region = $('#regionSelect').val();
                $.ajax({
                    url: '<?= base_url('statistikresult/fetch_statistics') ?>',
                    method: 'GET',
                    data: {
                        start_date: startDate.format('YYYY-MM-DD'),
                        end_date: endDate.format('YYYY-MM-DD'),
                        filter: filter,
                        region_id: region
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Opsi: Tambahkan loader jika tabel sangat besar
                    },
                    success: function(data) {
                        table.clear();
                        var rows = [];
                        Object.keys(data).forEach(function(tagName) {
                            let total = data[tagName].total || 0;
                            if (total > 0) { // Hanya tampilkan yang ada datanya
                                rows.push([tagName, total]);
                            }
                        });
                        table.rows.add(rows).draw();
                    },
                    error: function(xhr) {
                        console.error("Error fetch statistics:", xhr.responseText);
                    }
                });
            }
        }
    });
</script>
<?= $this->endSection() ?>