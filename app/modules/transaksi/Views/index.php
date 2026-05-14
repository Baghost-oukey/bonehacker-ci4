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

            <button type="button" data-modal-open="modalTambah"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Transaksi Baru
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <!-- Sisa Saldo -->
        <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">SISA SALDO</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50">
                    <i class="fas fa-wallet text-teal-600 text-sm"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-semibold tracking-tight text-slate-900 truncate" id="todayBalance">
                    Rp <?= number_format($today_balance, 0, ',', '.') ?>
                </h3>
            </div>
            <p class="mt-4 text-xs text-slate-500">Saldo yang dipegang admin hari ini</p>
        </div>

        <?php if (in_array($role, ['superadmin', 'owner'])): ?>
            <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">PENGELUARAN GLOBAL</p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50">
                        <i class="fas fa-exchange-alt text-rose-600 text-sm"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-semibold tracking-tight text-rose-600 truncate" id="totalExpense">
                        Rp <?= number_format($total_expense, 0, ',', '.') ?>
                    </h3>
                </div>
                <p class="mt-4 text-xs text-slate-500">*Data semua wilayah</p>
            </div>
        <?php endif; ?>

        <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm border border-slate-200/50 ">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">TOTAL PENDAPATAN</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <i class="fas fa-chart-line text-emerald-600 text-sm"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-semibold tracking-tight text-slate-900 truncate" id="totalIncome">
                    Rp <?= number_format($total_income, 0, ',', '.') ?>
                </h3>
            </div>
            <p class="mt-4 text-xs text-slate-500">
                <span class="font-medium text-emerald-600" id="percentageChange">
                    <i class="fas fa-arrow-up mr-1"></i><span id="percentageValue">12.5</span>%
                </span>
                <span class="text-slate-400 ml-1">dari bulan lalu</span>
            </p>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Riwayat Transaksi</h3>
                <p class="text-sm text-slate-500">Data transaksi keuangan klinik</p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari transaksi..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-slate-400 text-base"></i>
                        <input type="date" id="filter_date_start"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                            placeholder="Dari Tanggal">
                        <span class="text-slate-400">-</span>
                        <input type="date" id="filter_date_end"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                            placeholder="Sampai Tanggal">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="tableTransaksi" class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">No</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Cabang</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Keterangan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Ditambahkan Oleh</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nominal</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <!-- Data ditarik via AJAX -->
                </tbody>
            </table>
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

            <!-- Nominal -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nominal (Rp)</label>
                <input type="number" name="nominal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" placeholder="0" required>
            </div>

            <!-- Keterangan -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Keterangan</label>
                <textarea name="keterangan" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" placeholder="Contoh: Pembayaran Pasien A atau Biaya Listrik"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnSimpan" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan Transaksi</button>
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
