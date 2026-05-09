<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8 max-w-300 mx-auto font-sans">
    
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Statistik Wilayah & Daerah</h1>
            <p class="text-sm text-slate-500 mt-1">Pemetaan distribusi rekam medis berdasarkan geografi dan periode waktu.</p>
        </div>
    </div>
</div>


<div class="flex flex-col gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 lg:p-6 transition-all hover:border-indigo-100">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col lg:flex-row gap-4 items-end">
                    <div class="w-full lg:w-4/12">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Rentang Waktu</label>
                        <div id="reportrange" class="flex items-center justify-between gap-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 cursor-pointer transition-all">
                            <div class="flex items-center gap-2 pointer-events-none text-indigo-500">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="text-slate-700"></span>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="w-full lg:w-3/12">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Filter Tampilan</label>
                        <select id="statisticFilter" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:ring-2 focus:ring-indigo-500/20 w-full transition-all">
                            <option value="daily">Hari</option>
                            <option value="weekly">Minggu</option>
                            <option value="monthly">Bulan</option>
                            <option value="yearly">Tahun</option>
                        </select>
                    </div>

                    <div class="w-full lg:w-5/12">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kantor Cabang</label>
                        <select id="region_id" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:ring-2 focus:ring-indigo-500/20 w-full transition-all">
                            <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                                <?php foreach ($wilayah as $value): ?>
                                    <?php if (in_array($value->id, (array)$regions_patient)): ?>
                                        <option value="<?= $value->id ?>" selected><?= esc($value->name) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Semua Wilayah</option>
                                <?php foreach ($wilayah as $value): ?>
                                    <option value="<?= $value->id ?>" <?= (isset($region) && $region == $value->id) ? 'selected' : '' ?>>
                                        <?= esc($value->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-slate-50 pt-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kabupaten / Kota</label>
                        <select id="kabupaten_id" class="select2 w-full">
                            <option value="">Pilih Kabupaten/Kota</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kecamatan</label>
                        <select id="kecamatan_id" class="select2 w-full" disabled>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Desa / Kelurahan</label>
                        <select id="desa_id" class="select2 w-full" disabled>
                            <option value="">Pilih Desa/Kelurahan</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden transition-all hover:shadow-md">
            <div class="p-6 border-b border-slate-100 bg-white/50 backdrop-blur-sm">
                <h4 class="text-base font-bold text-slate-800" id="chartTitle">Analisis Tren Rekam Medis</h4>
            </div>
            <div class="p-6">
                <div id="chartContainer" class="relative h-112.5 w-full">
                    <canvas id="statisticChart"></canvas>
                </div>
            </div>
        </div>

    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.statistikDaerahConfig = {
        fetchKabupatenUrl: "<?= base_url('statistikdaerah/fetch_kabupaten') ?>",
        fetchKecamatanUrl: "<?= base_url('statistikdaerah/fetch_kecamatan') ?>",
        fetchDesaUrl: "<?= base_url('statistikdaerah/fetch_desa') ?>",
        fetchStatisticsUrl: "<?= base_url('statistikdaerah/fetch_statistics') ?>"
    };
</script>
<?= $this->endSection() ?>