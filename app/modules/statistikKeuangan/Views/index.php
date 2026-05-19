<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="statistikKeuanganPage" class="w-full py-4 md:py-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight text-uppercase">Analisis Keuangan</h2>
            <p class="text-sm text-slate-500 font-medium">Monitoring arus kas dan alokasi biaya secara real-time.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center w-full md:w-auto">
            <!-- Filter Region -->
            <?php if (in_array($role, ['superadmin', 'owner'])): ?>
                <div class="relative w-full sm:w-auto">
                    <select id="chartRegionFilter" aria-label="Filter Cabang" class="w-full appearance-none rounded-xl border border-slate-200 bg-white pl-4 pr-10 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none">
                        <option value="all">Semua Cabang</option>
                        <?php if (!empty($list_regions)): ?>
                            <?php foreach ($list_regions as $r): ?>
                                <option value="<?= esc($r->id) ?>"><?= esc($r->name) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fas fa-map-marker-alt text-xs"></i>
                    </div>
                </div>
            <?php endif; ?>

            <!-- DATETIME & EXPORT -->
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200 flex-1 sm:flex-none" id="timeFilterGroup">
                    <button data-value="7" class="flex-1 sm:flex-none time-filter-btn px-4 py-1.5 text-[10px] font-bold rounded-lg transition-all active-filter bg-white text-slate-900 shadow-sm">7 Hari</button>
                    <button data-value="30" class="flex-1 sm:flex-none time-filter-btn px-4 py-1.5 text-[10px] font-bold text-slate-500 rounded-lg transition-all hover:text-slate-700">30 Hari</button>
                    <button data-value="90" class="flex-1 sm:flex-none time-filter-btn px-4 py-1.5 text-[10px] font-bold text-slate-500 rounded-lg transition-all hover:text-slate-700">3 Bulan</button>
                </div>

                <div class="flex gap-2 shrink-0">
                    <button id="btnExportPdf" title="Export PDF" class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-100 bg-white text-rose-600 transition hover:bg-rose-50 shadow-sm active:scale-95">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                    <button id="btnExportExcel" title="Export Excel" class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 bg-white text-emerald-600 transition hover:bg-emerald-50 shadow-sm active:scale-95">
                        <i class="fas fa-file-excel"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DROPDOWN NAVIGASI MOBILE -->
    <div class="w-full lg:hidden">
        <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
            <option value="<?= site_url('kas') ?>">📊 Arus Kas</option>
            <option value="<?= site_url('statistikkeuangan') ?>" selected>📈 Statistik Keuangan</option>
            <option value="<?= site_url('kas/categories') ?>">⚙️ Master Kategori</option>
        </select>
    </div>

    <!-- Charts  -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- CHART 1 -->
        <div class="lg:col-span-2 rounded-3xl bg-white p-6 shadow-sm border border-slate-200/60 flex flex-col relative min-h-105">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 shadow-inner">
                        <i class="fas fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 leading-none">Statistik Keuangan</h4>
                        <p id="chartPeriodLabel" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1.5 italic">Memuat data...</p>
                    </div>
                </div>
                <!-- Legend -->
                <div class="hidden sm:flex items-center gap-4 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                        <span class="text-[10px] font-extrabold text-slate-500 uppercase">Pemasukan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        <span class="text-[10px] font-extrabold text-slate-500 uppercase">Pengeluaran</span>
                    </div>
                </div>
            </div>
            <!-- JIKA DATA KOSONG -->
            <div id="emptyTrendState" class="hidden absolute inset-0 flex flex-col items-center justify-center bg-white/90 z-20 rounded-3xl">
                <div class="bg-slate-50 p-6 rounded-full mb-4 text-slate-200"><i class="fas fa-file-invoice-dollar text-5xl"></i></div>
                <h5 class="text-slate-700 font-bold text-lg">Belum Ada Transaksi</h5>
                <p class="text-slate-400 text-sm mt-1 text-center px-12">Tidak ditemukan data pada periode atau cabang ini.</p>
            </div>
            <div class="relative flex-grow w-full"><canvas id="trendChart"></canvas></div>
        </div>

        <!-- DONUT CHART -->
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200/60 flex flex-col relative min-h-[420px]">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 shadow-inner">
                    <i class="fas fa-pie-chart text-lg"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-slate-800 leading-none">Alokasi Biaya</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1.5">Kategori Terbesar</p>
                </div>
            </div>
            <!-- JIKA DATA KOSONG -->
            <div id="emptyCategoryState" class="hidden absolute inset-0 flex flex-col items-center justify-center bg-white/90 z-20 rounded-3xl text-center px-8">
                <div class="bg-slate-50 p-6 rounded-full mb-4 text-slate-200"><i class="fas fa-chart-pie text-5xl"></i></div>
                <h5 class="text-slate-700 font-bold text-lg">Data Kosong</h5>
                <p class="text-slate-400 text-sm mt-1">Belum ada catatan pengeluaran.</p>
            </div>
            <div class="relative flex-grow flex items-center justify-center w-full"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.financeStatsConfig = {
        url: '<?= base_url("statistikkeuangan/get_chart_data") ?>',
        exportExcelUrl: '<?= base_url("statistikkeuangan/export_excel") ?>',
        exportPdfUrl: '<?= base_url("statistikkeuangan/export_pdf") ?>'
    };
</script>
<?= $this->endSection() ?>