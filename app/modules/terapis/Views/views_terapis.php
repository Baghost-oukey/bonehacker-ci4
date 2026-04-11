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
                    <div class="card-header">
                        <h4>Data Terapis</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#terapisModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-terapis" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>
                                            <select id="region_filter" class="form-control select2">
                                                <option value="">Semua Wilayah</option>
                                                <?php foreach ($regions as $value) : ?>
                                                    <option value="<?= $value->id ?>"><?= $value->name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </th>
                                        <th>Alamat</th>
                                        <th>Jumlah Tindakan</th>
                                        <th>Status</th>
                                        <th class="text-right">Aksi</th>
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

<div id="terapisModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Terapis</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= base_url('terapis/store') ?>" id="InputForm" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID Terapis (NIK/ID) :</label>
                                <input type="text" class="form-control" name="terapis_id" id="terapis_id" required>
                                <div id="idError" class="invalid-feedback">ID tidak boleh kosong</div>
                            </div>
                            <div class="form-group">
                                <label>Nama :</label>
                                <input type="text" class="form-control" name="nama" required>
                            </div>
                            <div class="form-group">
                                <label>Tempat Lahir :</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir :</label>
                                <input type="date" class="form-control" name="tgl_lahir" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Wilayah :</label>
                                <select class="form-control select2" name="region_id" style="width: 100%" required>
                                    <option value="">-- Pilih Wilayah --</option>
                                    <?php foreach ($regions as $value) : ?>
                                        <option value="<?= $value->id ?>"><?= $value->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jabatan :</label>
                                <select class="form-control select2" name="jabatan_id" style="width: 100%">
                                    <option value="">-- Pilih Jabatan --</option>
                                    <?php foreach ($jabatan as $j) : ?>
                                        <option value="<?= $j->id ?>"><?= $j->nama_jabatan ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Rank :</label>
                                <select class="form-control" name="rank">
                                    <option value="">-- Pilih Rank --</option>
                                    <option value="SS">SS</option>
                                    <option value="S">S</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat :</label>
                        <textarea class="form-control" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Mulai Kerja :</label>
                                <input type="date" class="form-control" name="tgl_kerja">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto :</label>
                                <input type="file" class="form-control" name="foto" id="foto" accept="image/*" onchange="previewImage(event)">
                                <small class="text-danger">Maks. 2MB (JPG, JPEG, PNG)</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <img id="preview" src="#" alt="Preview" style="display: none; max-width: 150px; margin: 0 auto;" class="img-thumbnail" />
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button id="add_submitBtn" type="submit" class="btn btn-primary" disabled>Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal_status_terapis" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="confirm-text"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-confirm-submit">Ya, Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();

        // DataTable Initialization
        var table = $('#table-terapis').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [
                [1, 'asc']
            ],
            "ajax": {
                "url": "<?= base_url('terapis/fetch') ?>",
                "type": "POST",
                "data": function(d) {
                    d.<?= csrf_token() ?> = $('meta[name="csrf-token-hash"]').attr('content') || '<?= csrf_hash() ?>';
                    d.region = $('#region_filter').val();
                },
                "dataSrc": function(json) {
                    if (json.crsfHash) {
                        $('meta[name="<?= csrf_token() ?>"]').attr('content', json.csrfHash);
                    }
                    return json.data;
                }
            },
            "columns": [{
                    "data": "no",
                    "width": "5%",
                    "sortable": false,
                    "searchable": false,
                },
                {
                    "data": "nama",
                    "width": "20%",
                    "searchable": true,

                },
                {
                    "data": "region_name",
                    "width": "15%",
                    "sortable": false,
                    "searchable": true,
                },
                {
                    "data": "alamat",
                    "width": "25%",
                    "searchable": false,
                },
                {
                    "data": "jml_tindakan",
                    "class": "text-center",
                    "orderable": false,
                    "searchable": false
                },
                {
                    "data": "is_active",
                    "class": "text-center",
                    "searchable": false
                },
                {
                    "data": "action",
                    "class": "text-right",
                    "sortable": false,
                    "searchable": false
                }
            ]
        });

        // Filter Wilayah
        $('#region_filter').on('change', function() {
            table.ajax.reload();
        });

        // Validation ID (NIK) Check via Ajax
        $('#terapis_id').on('input', function() {
            var id = $(this).val().trim();
            var errorLabel = $('#idError');
            var btn = $('#add_submitBtn');
            if (id.length < 7) {
                $(this).addClass('is-invalid');
                errorLabel.text('ID terlalu pendek').show();
                btn.prop('disabled', true);
                return;
            } else {
                $(this).removeClass('is-invalid');
                errorLabel.hide();
            }
            $.post("<?= base_url('terapis/checkId') ?>", {
                terapis_id: id,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            }, function(res) {
                if (res.exists) {
                    $('#terapis_id').addClass('is-invalid');
                    errorLabel.text('ID sudah digunakan terapis lain').show();
                    btn.prop('disabled', true);
                } else {
                    $('#terapis_id').removeClass('is-invalid').addClass('is-valid');
                    errorLabel.hide();
                    btn.prop('disabled', false);
                }
            }, 'json');
        });

        $(document).on('click', '.btn_status', function(e) {
            e.preventDefault();
            e.stopPropagation();


            const btn = $(this);
            const href = btn.data('href');
            const type = btn.data('type');

            const isDelete = (type === 'delete');
            const config = {
                title: isDelete ? 'Nonaktifkan Terapis?' : 'Aktifkan Terapis?',
                text: isDelete ? 'Terapis ini tidak akan muncul dalam daftar tindakan.' : 'Terapis akan kembali aktif di sistem.',
                icon: 'warning',
                confirmBtn: isDelete ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
                confirmColor: isDelete ? '#e74c3c' : '#3498db'
            };

            Swal.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                showCancelButton: true,
                confirmButtonColor: config.confirmColor,
                cancelButtonColor: '#bdc3c7',
                confirmButtonText: config.confirmBtn,
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: href,
                        type: 'POST',
                        data: {
                          [$('meta[name="csrf-header"]').attr('content')]: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json'
                    }).fail((xhr) => {
                        Swal.showValidationMessage(`Request failed: ${xhr.statusText}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const res = result.value;
                   if (res && res.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', res.csrfHash);
                    }

                    if (res && res.status === 'success') {
                      $('#table-terapis').DataTable().ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            });
        });
        // Handler Detail Terapis
        $(document).on('click', '.btn_detail_terapis', function() {
            window.location.href = "<?= base_url('terapis/detail/') ?>/" + $(this).data('userid');
        });
    });

    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<?= $this->endSection() ?>