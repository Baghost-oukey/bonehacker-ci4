<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8 max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800 tracking-tight" id="dynamicTitle">Statistik Keluhan</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan Statistik Dan Rekam Medis Pasien </p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE & TABLET -->
        <div class="w-full lg:hidden mt-2">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('statistiktag') ?>" selected>Keluhan & Medis</option>
                <option value="<?= site_url('statistik') ?>">Riwayat Pasien</option>
                <option value="<?= site_url('statistikresource') ?>">Sosial Media</option>
                <option value="<?= site_url('statistikresult') ?>">Hasil Pemeriksaan</option>
                <option value="<?= site_url('statistikgender') ?>">Jenis Kelamin</option>
                <option value="<?= site_url('statistikdaerah') ?>">Daerah</option>
            </select>
        </div>
    </div>

    <div class="flex flex-col gap-6">

        <!-- FILTER MODAL -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                <div class="text-sm font-medium text-slate-600 flex items-center gap-2">
                    <i class="fas fa-filter text-indigo-500"></i> Filter Data
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto lg:justify-end">

                    <!-- FILTER DATE -->
                    <div id="rangefilter" class="flex items-center justify-between gap-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 cursor-pointer transition-all min-w-60 relative">
                        <div class="flex items-center gap-2 pointer-events-none">
                            <i class="fas fa-calendar-alt text-indigo-600"></i>
                            <span id="selected-date-text"></span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                    <!-- FILTER KELUAHAN | RIWAYAT -->
                    <select id="selecttag" class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl px-4 py-2.5 outline-none cursor-pointer min-w-35 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="complaint">Keluhan</option>
                        <option value="medhis">Riwayat Medis</option>
                    </select>

                    <!-- FILTER DAY -->
                    <!-- <select id="selectfilter" class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl px-4 py-2.5 outline-none cursor-pointer min-w-32.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="daily">Hari</option>
                        <option value="monthly">Bulan</option>
                        <option value="yearly">Tahun</option>
                    </select> -->

                    <!-- FILTER WILAYAH -->
                    <select id="regionSelect" name="regionSelect" class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl px-4 py-2.5 outline-none cursor-pointer min-w-45 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <?php if (isset($regions_patient) && !empty($regions_patient) && $regions_patient !== 'all'): ?>
                            <?php if (is_array($regions_patient) && count($regions_patient) > 1): ?>
                                <option value="">Semua Wilayah</option>
                            <?php endif; ?>
                            <?php foreach ($wilayah as $region): ?>
                                <?php if (in_array($region->id, (array)$regions_patient)): ?>
                                    <option value="<?= $region->id ?>"><?= esc($region->name) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Semua Wilayah</option>
                            <?php foreach ($wilayah as $region): ?>
                                <option value="<?= $region->id ?>" <?= (isset($selected_region) && $selected_region == $region->id) ? 'selected' : '' ?>>
                                    <?= esc($region->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLE KETERANGAN STATISTIK -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden w-full max-w-6xl mx-auto transition-all hover:shadow-md">
    <div class="p-6 border-b border-slate-100 bg-white">
        <div class="flex items-center justify-between">
            <h5 class="text-base font-bold text-slate-800 m-0" id="heading">Statistik Keluhan</h5>
            <div id="table-search-container"></div>
        </div>
    </div>
    <div class="overflow-x-auto w-full">
        <table id="statisticTable" class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-0">Nama Tag / Keluhan</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider border-0 text-center">Total Frekuensi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-slate-700 font-medium"></tbody>
        </table>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.statistikConfig = {
        fetchUrl: "<?= base_url('statistiktag/fetch_statistics') ?>"
    };
</script>
<?= $this->endSection() ?>