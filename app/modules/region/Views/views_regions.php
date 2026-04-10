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
                        <h4>Daftar Wilayah</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-region" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Waktu Buat</th>
                                        <th>Terakhir Diperbarui</th>
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

<div id="exampleModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Wilayah</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('region/store'); ?>" method="post" class="needs-validation" novalidate="">
                <?= csrf_field() ?> <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Wilayah</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                        <div class="invalid-feedback">Nama wilayah tidak boleh kosong</div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modela Edit -->
<div id="modal_edit_region" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Data Wilayah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_edit_region" action="" method="post" class="needs-validation" novalidate="">
                <?= csrf_field() ?> <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Wilayah</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required autofocus>
                        <div class="invalid-feedback">Nama wilayah tidak boleh kosong</div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modela Hapus -->
<div id="modal_delete_region" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Peringatan!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_delete_region" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data wilayah ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#table-region')) {
            $('#table-region').DataTable().clear().destroy();
        }

        $('#table-region').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "autoWidth": false,
            "ajax": {
                "url": "<?= base_url('region/fetch') ?>",
                "type": "POST",
                "data": function(d) {
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                },
                error: function(xhr, error, code) {
                    console.log(xhr.responseText); // Lihat error detail di console
                }
            },
            "columns": [{
                    "data": "id",
                    "width": "7%"
                },
                {
                    "data": "name_view",
                    "width": "37.5%"
                },
                {
                    "data": "created_at",
                    "width": "20.5%"
                },
                {
                    "data": "updated_at",
                    "width": "20.5%"
                },
                {
                    "data": "action",
                    "class": "text-center",
                    "width": "10%",
                    "orderable": false
                }
            ],
            "order": [
                [0, 'asc']
            ]
        });

$(document).on('click', '.btn_edit', function(e) {
    e.preventDefault();
    const href = $(this).data('href'); 
    const name = $(this).data('name');
    $("#modal_edit_region form").attr("action", href);
    $("#modal_edit_region #edit_name").val(name); 

    $("#modal_edit_region").modal('show');
});

$(document).on('click', '.btn_delete', function(e) {
    e.preventDefault();
    const href = $(this).data('href');

    if (!href) {
        console.error("URL tidak ditemukan pada atribut data-href!");
        return;
    }
    $("#modal_delete_region form").attr("action", href);
    $("#modal_delete_region").modal('show');
});
    });
</script>
<?= $this->endSection() ?>