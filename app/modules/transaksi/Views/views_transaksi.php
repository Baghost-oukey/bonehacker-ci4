<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<style>
    /* Custom Styling untuk kesan Premium */
    .card-dashboard {
        border-radius: 1.25rem;
        transition: transform 0.2s;
    }

    .card-dashboard:hover {
        transform: translateY(-3px);
    }

    .icon-box {
        border-radius: 0.75rem;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .table-transaksi thead th {
        border-top: none;
        background-color: #f9f9f9;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }

    .badge-custom {
        padding: 0.5em 1em;
        border-radius: 8px;
        font-weight: 600;
    }
</style>

<section class="section">
    <div class="section-header">
        <h1>Dashboard Keuangan</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card card-dashboard bg-primary text-white shadow-primary border-0" style="min-height: 220px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-white-50 font-weight-bold">SALDO HARI INI</span>
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                            <h2 class="font-weight-bold mb-0">Rp <?= number_format($today_balance, 0, ',', '.') ?></h2>
                            <small class="text-white-50">*Akan di-reset otomatis setiap hari</small>
                        </div>
                        <div class="mt-4">
                            <div class="row no-gutters gap-5">
                                <div class="col">
                                    <button class="btn btn-light btn-block btn-sm font-weight-bold" data-toggle="modal" data-target="#modalTambah">
                                        <i class="fas fa-plus-circle mr-1"></i> Transaksi
                                    </button>
                                </div>
                                <div class="col">
                                    <button class="btn btn-primary border-white btn-block btn-sm font-weight-bold" id="btnRekap">
                                        <i class="fas fa-print mr-1"></i> Rekap
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-12">
                <div class="row h-100">
                    <div class="col-md-6 mb-4">
                        <div class="card card-dashboard shadow-sm border-0 h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-light-success mr-3">
                                        <i class="fas fa-chart-line text-success fa-lg"></i>
                                    </div>
                                    <div>
                                        <span class="text-muted d-block small font-weight-bold">TOTAL PENDAPATAN</span>
                                        <h4 class="mb-0 font-weight-bold text-dark">Rp <?= number_format($total_income, 0, ',', '.') ?></h4>
                                    </div>
                                </div>
                                <div class="text-success small font-weight-bold">
                                    <i class="fas fa-arrow-up mr-1"></i> 12.5% <span class="text-muted font-weight-normal ml-1">dari bulan lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (in_array($role, ['superadmin', 'owner'])): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card card-dashboard shadow-sm border-0 h-100 bg-white">
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box bg-light-danger mr-3">
                                            <i class="fas fa-exchange-alt text-danger fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block small font-weight-bold">PENGELUARAN GLOBAL</span>
                                            <h4 class="mb-0 font-weight-bold text-danger">Rp <?= number_format($total_expense, 0, ',', '.') ?></h4>
                                        </div>
                                    </div>
                                    <span class="badge badge-light text-muted small">*Data semua wilayah</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 card-dashboard">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <h4 class="text-dark font-weight-bold mb-0">Riwayat Transaksi</h4>
                <div class="d-flex align-items-center">
                    <div class="input-group input-group-sm mr-2 shadow-sm" style="width: 200px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        </div>
                        <input type="date" id="filter_date" class="form-control border-left-0 pl-0">
                    </div>
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-white btn-sm text-danger border" id="btnExportPdf" title="Export PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-white btn-sm text-success border" id="btnExportExcel" title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-transaksi table-hover w-100" id="tableTransaksi">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th>Tanggal</th>
                                <th>Cabang</th>
                                <th>Usia</th>
                                <th>Metode</th>
                                <th>Nominal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalTambahLabel">Baru Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTransaksi">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="btn-group btn-group-toggle d-flex mb-4" data-toggle="buttons">
                        <label class="btn btn-outline-success flex-grow-1 active">
                            <input type="radio" name="type" value="income" checked> <i class="fas fa-arrow-down mr-1"></i> Pendapatan
                        </label>
                        <label class="btn btn-outline-danger flex-grow-1">
                            <input type="radio" name="type" value="expense"> <i class="fas fa-arrow-up mr-1"></i> Pengeluaran
                        </label>
                    </div>

                    <?php if (in_array($role, ['superadmin', 'owner'])): ?>
                        <div class="form-group">
                            <label class="font-weight-bold small">CABANG</label>
                            <select name="region_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php foreach ($list_regions as $rg): ?>
                                    <option value="<?= $rg['id'] ?>" <?= (session()->get('active_region') == $rg['id']) ? 'selected' : '' ?>><?= $rg['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="region_id" value="<?= session()->get('region_id') ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="font-weight-bold small">NOMINAL (RP)</label>
                        <input type="number" name="nominal" class="form-control" placeholder="0" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">KETERANGAN</label>
                        <textarea name="keterangan" class="form-control" placeholder="Contoh: Pembayaran Pasien A atau Biaya Listrik" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">METODE</label>
                                <select name="metode_pembayaran" class="form-control">
                                    <option value="Cash">Cash</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">USIA</label>
                                <select name="rentang_usia" class="form-control">
                                    <option value="Anak">Anak</option>
                                    <option value="Remaja">Remaja</option>
                                    <option value="Dewasa">Dewasa</option>
                                    <option value="Lansia">Lansia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4" id="btnSimpan">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {

        const table = $('#tableTransaksi').DataTable({
            processing: true,
            serverSide: true,
            order: [
                [1, 'desc']
            ], // Urutkan berdasarkan tanggal terbaru
            ajax: {
                url: "<?= site_url('transaksi/fetch') ?>",
                type: "POST",
                data: function(d) {
                    d.<?= csrf_token() ?> = "<?= csrf_hash() ?>";
                    d.date = $('#filter_date').val(); // Kirim filter tanggal ke server
                }
            },
            columns: [{
                    data: 'no',
                    class: 'text-center'
                },
                {
                    data: 'tanggal',
                    name: 'created_at'
                },
                {
                    data: 'region_name',
                    name: 'regions.name'
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
                    render: function(data, type, row) {
                        // Jika ada kolom 'type' di data JSON, kita kasih warna
                        let color = (row.type === 'expense') ? 'text-danger' : 'text-success';
                        return `<span class="${color} font-weight-bold">${data}</span>`;
                    }
                },
                {
                    data: 'aksi',
                    class: 'text-center',
                    orderable: false
                }
            ]
        });

        // 2. FUNGSI SIMPAN TRANSAKSI (AJAX)
        $('#formTransaksi').on('submit', function(e) {
            e.preventDefault(); // Mencegah reload halaman manual

            const btn = $('#btnSimpan');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: "<?= site_url('transaksi/store') ?>",
                type: "POST",
                data: $(this).serialize(),
                dataType: "JSON",
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalTambah').modal('hide');
                            $('#formTransaksi')[0].reset(); // Kosongkan form
                            location.reload(); // Reload total halaman agar saldo di Card terupdate
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                        btn.prop('disabled', false).text('Simpan Transaksi');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem atau session habis.', 'error');
                    btn.prop('disabled', false).text('Simpan Transaksi');
                    console.log(xhr.responseText); // Cek detail error di Inspect -> Console
                }
            });
        });

        // 3. FILTER TANGGAL
        $('#filter_date').on('change', function() {
            table.ajax.reload();
        });

        // 4. TRIGGER MODAL REKAP
        $('#btnRekap').on('click', function() {
            $('#modalRekap').modal('show');
        });

        // 5. EVENT EXPORT (PDF & EXCEL)
        $('#btnExportPdf').on('click', function() {
            let tgl = $('#filter_date').val();
            window.open("<?= site_url('transaksi/export_pdf') ?>?date=" + tgl, "_blank");
        });

        $('#btnExportExcel').on('click', function() {
            let tgl = $('#filter_date').val();
            window.location.href = "<?= site_url('transaksi/export_excel') ?>?date=" + tgl;
        });

        // 6. FUNGSI HAPUS (Jika kamu butuh tombol hapus di tabel)
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Hapus transaksi?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= site_url('transaksi/delete') ?>", {
                        id_transaksi: id,
                        <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                    }, function() {
                        location.reload();
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>