<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="transaksiPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Pantau dan kelola transaksi keuangan klinik secara real-time
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" id="btnRekap"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-print text-slate-500"></i>
                Rekap
            </button>

            <?php if (in_array($role, ['superadmin', 'owner', 'admin'])): ?>
            <button type="button" data-modal-open="modalMutasi"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                <i class="fas fa-exchange-alt text-white"></i>
                Pindah Buku
            </button>
            <?php endif; ?>

            <button type="button" data-modal-open="modalTambah"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Transaksi Baru
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <!-- Saldo Kas Kecil -->
        <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">SALDO KAS KECIL</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50">
                    <i class="fas fa-wallet text-teal-600 text-sm"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-semibold tracking-tight text-slate-900 truncate">
                    Rp <?= number_format($saldo_kas_kecil, 0, ',', '.') ?>
                </h3>
            </div>
            <p class="mt-4 text-xs text-slate-500">Saldo tunai admin saat ini</p>
        </div>

        <?php if (in_array($role, ['superadmin', 'owner'])): ?>
        <!-- Saldo Kas Besar -->
        <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">SALDO KAS BESAR</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                    <i class="fas fa-building text-blue-600 text-sm"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-semibold tracking-tight text-slate-900 truncate">
                    Rp <?= number_format($saldo_kas_besar, 0, ',', '.') ?>
                </h3>
            </div>
            <p class="mt-4 text-xs text-slate-500">Saldo tabungan/owner</p>
        </div>
        <?php endif; ?>

        <!-- Pemasukan -->
        <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">PEMASUKAN</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="fas fa-arrow-down text-emerald-600 text-sm"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-semibold tracking-tight text-emerald-600 truncate">
                    Rp <?= number_format($total_income, 0, ',', '.') ?>
                </h3>
            </div>
            <p class="mt-4 text-xs text-slate-500">Total pemasukan <?= $period_label ?></p>
        </div>

        <!-- Pengeluaran -->
        <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">PENGELUARAN</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50">
                    <i class="fas fa-arrow-up text-rose-600 text-sm"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-semibold tracking-tight text-rose-600 truncate">
                    Rp <?= number_format($total_expense, 0, ',', '.') ?>
                </h3>
            </div>
            <p class="mt-4 text-xs text-slate-500">Total pengeluaran <?= $period_label ?></p>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-4 sm:px-6 py-4">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Riwayat Transaksi</h3>
                <p class="text-sm text-slate-500">Data transaksi keuangan klinik</p>
            </div>
 
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4 w-full">
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari transaksi..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                    <?php if ($role !== 'admin'): ?>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar text-slate-400 text-base"></i>
                        <input type="month" id="filter_month" value="<?= esc($current_month) ?>"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <?php endif; ?>
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <i class="fas fa-calendar-alt text-slate-400 text-base"></i>
                        <input type="date" id="filter_date_start"
                            class="flex-1 min-w-[120px] sm:flex-initial rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                            placeholder="Dari Tanggal" value="<?= ($role === 'admin') ? date('Y-m-d') : '' ?>">
                        <span class="text-slate-400">-</span>
                        <input type="date" id="filter_date_end"
                            class="flex-1 min-w-[120px] sm:flex-initial rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                            placeholder="Sampai Tanggal" value="<?= ($role === 'admin') ? date('Y-m-d') : '' ?>">
                    </div>
                </div>
            </div>
        </div>
 
        <!-- Tampilan Desktop (Table) -->
        <div class="hidden lg:block overflow-x-auto w-full no-scrollbar">
            <table id="tableTransaksi" class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="hidden sm:table-cell px-4 sm:px-6 py-3.5 text-left font-semibold">No</th>
                        <th class="px-4 sm:px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="hidden md:table-cell px-4 sm:px-6 py-3.5 text-left font-semibold">Cabang</th>
                        <th class="px-4 sm:px-6 py-3.5 text-left font-semibold">Keterangan</th>
                        <th class="hidden lg:table-cell px-4 sm:px-6 py-3.5 text-left font-semibold">Ditambahkan Oleh</th>
                        <th class="px-4 sm:px-6 py-3.5 text-left font-semibold">Nominal</th>
                        <th class="px-4 sm:px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <!-- Data ditarik via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Tampilan Mobile/Tablet (Cards) -->
        <div class="block lg:hidden p-4 space-y-3 bg-slate-50/30" id="cardsTransaksi">
            <!-- Data ditarik via AJAX -->
        </div>
    </div>
</section>

