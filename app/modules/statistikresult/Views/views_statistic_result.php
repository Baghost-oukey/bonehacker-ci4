<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8 max-w-350 mx-auto">

    <!-- HEADER -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Hasil Pemeriksaan</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan distribusi dan statistik detail hasil pemeriksaan medis.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden mt-2">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('statistiktag') ?>">Keluhan & Medis</option>
                <option value="<?= site_url('statistik') ?>">Riwayat Pasien</option>
                <option value="<?= site_url('statistikresource') ?>">Sosial Media</option>
                <option value="<?= site_url('statistikresult') ?>" selected>Hasil Pemeriksaan</option>
                <option value="<?= site_url('statistikgender') ?>">Jenis Kelamin</option>
                <option value="<?= site_url('statistikdaerah') ?>">Daerah</option>
            </select>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <!-- FILTER DATA -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row gap-4 items-end justify-between">

                <div class="w-full lg:w-5/12">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Rentang Waktu Analisis</label>
                    <div id="rangefilter" class="flex items-center justify-between gap-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 cursor-pointer transition-all">
                        <div class="flex items-center gap-2 pointer-events-none text-indigo-500">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="text-slate-700"></span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-end">
                    <div class="w-full sm:w-64">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Lokasi Cabang</label>
                        <select id="regionSelect" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:ring-2 focus:ring-indigo-500/20 w-full transition-all">
                            <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                                <?php foreach ($wilayah as $value): ?>
                                    <?php if (in_array($value->id, (array)$regions_patient)): ?>
                                        <option value="<?= $value->id ?>" selected><?= esc($value->name) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Semua Wilayah</option>
                                <?php foreach ($wilayah as $value): ?>
                                    <option value="<?= $value->id ?>" <?= (isset($selected_region) && $selected_region == $value->id) ? 'selected' : '' ?>>
                                        <?= esc($value->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE DATA  -->
        <div id="chartContainer" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden w-full transition-all hover:shadow-md">
            <div class="p-5 border-b border-slate-100 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h5 class="text-base font-bold text-slate-800 m-0" id="heading">Detail Pemeriksaan</h5>
                <div id="custom-search-container" class="w-full sm:w-auto"></div>
            </div>

            <div class="overflow-x-auto w-full">
                <table id="statisticTable" class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <th class="px-6 py-4 border-0 w-[80%]">Hasil Pemeriksaan / Tag</th>
                            <th class="px-6 py-4 border-0 text-center w-[20%]">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 font-medium">
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.statistikResultConfig = {
        fetchUrl: "<?= base_url('statistikresult/fetch_statistics') ?>"
    };
</script>
<?= $this->endSection() ?>