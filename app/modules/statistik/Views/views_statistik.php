<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="w-full px-4 py-4 md:py-6 space-y-6">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">Riwayat Pasien</h1>
            <p class="text-sm text-slate-500 mt-1">Statistik Pasien Berdasarkan Kunjungan Pasien</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE & TABLET -->
        <div class="w-full lg:hidden mt-2">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('statistiktag') ?>">Keluhan & Medis</option>
                <option value="<?= site_url('statistik') ?>" selected>Riwayat Pasien</option>
                <option value="<?= site_url('statistikresource') ?>">Sosial Media</option>
                <option value="<?= site_url('statistikresult') ?>">Hasil Pemeriksaan</option>
                <option value="<?= site_url('statistikgender') ?>">Jenis Kelamin</option>
                <option value="<?= site_url('statistikdaerah') ?>">Daerah</option>
            </select>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center w-full md:w-auto">
            <div id="reportrange" class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 cursor-pointer transition-all w-full sm:min-w-[240px] justify-between shadow-sm">
                <div class="flex items-center gap-2 pointer-events-none text-indigo-600">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="text-slate-700"></span>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 pointer-events-none"></i>
            </div>

            <select id="region_id" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:ring-2 focus:ring-indigo-500/20 w-full sm:w-64 shadow-sm">
                <?php if (isset($regions_patient) && !empty($regions_patient)): ?>
                    <?php foreach ($wilayah as $v): ?>
                        <?php if ($v->id == $regions_patient[0]): ?>
                            <option value="<?= $v->id ?>" selected><?= esc($v->name) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">-- Semua Wilayah --</option>
                    <?php foreach ($wilayah as $v): ?>
                        <option value="<?= $v->id ?>"><?= esc($v->name) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>


    <!-- CARD KETERANGAN PASIEN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 border-l-4 border-l-slate-700 relative overflow-hidden group hover:border-slate-300 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mt-1">Total Volume Pasien</p>
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-600 border border-slate-100 shadow-sm shrink-0">
                    <i class="fas fa-users text-sm"></i>
                </div>
            </div>
            <h3 class="text-4xl font-black text-slate-800 tracking-tight" id="totalCount">-</h3>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 border-l-4 border-l-emerald-500 hover:border-emerald-300 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-black text-emerald-600 uppercase tracking-widest mt-1">Pasien Baru</p>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 border border-emerald-100 shadow-sm shrink-0">
                    <i class="fas fa-user-plus text-sm"></i>
                </div>
            </div>
            <div class="flex flex-col items-start gap-2">
                <h3 class="text-4xl font-black text-slate-800 tracking-tight" id="newPatientsCount">-</h3>
                <div class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider" id="percBaru">Memuat...</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 border-l-4 border-l-indigo-500 hover:border-indigo-300 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-black text-indigo-600 uppercase tracking-widest mt-1">Pasien Lama</p>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 border border-indigo-100 shadow-sm shrink-0">
                    <i class="fas fa-history text-sm"></i>
                </div>
            </div>
            <div class="flex flex-col items-start gap-2">
                <h3 class="text-4xl font-black text-slate-800 tracking-tight" id="oldPatientsCount">-</h3>
                <div class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider" id="percLama">Memuat...</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 border-l-4 border-l-amber-500 hover:border-amber-300 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-black text-amber-600 uppercase tracking-widest mt-1">Rata-rata Harian</p>
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 border border-amber-100 shadow-sm shrink-0">
                    <i class="fas fa-chart-line text-sm"></i>
                </div>
            </div>
            <h3 class="text-4xl font-black text-slate-800 tracking-tight" id="avgPerDay">-</h3>
            <p class="text-[11px] font-bold text-slate-400 mt-2 uppercase tracking-tight">Pasien per Hari</p>
        </div>
    </div>


    <!-- TABLE KETERANGAN PASIEN -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden transition-all hover:shadow-md">
        <div class="p-6 border-b border-slate-100 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h4 class="text-base font-bold text-slate-800">Detail Aktivitas per Wilayah</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Data rincian performa operasional pasien baru dan lama.</p>
            </div>
        </div>

        <!-- Mobile Analysis Container (KODE KITA) -->
        <div id="mobile-analysis-container" class="md:hidden divide-y divide-slate-100">
            <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                Memuat data statistik...
            </div>
        </div>

        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold">Wilayah / Cabang</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Rata² / Hari</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Total Pasien</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Pasien Lama</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Pasien Baru</th>
                        <th class="px-6 py-3.5 font-semibold text-center">% Lama</th>
                        <th class="px-6 py-3.5 font-semibold text-center">% Baru</th>
                    </tr>
                </thead>
                <tbody id="tbody-analysis" class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data statistik...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <!-- CHART CABANG -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden transition-all hover:shadow-md">
        <div class="p-6 border-b border-slate-100 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h4 class="text-base font-bold text-slate-800">Volume Pasien per Cabang</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Perbandingan distribusi kunjungan antar unit operasional.</p>
            </div>
        </div>
        <div class="p-6 bg-white">
            <div class="relative h-105 w-full pb-4">
                <canvas id="statisticChart"></canvas>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.statistikPasienConfig = {
        fetchUrl: "<?= site_url('statistik/fetch_analysis') ?>",
        csrfTokenName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>
<?= $this->endSection() ?>