<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
    </div>

    <div class="section-body">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Daftar Riwayat Transaksi</h4>
                <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus mr-1"></i> Tambah Transaksi
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped w-100" id="tableTransaksi">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th>Tanggal</th>
                                <th>Cabang</th>
                                <th>Rentang Usia</th>
                                <th>Metode</th>
                                <th>Nominal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTambahLabel">Input Transaksi Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTransaksi">
                <?= csrf_field() ?>
                <div class="modal-body">

                    <?php
                    $role = session()->get('role');
                    $user_region_id = session()->get('region_id');
                    $user_region_name = session()->get('region_name');
                    ?>

                    <?php if ($role === 'superadmin' || $role === 'owner'): ?>
                        <div class="form-group">
                            <label class="font-weight-bold">Cabang</label>
                            <select name="region_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php
                                $list_regions = session()->get('list_regions_global') ?? [];
                                foreach ($list_regions as $rg):
                                ?>
                                    <option value="<?= $rg['id'] ?>" <?= (session()->get('active_region') == $rg['id']) ? 'selected' : '' ?>>
                                        <?= $rg['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="region_id" value="<?= $user_region_id ?>">
                        <div class="form-group">
                            <label class="font-weight-bold">Cabang</label>
                            <input type="text" class="form-control bg-light" value="<?= $user_region_name ?? 'Cabang Tidak Terdeteksi' ?>" readonly>
                            <small class="text-muted font-italic">*Transaksi otomatis dicatat untuk cabang Anda.</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="font-weight-bold">Nominal Pembayaran</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text font-weight-bold">Rp</div>
                            </div>
                            <input type="number" name="nominal" class="form-control" placeholder="Contoh: 50000" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-control">
                            <option value="Cash">Cash (Tunai)</option>
                            <option value="Transfer">Transfer Bank</option>
                            <option value="QRIS">QRIS / Digital Payment</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Kategori Usia Pasien</label>
                        <div class="selectgroup w-100">
                            <?php foreach (['Anak', 'Remaja', 'Dewasa', 'Lansia'] as $u): ?>
                                <label class="selectgroup-item">
                                    <input type="radio" name="rentang_usia" value="<?= $u ?>" class="selectgroup-input" <?= $u == 'Anak' ? 'checked' : '' ?>>
                                    <span class="selectgroup-button"><?= $u ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm" id="btnSimpan">
                        <i class="mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        const table = $('#tableTransaksi').DataTable({
            processing: true,
            serverSide: true,
            order: [
                [1, 'desc']
            ],
            ajax: {
                url: "<?= site_url('transaksi/fetch') ?>",
                type: "POST",
                data: d => {
                    d.<?= csrf_token() ?> = "<?= csrf_hash() ?>";
                }
            },
            columns: [{
                    data: 'id_transaksi',
                    class: 'text-center'
                },
                {
                    data: 'tanggal',
                    name: 'created_at'
                },
                {
                    data: 'region_name'
                },
                {
                    data: 'rentang_usia',
                    class: 'text-center'
                },
                {
                    data: 'metode_pembayaran',
                    class: 'text-center'
                },
                {
                    data: 'nominal_format',
                    name: 'nominal',
                    class: 'font-weight-bold text-success'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    class: 'text-center'
                }
            ]
        });

        // Fix Select2 di dalam Modal
        $('#modalTambah').on('shown.bs.modal', function() {
            $('.select2').select2({
                dropdownParent: $('#modalTambah'),
                width: '100%'
            });
        });

        // AJAX Simpan
        $('#formTransaksi').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnSimpan');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.post("<?= site_url('transaksi/store') ?>", $(this).serialize(), function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#modalTambah').modal('hide');
                    $('#formTransaksi')[0].reset();
                    table.ajax.reload();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Data');
            }, 'json');
        });

        // AJAX Hapus
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Hapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post("<?= site_url('transaksi/delete') ?>", {
                        id_transaksi: id,
                        <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                    }, function() {
                        table.ajax.reload();
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>