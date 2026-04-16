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
                                <?php if (session()->get('role') === 'user'): ?>
                                    <input type="text" class="form-control" value="<?= session()->get('region_name') ?>" readonly>
                                    <input type="hidden" id="region" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                                <?php else: ?>
                                    <select id="region" class="form-control select2">
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php $selected = (session()->get('active_region') == $value->id) ? 'selected' : ''; ?>
                                            <option value="<?= $value->id ?>" <?= $selected ?>><?= esc($value->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label class="d-block">&nbsp;</label>
                                <button type="button" id="btn-reset" class="btn btn-danger btn-block">
                                    <i class="fas fa-undo"></i> Reset Filter
                                </button>
                            </div>

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

<!-- Export Modal -->
<div class="modal fade" id="modalExportJournal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Unduh Data Pasien</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= site_url('journal/export_file_journal') ?>" method="GET" target="_blank">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Periode Laporan</label>
                        <select id="period_picker" class="form-control mb-2">
                            <option value="all">Seluruh Data</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="last_year">Tahun Lalu</option>
                            <option value="custom">Custom Range (Pilih Sendiri)</option>
                        </select>
                    </div>

                    <div class="form-group" id="custom_date_container" style="display: none;">
                        <label>Rentang Tanggal</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="start_date" id="exp_start_date" class="form-control">
                            </div>
                            <div class="col-6">
                                <input type="date" name="end_date" id="exp_end_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pilih Wilayah</label>
                        <?php if (session()->get('role') === 'user'): ?>
                            <input type="text" class="form-control" value="<?= session()->get('region_name') ?>" readonly>
                            <input type="hidden" name="region_id" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                        <?php else: ?>
                            <select name="region_id" id="export_region" class="form-control select2" style="width: 100%;">
                                <option value="">Semua Wilayah</option>
                                <?php foreach ($wilayah as $r): ?>
                                    <?php $selected = (session()->get('active_region') == $r->id) ? 'selected' : ''; ?>
                                    <option value="<?= $r->id ?>" <?= $selected ?>><?= esc($r->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Format Laporan</label>
                        <select name="format_type" class="form-control">
                            <option value="excel">Microsoft Excel (.xlsx)</option>
                            <option value="pdf">PDF Document (.pdf)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unduh Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
                    d.<?= csrf_token() ?> = $('input[name=<?= csrf_token() ?>]').val() || "<?= csrf_hash() ?>";
                },
                "dataSrc": function(json) {
                    if (json.new_token) {
                        CodeIgniter.csrfHash = json.new_token;
                        $('input[name=<?= csrf_token() ?>]').val(json.new_token);
                    }
                    return json.data;
                }
            },
            "dom": '<"top"lBf>rt<"bottom"ip><"clear">',
            "buttons": [{
                text: '<i class="fas fa-file-export"></i> Download Data Pasien',
                className: 'btn btn-primary btn-sm',
                action: function(e, dt, button, config) {
                    $('#export_region').val($('#region').val()).trigger('change');
                    $('#modalExportJournal').modal('show');
                }
            }, ],
            "columns": [{
                    "data": "no",
                    "width": "5%",
                    "sortable": false,
                    "searchable": false,
                },
                {
                    "data": "tanggal",
                    "width": "15%",
                    "searchable": true

                },
                {
                    "data": "nama",
                    "width": "20%",
                    "searchable": true
                },
                {
                    "data": "status",
                    "width": "10%",
                    "searchable": false
                },
                {
                    "data": "alamat",
                    "width": "15%",
                    "searchable": false
                },
                {
                    "data": "result_names",
                    "width": "15%",
                    "searchable": false
                },
                {
                    "data": "measures",
                    "width": "15%",
                    "searchable": true
                },
                {
                    "data": "action",
                    "width": "5%",
                    "sortable": false,
                    "searchable": false
                }
            ]
        });

        // Reload tabel otomatis saat filter diubah
        $('#region, #start_date, #end_date').on('change', function() {
            table.ajax.reload();
        });

        // Custom Range Tanggal 
        $('#period_picker').on('change', function() {
            const period = $(this).val();
            const today = new Date();
            let start = new Date();
            let end = new Date();

            // Logika perhitungan tanggal
            if (period === 'yesterday') {
                start.setDate(today.getDate() - 1);
                end.setDate(today.getDate() - 1);
            } else if (period === 'last_month') {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
            } else if (period === 'last_year') {
                start = new Date(today.getFullYear() - 1, 0, 1);
                end = new Date(today.getFullYear() - 1, 11, 31);
            } else if (period === 'all') {
                start = new Date(2000, 0, 1);
                end = today;
            }

            // LOGIKA SHOW/HIDE FIELD TANGGAL
            if (period === 'custom') {
                // Jika pilih custom, tampilkan field tanggal
                $('#custom_date_container').slideDown();
                // Hapus nilai agar user memilih sendiri atau set ke hari ini
                $('#exp_start_date').val('').prop('required', true);
                $('#exp_end_date').val('').prop('required', true);
            } else {
                // Jika pilih periode instan, sembunyikan field dan isi otomatis
                $('#custom_date_container').slideUp();

                const startDateString = start.toISOString().split('T')[0];
                const endDateString = end.toISOString().split('T')[0];

                $('#exp_start_date').val(startDateString).prop('required', false);
                $('#exp_end_date').val(endDateString).prop('required', false);
            }
        });

        $('#btn-reset').on('click', function() {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#region').val('').trigger('change');
        });
    });
</script>
<?= $this->endSection() ?>