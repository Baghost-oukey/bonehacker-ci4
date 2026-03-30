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
                        <h4>Daftar Jabatan</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal"
                                data-target="#jabatanModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-jabatan" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Deskripsi</th>
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

<div id="jabatanModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Jabatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addJabatanForm" action="<?= base_url('jabatan/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Jabatan</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                        <div class="invalid-feedback" id="add_nameError">Nama jabatan tidak boleh kosong</div>
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

<div id="modal_edit_jabatan" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Data Jabatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editjabatanForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Jabatan</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback" id="edit_nameError">Nama Jabatan tidak boleh kosong</div>
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

<!-- <div id="modal_delete_jabatan" class="modal fade">
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
                    <p>Yakin menghapus data ini ?</p>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ya</button>
                </div>
            </form>
        </div>
    </div>
</div> -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {

        <?php if (session()->getFlashdata('message')) : ?>
            <?php $flash = session()->getFlashdata('message'); ?>
            Swal.fire({
                icon: '<?= $flash[0] ?>',
                title: '<?= ($flash[0] == 'success') ? 'Mantap!' : 'Oops!' ?>',
                text: '<?= $flash[1] ?>',
                timer: 2500,
                showConfirmButton: false
            });
        <?php endif; ?>

        // Inisialisasi DataTable
        $("#table-jabatan").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?= base_url('jabatan/fetch') ?>",
                "type": "POST",
                "data": function(d) {
                    d.<?= csrf_token() ?> = "<?= csrf_hash() ?>"; // Kirim CSRF Token
                }
            },
            "columns": [{
                    "data": "no",
                    "width": "5%",
                    "sortable": false,
                    "searchable": false
                },
                {
                    "data": "nama_jabatan",
                    "width": "25%"
                },
                {
                    "data": "deskripsi",
                    "width": "50%"
                },
                {
                    "data": "action",
                    "class": "text-center",
                    "width": "20%",
                    "sortable": false,
                    "searchable": false
                }
            ]
        });

        // Event Edit
        $(document).on('click', '.btn_edit', function() {
            const href = $(this).data('href');
            const name = $(this).data('name');
            const deskripsi = $(this).data('description');

            $("#modal_edit_jabatan").modal('show');
            $("#edit_name").val(name);
            $("#edit_deskripsi").val(deskripsi);
            $("#editjabatanForm").attr("action", href);

            $("#edit_submitBtn").prop('disabled', false);
            $("#edit_name").removeClass('is-invalid');

        });

        // Event Delete
        $(document).on('click', '.btn_delete', function() {
            const href = $(this).data('href');
            Swal.fire({
                title: 'Hapus Jabatan ?',
                text: "Jabatan Akan dihapus dari daftar",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(href, {
                        "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                    }, function(res) {
                        Swal.fire({
                            icon: res.status, // success atau error
                            title: res.status === 'success' ? 'Mantap!' : 'Oops!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }, 'json');
                    // window.location.href = href;
                }
                // $("#modal_delete_jabatan").modal('show');
                // $("#modal_delete_jabatan form").attr("action", href);
            })
        })

        // Logic Validasi AJAX (Check Exists)
        function setupValidation(inputId, submitBtnId, errorId, originalName, originalId) {
            let timer;
            $(inputId).on('input', function() {
                clearTimeout(timer);
                const name = $(this).val();

                if (name.trim() === "") {
                    $(submitBtnId).prop('disabled', true);
                    return;
                }

                if (name === originalName) {
                    $(inputId).removeClass('is-invalid');
                    $(submitBtnId).prop('disabled', false);
                    return;
                }

                timer = setTimeout(() => {
                    $.ajax({
                        url: "<?= base_url('jabatan/check_name_exists') ?>",
                        type: "POST",
                        data: {
                            name: name,
                            id: originalId,
                            ["<?= csrf_token() ?>"]: "<?= csrf_hash() ?>"
                        },
                        success: function(res) {
                            if (res.exists) {
                                $(inputId).addClass('is-invalid');
                                $(errorId).text('Nama jabatan sudah digunakan.');
                                $(submitBtnId).prop('disabled', true);
                            } else {
                                $(inputId).removeClass('is-invalid');
                                $(submitBtnId).prop('disabled', false);
                            }
                        }
                    });
                }, 500);
            });
        }

        // Init Validation untuk modal tambah
        $('#jabatanModal').on('shown.bs.modal', function() {
            setupValidation('#add_name', '#add_submitBtn', '#add_nameError', '', '');
        });

        $('#modal_edit_jabatan').on('shown.bs.modal', function() {
            const originalName = $("#edit_name").val();
            const urlParts = $("#editjabatanForm").attr("action").split('/');
            const originalId = urlParts[urlParts.length - 1];

            setupValidation('#edit_name', '#edit_submitBtn', '#edit_nameError', originalName, originalId);
        });
    });
</script>
<?= $this->endSection() ?>