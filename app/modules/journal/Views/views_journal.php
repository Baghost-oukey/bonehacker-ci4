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
                    <div class="card-header">
                        <h4>Data Journal</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label>Filter Date:</label>
                                <div class="input-group">
                                    <input type="date" id="start_date" class="form-control">
                                    <div class="input-group-append">
                                        <span class="input-group-text">~</span>
                                    </div>
                                    <input type="date" id="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Wilayah:</label>
                                <select id="region" class="form-control select2">
                                    <option value="">Semua Wilayah</option>
                                    <?php foreach ($wilayah as $value): ?>
                                        <option value="<?= $value->id ?>"><?= esc($value->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="table-journal" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Status</th>
                                        <th>Alamat</th>
                                        <th>Hasil Pemeriksaan</th>
                                        <th>Tindakan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
        $('.select2').select2();
        var table = $("#table-journal").DataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                "url": "<?= site_url('journal/fetch') ?>",
                "type": "POST",
                "data": function(d) {
                    d.region = $('#region').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            "dom": '<"top"lBf>rt<"bottom"ip><"clear">',
            "buttons": [{
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm mr-2',
                    action: function(e, dt, button, config) {
                        var params = $.param({
                            region: $('#region').val(), 
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val()
                        });
                        window.open(url, '_blank');
                    }
                },
                {
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    action: function(e, dt, button, config) {
                        var params = $.param({
                            region: $('#region').val(), 
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val()
                        });
                        window.location.href = url;
                    }
                }
            ],
            "columns": [{
                    "data": "no",
                    "width": "5%",
                    "sortable": false
                },
                {
                    "data": "tanggal",
                    "width": "15%"
                },
                {
                    "data": "nama",
                    "width": "20%"
                },
                {
                    "data": "status",
                    "width": "10%"
                },
                {
                    "data": "alamat",
                    "width": "15%"
                },
                {
                    "data": "result_names",
                    "width": "15%"
                },
                {
                    "data": "measures",
                    "width": "15%"
                },
                {
                    "data": "action",
                    "width": "5%",
                    "sortable": false
                }
            ]
        });

        // Reload tabel otomatis saat filter diubah
        $('#region, #start_date, #end_date').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
<?= $this->endSection() ?>