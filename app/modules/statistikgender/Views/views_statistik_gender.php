<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="statistikGenderPage" class="w-full px-4 py-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900"><?= esc($title) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Laporan distribusi dan statistik pasien berdasarkan jenis kelamin.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden mt-2">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('statistiktag') ?>">Keluhan & Medis</option>
                <option value="<?= site_url('statistik') ?>">Riwayat Pasien</option>
                <option value="<?= site_url('statistikresource') ?>">Sosial Media</option>
                <option value="<?= site_url('statistikresult') ?>">Hasil Pemeriksaan</option>
                <option value="<?= site_url('statistikgender') ?>" selected>Jenis Kelamin</option>
                <option value="<?= site_url('statistikdaerah') ?>">Daerah</option>
            </select>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <!-- MODAL FILTER -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:items-end">
                <div class="w-full">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Rentang Waktu Analisis</label>
                    <div id="reportrange" class="flex items-center justify-between gap-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 cursor-pointer transition-colors w-full shadow-sm">
                        <div class="flex items-center gap-2 pointer-events-none text-indigo-600">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="text-slate-700"></span>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Lokasi Cabang</label>
                    <select id="region_id" class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl px-4 py-2.5 outline-none cursor-pointer w-full focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm transition-all">
                        <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                            <?php foreach ($wilayah as $value): ?>
                                <?php if (in_array($value->id, (array)$regions_patient)): ?>
                                    <option value="<?= $value->id ?>" selected><?= esc($value->name) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Semua Wilayah</option>
                            <?php foreach ($wilayah as $value): ?>
                                <option value="<?= $value->id ?>"><?= esc($value->name) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- CHART STATISTIK DATA -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col w-full overflow-hidden transition-all hover:shadow-md">
            <div class="p-5 border-b border-slate-100 bg-white">
                <h4 class="text-base font-bold text-slate-800 m-0" id="chartTitle">Grafik Komparasi Jenis Kelamin</h4>
            </div>
            <div class="p-6 w-full">
                <div class="relative w-full h-100">
                    <canvas id="statisticChart"></canvas>
                </div>
            </div>
        </div>
        
    </div>

</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>

<script>
    window.statistikGenderConfig = {
        fetchUrl: "<?= base_url('statistikgender/fetch_statistics') ?>"
        
    };
</script>
<?= $this->endSection() ?>