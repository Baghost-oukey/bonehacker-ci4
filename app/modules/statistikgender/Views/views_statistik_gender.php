<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8 max-w-350 mx-auto">
    <!-- HEADER -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight"><?= esc($title) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Laporan distribusi dan statistik pasien berdasarkan jenis kelamin.</p>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <!-- MODAL FILTER -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 lg:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
                <div class="w-full">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Rentang Waktu Analisis</label>
                    <div id="reportrange" class="flex items-center justify-between gap-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 cursor-pointer transition-all w-full">
                        <div class="flex items-center gap-2 pointer-events-none text-indigo-500">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="text-slate-700"></span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Lokasi Cabang</label>
                    <select id="region_id" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none cursor-pointer w-full focus:ring-2 focus:ring-indigo-500/20 transition-all">
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