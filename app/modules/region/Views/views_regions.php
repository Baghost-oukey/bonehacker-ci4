<?= $this->extend('App\Views\blade\layout') ?> <?= $this->section('content') ?>
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
        "ajax": {
            "url": "<?= base_url('region/fetch') ?>", 
            "type": "POST",
            "data": function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        "columns": [
            { "data": "id", "width": "7%" },
            { "data": "name_view", "width": "37.5%" },
            { "data": "created_at", "width": "20.5%" },
            { "data": "updated_at", "width": "20.5%" },
            { "data": "action", "class": "text-center", "width": "10%", "orderable": false }
        ]
    });
});
</script>
<?= $this->endSection() ?>