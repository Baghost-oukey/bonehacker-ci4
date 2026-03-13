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
                        <h4>Daftar Tags Riwayat Medis</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#medhisModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-medhis" class="table table-striped">
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

<div id="medhisModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tag Riwayat Medis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addMedhisForm" action="<?= base_url('medis/store') ?>" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Tag Riwayat Medis</label>
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

<div id="modal_edit_medhis" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Tag Riwayat Medis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editMedhisForm" action="<?= base_url('medis/update') ?>" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Tag Riwayat Medis</label>
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

<div id="modal_delete_medhis" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Peringatan!</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <p>Yakin menghapus data ini ?</p>
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
    // Data tabel riwayat medis
    $("#table-medhis").dataTable({
        "processing": true,
        "serverSide": true,
        "columns": [
            { "data": "no", "width": "5%", "sortable": false, "searchable": false },
            { "data": "nama", "width": "24.5%", "sortable": true },
            { "data": "deskripsi", "width": "45.5%", "sortable": true },
            { "data": "jumlah", "width": "10.5%", "sortable": false, "searchable": false },
            { "data": "action", "class": "text-center", "width": "10%", "sortable": false, "searchable": false },
        ],
        "order": [],
        "ajax": {
            "url": "<?= site_url('medis/fetch') ?>",
            "type": "POST"
        },
    });

    // Event button edit
    $(document).on('click', '.btn_edit', function() {
        const href = $(this).data('href');
        const name = $(this).data('name');
        const deskripsi = $(this).data('description');

        $("#modal_edit_medhis").modal('show');
        $("#modal_edit_medhis #edit_name").val(name);
        $("#modal_edit_medhis #edit_deskripsi").val(deskripsi);
        $("#modal_edit_medhis form").attr("action", href);
    });

    // Event button delete
    $(document).on('click', '.btn_delete', function() {
        const href = $(this).data('href');
        $("#modal_delete_medhis").modal('show');
        $("#modal_delete_medhis form").attr("action", href);
    });

    // Validasi input
    $(document).ready(function() {
        let originalName = '';
        let originalId = '';
        let originalDescription = '';
        let ajaxRequest;
        let isNameInvalid = false;

        function validateInput(inputId, submitBtnId, nameErrorId, originalValue, originalId, descriptionInputId, originalDescription) {
            let debounceTimer;

            $(inputId).on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const name = $(this).val();

                    if (name.trim() === '') {
                        setInvalid(nameErrorId, submitBtnId, 'Nama tag tidak boleh kosong.');
                        return;
                    }

                    if (name === originalValue) {
                        isNameInvalid = false;
                        setValid(nameErrorId, submitBtnId, $(descriptionInputId).val() !== originalDescription);
                        return;
                    }

                    if (ajaxRequest) { ajaxRequest.abort(); }

                    ajaxRequest = $.ajax({
                        url: '<?= base_url('medis/check_name_exists') ?>',
                        type: 'POST',
                        data: { name: name, id: originalId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.exists) {
                                isNameInvalid = true;
                                setInvalid(nameErrorId, submitBtnId, 'Nama tag sudah ada, gunakan nama lain.');
                            } else {
                                isNameInvalid = false;
                                setValid(nameErrorId, submitBtnId, true);
                            }
                        }
                    });
                }, 300);
            });

            $(descriptionInputId).on('input', function() {
                const descriptionChanged = $(this).val() !== originalDescription;
                const name = $(inputId).val();
                const nameChanged = name !== originalValue;

                if (name.trim() === '') {
                    setInvalid(nameErrorId, submitBtnId, 'Nama tag tidak boleh kosong.');
                    return;
                }

                if (!isNameInvalid) {
                    setValid(nameErrorId, submitBtnId, descriptionChanged || nameChanged);
                }
            });

            function setInvalid(nameErrorId, submitBtnId, message) {
                $(inputId).addClass('is-invalid');
                $(nameErrorId).text(message);
                $(submitBtnId).prop('disabled', true);
            }

            function setValid(nameErrorId, submitBtnId, enableSave) {
                $(inputId).removeClass('is-invalid');
                $(nameErrorId).text('');
                $(submitBtnId).prop('disabled', !enableSave);
            }
        }

        function resetForm(inputId, nameErrorId, submitBtnId) {
            $(inputId).removeClass('is-invalid').val('');
            $(nameErrorId).text('');
            $(submitBtnId).prop('disabled', true);
        }

        $('#medhisModal').on('show.bs.modal', function() {
            resetForm('#add_name', '#add_nameError', '#add_submitBtn');
            $('#add_deskripsi').val('');
            validateInput('#add_name', '#add_submitBtn', '#add_nameError', '', '', '#add_deskripsi', '');
        });

        $(document).on('click', '.btn_edit', function(event) {
            var button = $(this);
            var Id = button.data('id');
            var Name = button.data('name');
            var Description = button.data('description');

            if (Id && Name) {
                $('#edit_name').val(Name);
                $('#edit_deskripsi').val(Description);

                originalName = Name;
                originalId = Id;
                originalDescription = Description;

                resetForm('#edit_name', '#edit_nameError', '#edit_submitBtn');
                $('#edit_submitBtn').prop('disabled', true); // Default disable sampai ada perubahan
                validateInput('#edit_name', '#edit_submitBtn', '#edit_nameError', originalName, originalId, '#edit_deskripsi', originalDescription);
            }
        });
    });
</script>
<?= $this->endSection() ?>