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
                            <h5 id="dynamicTitle">Statistik Keluhan</h5>
                        </div>
                        <div>
                            <div id="rangefilter" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                            <div>
                                <select id="selecttag" style="width: 150px; padding: 5px; margin-top: 5px; cursor: pointer">
                                    <option value="complaint">Keluhan</option>
                                    <option value="medhis">Riwayat Medis</option>
                                </select>
                                <select id="selectfilter" style="width: 150px; padding: 5px; margin-top: 5px; cursor: pointer">
                                    <option value="daily">Hari</option>
                                    <option value="monthly">Bulan</option>
                                    <option value="yearly">Tahun</option>
                                </select>
                                <select id="regionSelect" name="regionSelect" style="width: 150px; padding: 5px; margin-top: 5px; cursor: pointer">
                                    <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                                        <?php foreach ($wilayah as $region): ?>
                                            <?php if ($region->id == $regions_patient[0]): ?>
                                                <option value="<?= $region->id ?>" selected><?= esc($region->name) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $region): ?>
                                            <option value="<?= $region->id ?>" <?= (isset($selected_region) && $selected_region == $region->id) ? 'selected' : '' ?>>
                                                <?= esc($region->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body col-12">
                        <div id="chartContainer" style="height: auto; margin: auto">
                            <h5 class="card-title" id="heading"></h5>
                            <div class="table-responsive">
                                <table id="statisticTable" class="table table-bordered table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:80%;">Nama Tag</th>
                                            <th style="width:20%;">Jumlah</th>
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
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#regionSelect').select2({
            width: '150px'
        });

        if ($('#statisticTable').length) {
            var currentFilter = 'daily';
            var currentTag = 'complaint';
            var previousStartDate = moment().subtract(6, 'days');
            var previousEndDate = moment();

            // Inisialisasi DataTable CI4
            var table = $('#statisticTable').DataTable({
                "paging": true,
                "pageLength": 10,
                "lengthChange": false,
                "ordering": true,
                "info": true,
                "searching": true,
                "destroy": true // Mengizinkan re-inisialisasi
            });

            // Inisialisasi Daterangepicker
            $('#rangefilter').daterangepicker({
                startDate: previousStartDate,
                endDate: previousEndDate,
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
                $('#rangefilter span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                previousStartDate = start;
                previousEndDate = end;
                updateHeading(start, end, currentFilter);
                fetchStatistics(start, end, currentFilter, currentTag);
            });

            // Set tampilan awal
            $('#rangefilter span').html(previousStartDate.format('D MMMM YYYY') + ' - ' + previousEndDate.format('D MMMM YYYY'));
            updateHeading(previousStartDate, previousEndDate, currentFilter);
            fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);

            // Listener Dropdown
            $('#selectfilter').on('change', function() {
                currentFilter = $(this).val();
                fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);
            });

            $('#selecttag').on('change', function() {
                currentTag = $(this).val();
                $('#dynamicTitle').text(currentTag === 'complaint' ? 'Statistik Keluhan' : 'Statistik Riwayat Medis');
                fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);
            });

            $('#regionSelect').on('change', function() {
                fetchStatistics(previousStartDate, previousEndDate, currentFilter, currentTag);
            });

            function updateHeading(startDate, endDate, filter) {
                moment.locale('id');
                var headingText = startDate.format('D MMM YYYY') + (startDate.isSame(endDate, 'day') ? '' : ' - ' + endDate.format('D MMM YYYY'));
                $('#heading').text(headingText);
            }

            function fetchStatistics(startDate, endDate, filter, tag) {
                var region = $('#regionSelect').val();
                $.ajax({
                    url: '<?= base_url('statistiktag/fetch_statistics') ?>',
                    method: 'GET',
                    data: {
                        start_date: startDate.format('YYYY-MM-DD'),
                        end_date: endDate.format('YYYY-MM-DD'),
                        filter: filter,
                        tag: tag,
                        region_id: region
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Opsi: Tambahkan loading spinner
                    },
                    success: function(data) {
                        table.clear();
                        var rows = [];

                        if (data && typeof data === 'object') {
                            Object.keys(data).forEach(function(tagName) {
                                // Ambil total dari nested object: { "NamaTag": { "total": 5, ... } }
                                let total = data[tagName].total || 0;

                                if (total > 0) { // Hanya masukkan jika jumlahnya lebih dari 0
                                    rows.push([tagName, total]);
                                }
                            });
                        }

                        if (rows.length > 0) {
                            table.rows.add(rows).draw();
                        } else {
                            console.warn("Data diterima tapi tidak ada tag dengan jumlah > 0:", data);
                            table.draw();
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching data:", xhr.responseText);
                    }
                });
            }
        }
    });
</script>
<?= $this->endSection() ?>