<!-- Modal Tambah Transaksi -->
<div id="modalTambah" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Transaksi Baru</h5>
            <button type="button" data-modal-close class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formTransaksi" class="space-y-4 p-5">
            <?= csrf_field() ?>

            <!-- Type Toggle -->
            <div class="flex rounded-lg border border-slate-200 p-1">
                <label class="flex-1 cursor-pointer rounded-md px-4 py-2 text-center text-sm font-medium transition" id="labelIncome">
                    <input type="radio" name="type" value="income" class="hidden" checked>
                    <span class="text-emerald-600"><i class="fas fa-arrow-down mr-1"></i> Pendapatan</span>
                </label>
                <label class="flex-1 cursor-pointer rounded-md px-4 py-2 text-center text-sm font-medium transition" id="labelExpense">
                    <input type="radio" name="type" value="expense" class="hidden">
                    <span class="text-rose-600"><i class="fas fa-arrow-up mr-1"></i> Pengeluaran</span>
                </label>
            </div>

            <!-- Cabang -->
            <?php if (in_array($role, ['superadmin', 'owner'])): ?>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Cabang</label>
                    <select name="region_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" required>
                        <option value="">-- Pilih Cabang --</option>
                        <?php foreach ($list_regions as $rg): ?>
                            <option value="<?= $rg['id'] ?>" <?= session()->get('active_region') == $rg['id'] ? 'selected' : '' ?>>
                                <?= $rg['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="region_id" value="<?= session()->get('region_id') ?>">
            <?php endif; ?>

            <!-- Tanggal Transaksi -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Tanggal Transaksi</label>
                <input type="date" name="tanggal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Sumber/Tujuan Kas -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Pilih Kas</label>
                <select name="kas_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" required>
                    <option value="kas_kecil">Kas Kecil (Admin)</option>
                    <option value="kas_besar">Kas Besar (Owner)</option>
                </select>
            </div>

            <!-- Kategori -->
            <div class="space-y-1" id="kategoriContainer" style="display:none;">
                <label class="text-sm font-medium text-slate-700" id="kategoriLabel">Kategori Transaksi</label>
                <select name="kategori_pilihan" id="kategori_pilihan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="" data-type="all">-- Pilih Kategori --</option>
                    <?php foreach ($list_kategori as $kat): ?>
                        <option value="<?= esc($kat['name']) ?>" data-type="<?= $kat['type'] ?>"><?= esc($kat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Terapis/Karyawan -->
            <div class="space-y-1" id="terapisContainer" style="display:none;">
                <label class="text-sm font-medium text-slate-700">Pilih Karyawan</label>
                <select name="terapis_id" id="terapis_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="">-- Pilih Karyawan --</option>
                    <?php foreach ($list_terapis as $tp): ?>
                        <option value="<?= $tp['id'] ?>" data-region-id="<?= $tp['region_id'] ?>"><?= esc($tp['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-blue-600 mt-1"><i class="fas fa-info-circle"></i> Data akan otomatis masuk ke modul terkait.</p>
            </div>

            <!-- Nominal -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nominal (Rp)</label>
                <input type="text" name="nominal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" placeholder="0" required>
            </div>

            <!-- Keterangan -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Keterangan</label>
                <input type="text" name="keterangan" list="listKeterangan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" placeholder="Contoh: Pendapatan harian atau Biaya Listrik" required autocomplete="off">
                <datalist id="listKeterangan">
                    <?php foreach ($recent_keterangan as $rk): ?>
                        <option value="<?= esc($rk['keterangan']) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnSimpan" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pindah Buku -->
<div id="modalMutasi" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Pindah Buku (Mutasi Kas)</h5>
            <button type="button" data-modal-close class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formMutasi" class="space-y-4 p-5">
            <?= csrf_field() ?>

            <!-- Cabang (hanya jika superadmin/owner) -->
            <?php if (in_array($role, ['superadmin', 'owner'])): ?>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Cabang</label>
                    <select name="region_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" required>
                        <option value="">-- Pilih Cabang --</option>
                        <?php foreach ($list_regions as $rg): ?>
                            <option value="<?= $rg['id'] ?>" <?= session()->get('active_region') == $rg['id'] ? 'selected' : '' ?>>
                                <?= $rg['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="region_id" value="<?= session()->get('region_id') ?>">
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Dari Kas</label>
                    <select name="dari_kas" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        <option value="kas_kecil">Kas Kecil</option>
                        <option value="kas_besar">Kas Besar</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Ke Kas</label>
                    <select name="ke_kas" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                        <option value="kas_besar">Kas Besar</option>
                        <option value="kas_kecil">Kas Kecil</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nominal (Rp)</label>
                <input type="text" name="nominal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="0" required>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Keterangan</label>
                <textarea name="keterangan" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Contoh: Setoran harian ke Owner" required></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnSimpanMutasi" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Proses Mutasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rekap -->
<div id="modalRekap" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Rekap Transaksi</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>
        <div class="p-5">
            <p class="text-sm text-slate-600">Pilih format rekap transaksi:</p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <button id="btnRekapPdf" class="rounded-lg border border-slate-200 p-4 text-center hover:bg-slate-50">
                    <i class="fas fa-file-pdf text-3xl text-rose-600 mb-2"></i>
                    <span class="block text-sm font-medium">PDF</span>
                </button>
                <button id="btnRekapExcel" class="rounded-lg border border-slate-200 p-4 text-center hover:bg-slate-50">
                    <i class="fas fa-file-excel text-3xl text-emerald-600 mb-2"></i>
                    <span class="block text-sm font-medium">Excel</span>
                </button>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>

        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button id="confirmDelete"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Hapus
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Script -->
<script>
    window.transaksiConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('transaksi/fetch') ?>",
        storeUrl: "<?= site_url('transaksi/store') ?>",
        storeMutasiUrl: "<?= site_url('transaksi/store_mutasi') ?>",
        deleteUrl: "<?= site_url('transaksi/delete') ?>",
        exportPdfUrl: "<?= site_url('transaksi/export_pdf') ?>",
        exportExcelUrl: "<?= site_url('transaksi/export_excell') ?>",
        chartDataUrl: "<?= site_url('transaksi/chart_data') ?>",
        role: "<?= $role ?? '' ?>"
    };
</script>

<!-- External JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?= $this->endSection() ?>
