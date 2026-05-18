<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<style>
    .d-none {
        display: none !important;
    }

    .d-flex {
        display: flex !important;
    }
</style>

<div class="w-full px-4 py-4 md:py-6 space-y-6 overflow-x-hidden">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900"><?= esc($title) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Statistik Sumber Informasi Yang didapatkan Pasien pada saat Pendaftaran</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE & TABLET -->
        <div class="w-full lg:hidden mt-2">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('statistiktag') ?>">Keluhan & Medis</option>
                <option value="<?= site_url('statistik') ?>">Riwayat Pasien</option>
                <option value="<?= site_url('statistikresource') ?>" selected>Sosial Media</option>
                <option value="<?= site_url('statistikresult') ?>">Hasil Pemeriksaan</option>
                <option value="<?= site_url('statistikgender') ?>">Jenis Kelamin</option>
                <option value="<?= site_url('statistikdaerah') ?>">Daerah</option>
            </select>
        </div>
    </div>
    <!-- END -->

    <div class="flex flex-col gap-6">

        <!-- MODAL FILTER -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 md:p-6">
            <div class="flex flex-col md:flex-row gap-4 md:items-end justify-between">
                <div class="w-full md:w-5/12">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Rentang Waktu Analisis</label>
                    <div id="reportrange" class="flex items-center justify-between gap-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 cursor-pointer transition-colors w-full shadow-sm">
                        <div class="flex items-center gap-2 pointer-events-none text-indigo-600">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="text-slate-700"></span>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
                <div class="w-full md:w-4/12">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Lokasi Cabang</label>
                    <select id="filter_region" class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl px-4 py-2.5 outline-none cursor-pointer w-full focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm">
                        <option value="">Semua Wilayah</option>
                        <?php foreach ($wilayah as $w): ?>
                            <option value="<?= $w->id ?>"><?= esc($w->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-full md:w-3/12">
                    <button id="btn-update-analysis" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl px-4 py-2.5 transition-colors flex items-center justify-center gap-2 shadow-sm active:scale-95">
                        <i class="fas fa-sync-alt text-slate-400"></i> Update Analisis
                    </button>
                </div>
            </div>
        </div>
        <!-- END -->

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- CHART STATISTIK PASIEN -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col h-full min-h-[350px] md:min-h-100">
                <div class="p-6 border-b border-slate-100">
                    <h4 class="text-base font-bold text-slate-800">Proporsi Saluran</h4>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-center relative">
                    <div id="chart-container" class="relative h-75 w-full">
                        <canvas id="marketingPieChart"></canvas>
                    </div>

                    <div id="no-data-message" class="d-none flex-col items-center justify-center h-75 absolute inset-0 w-full">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-chart-pie text-3xl text-slate-300"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-500">Data Masih Kosong</p>
                        <p class="text-xs text-slate-400 mt-1">Belum ada pasien di rentang ini.</p>
                    </div>
                </div>
            </div>
            <!-- END -->


            <!-- TABLE DATA PASIEN -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-full">
                <div class="p-6 border-b border-slate-100 bg-white">
                    <h4 class="text-base font-bold text-slate-800">Ranking Efektivitas Saluran</h4>
                </div>

                <!-- Mobile Ranking Container (KODE KITA) -->
                <div id="mobile-marketing-container" class="md:hidden divide-y divide-slate-100">
                    <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                        Memuat data analisis...
                    </div>
                </div>

                <div class="overflow-x-auto w-full p-0 flex-1 hidden md:block">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold border-0 w-1/2">Saluran / Media</th>
                                <th class="px-6 py-3.5 font-semibold border-0 text-center w-1/4">Total Pasien</th>
                                <th class="px-6 py-3.5 font-semibold border-0 text-center w-1/4">Kontribusi</th>
                            </tr>
                        </thead>
                        <tbody id="table-marketing-body" class="divide-y divide-slate-100 text-sm text-slate-700">
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END -->
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.resourceConfig = {
        fetchUrl: "<?= base_url('statistikresource/get_marketing_data') ?>"
    };
</script>
<?= $this->endSection() ?>