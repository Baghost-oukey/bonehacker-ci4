<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="kasPage" class="container mx-auto px-4 py-6 space-y-8">

    <!-- HEADER & ACTION BAR -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">Manajemen Arus Kas</h2>
            <p class="text-sm text-slate-500 mt-1">Pantau dan kelola riwayat pemasukan, pengeluaran biasa, dan harian.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- FILTER WILAYAH -->
            <?php if (in_array($role, ['owner', 'superadmin'])): ?>
                <div class="flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-slate-400 text-sm"></i>
                    <select id="filterRegionKas" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all">
                        <option value="all">Semua Wilayah</option>
                        <?php foreach ($list_regions as $reg): ?>
                            <option value="<?= esc($reg['id'], 'attr') ?>" <?= session()->get('active_region') == $reg['id'] ? 'selected' : '' ?>>
                                <?= esc($reg['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="csrfToken" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                </div>
            <?php endif; ?>

            <!-- TOMBOL REKAP -->
            <button type="button" data-modal-open="modalRekap"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                <i class="fas fa-print text-slate-400"></i>
                Rekap
            </button>
        </div>
    </div>

    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- CARD PENDAPATAN TODAY -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase">Pemasukan Hari Ini</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
            <h3 class="mt-4 text-2xl font-bold text-emerald-600">
                Rp <?= number_format($today_income, 0, ',', '.') ?>
            </h3>
            <p class="mt-1 text-xs text-slate-400">*Total uang masuk hari ini</p>
        </div>

        <!-- CARD PENGELUARAN TODAY -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase">Pengeluaran Hari Ini</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <h3 class="mt-4 text-2xl font-bold text-rose-600">
                Rp <?= number_format($today_expense, 0, ',', '.') ?>
            </h3>
            <p class="mt-1 text-xs text-slate-400">*Total uang keluar hari ini</p>
        </div>

        <!-- CARD TOTAL SALDO TODAY -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase">Saldo Hari Ini</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <h3 class="mt-4 text-2xl font-bold <?= $today_balance < 0 ? 'text-rose-600' : 'text-slate-800' ?>">
                Rp <?= number_format($today_balance, 0, ',', '.') ?>
            </h3>
            <p class="mt-1 text-xs text-slate-400">*Sisa uang bersih hari ini</p>
        </div>

        <!-- TOTAL TRANSAKSI ALL TIME -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase">Total Pendapatan</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <h3 class="mt-4 text-2xl font-bold text-slate-800">
                Rp <?= number_format($total_income, 0, ',', '.') ?>
            </h3>
            <p class="mt-1 text-xs text-slate-400">*Akumulasi semua wilayah</p>
        </div>

    </div>

    <!-- TABS NAVIGATION -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="border-b border-slate-100 px-6">
            <nav class="-mb-px flex space-x-8" id="kas-tabs" aria-label="Tabs">
                <button data-tab="pemasukan"
                    class="tab-btn active text-teal-600 border-teal-600 border-b-2 py-5 px-1 text-sm font-bold whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-down text-xs"></i> Data Pemasukan
                </button>
                <button data-tab="pengeluaran"
                    class="tab-btn text-slate-400 border-transparent border-b-2 py-5 px-1 text-sm font-bold whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-up text-xs"></i> Data Pengeluaran
                </button>
                <button data-tab="rutinan"
                    class="tab-btn text-slate-400 border-transparent border-b-2 py-5 px-1 text-sm font-bold whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-calendar-check text-xs"></i> Pengeluaran Harian
                </button>
            </nav>
        </div>

        <!-- Konten Tab -->
        <div class="p-6">
            <div id="tab-content-pemasukan" class="tab-pane block">
                <?= $this->include('App\modules\kas\Views\components\pemasukkan_views') ?>
            </div>

            <div id="tab-content-pengeluaran" class="tab-pane hidden">
                <?= $this->include('App\modules\kas\Views\components\pengeluaran_views') ?>
            </div>

            <div id="tab-content-rutinan" class="tab-pane hidden">
                <?= $this->include('App\modules\kas\Views\components\pengeluaranrutinan_views') ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rekap -->
<div id="modalRekap" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
    <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <h5 class="text-xl font-bold text-slate-800">Cetak Laporan Kas</h5>
            <button type="button" data-modal-close
                class="rounded-xl p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-8">
            <p class="text-sm text-slate-500 text-center mb-6">Pilih format laporan yang Anda butuhkan untuk periode wilayah aktif saat ini.</p>
            <div class="grid grid-cols-2 gap-4">
                <button id="btnRekapPdf" class="group flex flex-col items-center gap-3 rounded-2xl border border-slate-100 p-6 transition-all hover:bg-rose-50 hover:border-rose-100">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-rose-50 text-rose-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-pdf text-2xl"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Format PDF</span>
                </button>

                <button id="btnRekapExcel" class="group flex flex-col items-center gap-3 rounded-2xl border border-slate-100 p-6 transition-all hover:bg-emerald-50 hover:border-emerald-100">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-excel text-2xl"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Format Excel</span>
                </button>
            </div>

            <div class="mt-8">
                <button type="button" data-modal-close
                    class="w-full rounded-xl border border-slate-200 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                    Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.kasConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        urlPemasukan: "<?= site_url('kas/get_data_pemasukan') ?>",
        urlPengeluaran: "<?= site_url('kas/get_data_pengeluaran') ?>",
        urlPengeluaranHarian: "<?= site_url('kas/get_data_pengeluaran_harian') ?>",
        urlMasterHarian: "<?= site_url('kas/get_master_harian') ?>",
        urlSimpanTransaksi: "<?= site_url('kas/simpan_transaksi') ?>",
        urlBayarHarian: "<?= site_url('kas/bayar_pengeluaran_harian') ?>",
        urlSimpanMaster: "<?= site_url('kas/simpan_master_harian') ?>",
        urlHapusMaster: "<?= site_url('kas/hapus_master_harian') ?>"
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?= $this->endSection() ?>