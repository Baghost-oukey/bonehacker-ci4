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
                        <h4>Daftar Tags Hasil Pemeriksaan</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal"
                                data-target="#resultModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-result" class="table table-striped w-100">
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

<div id="resultModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tag Hasil Pemeriksaan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addResultForm" action="<?= base_url('result/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Tag Hasil Pemeriksaan</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                        <div class="invalid-feedback" id="add_nameError">Nama tag tidak boleh kosong</div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" class="form-control" id="add_deskripsi" name="deskripsi">
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

<div id="modal_edit_result" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Tag Hasil Pemeriksaan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editResultForm" action="<?= base_url('result/update') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Tag Hasil Pemeriksaan</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback" id="edit_nameError">Nama tag tidak boleh kosong</div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" class="form-control" id="edit_deskripsi" name="deskripsi">
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

<div id="modal_delete_result" class="modal fade">
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
                    <p>Yakin menghapus data ini?</p>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ya</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // DataTables Initialization
        $("#table-result").dataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                "url": "<?= site_url('result/fetch') ?>",
                "type": "POST",
                "data": function(d) {
                    d.<?= csrf_token() ?> = "<?= csrf_hash() ?>"; // CSRF Token for DataTables
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
                    "width": "24.5%",
                    "sortable": true
                },
                {
                    "data": "deskripsi",
                    "width": "45.5%",
                    "sortable": true
                },
                {
                    "data": "jumlah",
                    "width": "10.5%",
                    "sortable": false,
                    "searchable": false
                },
                {
                    "data": "action",
                    "class": "text-right",
                    "width": "15%",
                    "sortable": false,
                    "searchable": false
                }
            ]
        });

        // Event: Edit Button
        $(document).on('click', '.btn_edit', function() {
            const href = $(this).data('href');
            const name = $(this).data('name');
            const deskripsi = $(this).data('description');
            const id = $(this).data('id');

            $("#modal_edit_result").modal('show');
            $("#edit_id").val(id);
            $("#edit_name").val(name);
            $("#edit_deskripsi").val(deskripsi);
            $("#editResultForm").attr("action", href);

            // Re-trigger validation check for edit
            validateInput('#edit_name', '#edit_submitBtn', '#edit_nameError', name, id, '#edit_deskripsi', deskripsi);
        });

        // Event: Delete Button
        $(document).on('click', '.btn_delete', function() {
            const href = $(this).data('href');
            $("#modal_delete_result").modal('show');
            $("#modal_delete_result form").attr("action", href);
        });

        // Validation Logic
        let ajaxRequest;
        let isNameInvalid = false;

        function validateInput(inputId, submitBtnId, nameErrorId, originalValue, originalId, descriptionInputId, originalDescription) {
            let debounceTimer;

            $(inputId).on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const name = $(this).val();

                    if (name.trim() === '') {
                        setInvalid(inputId, nameErrorId, submitBtnId, 'Nama tag tidak boleh kosong.');
                        return;
                    }

                    if (name === originalValue) {
                        isNameInvalid = false;
                        const descChanged = $(descriptionInputId).val() !== originalDescription;
                        setValid(inputId, nameErrorId, submitBtnId, descChanged);
                        return;
                    }

                    if (ajaxRequest) ajaxRequest.abort();

                    ajaxRequest = $.ajax({
                        url: '<?= base_url('result/check_name_exists') ?>',
                        type: 'POST',
                        data: {
                            name: name,
                            id: originalId,
                            <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.exists) {
                                isNameInvalid = true;
                                setInvalid(inputId, nameErrorId, submitBtnId, 'Nama tag sudah ada.');
                            } else {
                                isNameInvalid = false;
                                setValid(inputId, nameErrorId, submitBtnId, true);
                            }
                        }
                    });
                }, 300);
            });

            $(descriptionInputId).on('input', function() {
                const name = $(inputId).val();
                const nameChanged = name !== originalValue;
                const descChanged = $(this).val() !== originalDescription;

                if (name.trim() === '') {
                    setInvalid(inputId, nameErrorId, submitBtnId, 'Nama tag tidak boleh kosong.');
                } else if (!isNameInvalid) {
                    setValid(inputId, nameErrorId, submitBtnId, nameChanged || descChanged);
                }
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

        // Reset forms on modal show
        $('#resultModal').on('show.bs.modal', function() {
            $('#addResultForm')[0].reset();
            setValid('#add_name', '#add_nameError', '#add_submitBtn', false);
            validateInput('#add_name', '#add_submitBtn', '#add_nameError', '', '', '#add_deskripsi', '');
        });
    });
</script>
<?= $this->endSection() ?>