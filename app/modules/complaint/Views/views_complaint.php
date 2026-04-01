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
                        <h4>Daftar Tags Keluhan</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#complaintModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-complaint" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah Data</th>
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

<div id="complaintModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tag Keluhan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addComplaintForm" action="<?= base_url('complaint/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Tag Keluhan</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                        <div class="invalid-feedback" id="add_nameError">Nama tag tidak boleh kosong</div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" class="form-control" id="add_description" name="description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="add_submitBtn" disabled>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal_edit_complaint" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Tag Keluhan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editComplaintForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Tag Keluhan</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback" id="edit_nameError">Nama tag tidak boleh kosong</div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" class="form-control" id="edit_description" name="description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="edit_submitBtn" disabled>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal_delete_complaint" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Peringatan!</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Yakin menghapus data ini? Tag ini akan dihapus dari semua riwayat histori terkait.</p>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTable
        var table = $("#table-complaint").DataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                "url": "<?= site_url('complaint/fetch') ?>",
                "type": "POST",
                "data": function(d) {
                    d["<?= csrf_token() ?>"] = $('meta[name="csrf-token-hash"]').attr('content');
                }
            },
            "columns": [{
                    "data": "no",
                    "width": "5%",
                    "sortable": false,
                    "searchable": false
                },
                {
                    "data": "nama",
                    "width": "25%",
                    "searchable": true
                },
                {
                    "data": "deskripsi",
                    "width": "40%",
                    "searchable": false
                },
                {
                    "data": "jumlah",
                    "width": "15%",
                    "class": "text-center",
                    "sortable": false,
                    "searchable": false
                },
                {
                    "data": "action",
                    "width": "15%",
                    "class": "text-right",
                    "sortable": false,
                    "searchable": false
                }
            ]
        });

        // 2. Event Button Edit
        $(document).on('click', '.btn_edit', function() {
            const href = $(this).data('href');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const id = $(this).data('id');

            $("#modal_edit_complaint").modal('show');
            $("#edit_name").val(name);
            $("#edit_description").val(description);
            $("#editComplaintForm").attr("action", href);

            // Reset state tombol simpan
            $("#edit_submitBtn").prop('disabled', true);

            // Inisialisasi ulang validasi untuk Edit
            validateInput('#edit_name', '#edit_submitBtn', '#edit_nameError', name, id, '#edit_description', description);
        });

        // 3. Event Button Delete
        $(document).on('click', '.btn_delete', function() {
            const href = $(this).data('href');
            $("#modal_delete_complaint").modal('show');
            $("#modal_delete_complaint form").attr("action", href);
        });

        // 4. Logika Validasi Duplicate Name (Reusable)
        let ajaxRequest;
        let isNameInvalid = false;

        function validateInput(inputId, submitBtnId, nameErrorId, originalValue, originalId, descInputId, originalDesc) {
            let debounceTimer;

            $(`${inputId}, ${descInputId}`).on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const currentName = $(inputId).val().trim();
                    const currentDesc = $(descInputId).val().trim();

                    // Cek jika kosong
                    if (currentName === '') {
                        setInvalid(inputId, nameErrorId, submitBtnId, 'Nama tag tidak boleh kosong.');
                        return;
                    }

                    // Cek jika tidak ada perubahan sama sekali
                    if (currentName === originalValue && currentDesc === originalDesc) {
                        setValid(inputId, nameErrorId, submitBtnId, false);
                        return;
                    }

                    // Jika nama berubah, cek database
                    if (currentName !== originalValue) {
                        if (ajaxRequest) ajaxRequest.abort();

                        ajaxRequest = $.ajax({
                            url: '<?= base_url('complaint/check_name_exists') ?>',
                            type: 'POST',
                            data: {
                                name: currentName,
                                id: originalId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.exists) {
                                    isNameInvalid = true;
                                    setInvalid(inputId, nameErrorId, submitBtnId, 'Nama tag sudah digunakan.');
                                } else {
                                    isNameInvalid = false;
                                    setValid(inputId, nameErrorId, submitBtnId, true);
                                }
                            }
                        });
                    } else {
                        // Nama sama (original) tapi deskripsi berubah
                        setValid(inputId, nameErrorId, submitBtnId, true);
                    }
                }, 300);
            });
        }

        function setInvalid(inputId, errorId, btnId, msg) {
            $(inputId).addClass('is-invalid');
            $(errorId).text(msg);
            $(btnId).prop('disabled', true);
        }

        function setValid(inputId, errorId, btnId, enable) {
            $(inputId).removeClass('is-invalid');
            $(errorId).text('');
            $(btnId).prop('disabled', !enable);
        }

        $('#complaintModal').on('show.bs.modal', function() {
            $('#addComplaintForm')[0].reset();
            setValid('#add_name', '#add_nameError', '#add_submitBtn', false);
            validateInput('#add_name', '#add_submitBtn', '#add_nameError', '', '', '#add_description', '');
        });
    });
</script>
<?= $this->endSection() ?>