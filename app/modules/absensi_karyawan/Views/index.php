<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-7xl">

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Riwayat Presensi</h1>
            <p class="text-slate-500 text-sm mt-1">Pantau rekap kehadiran harian seluruh terapis.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm flex items-center gap-2">
                <i class="fas fa-filter text-slate-400"></i>
                <select class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 p-0 cursor-pointer">
                    <option>Mei 2026</option>
                    <option>April 2026</option>
                </select>
            </div>

            <button class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-sm py-2.5 px-4 rounded-xl transition-all shadow-sm flex items-center gap-2">
                <i class="fas fa-file-excel text-emerald-600"></i> Export
            </button>

            <a href="<?= base_url('kehadiran/store') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm uppercase tracking-widest py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
                <i class="fas fa-plus"></i> Input Hari Ini
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="p-5 pl-8">Tanggal</th>
                        <th class="p-5 text-center">Total Hadir</th>
                        <th class="p-5 text-center">Tidak Hadir</th>
                        <th class="p-5 text-center pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    <?php if (empty($rekap_harian)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 font-bold">Belum ada riwayat presensi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekap_harian as $rekap): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="p-5 pl-8">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800"><?= date('d F Y', strtotime($rekap['tanggal'])) ?></p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Telah diinput</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5 text-center">
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg font-black">
                                        <?= $rekap['total_hadir'] ?> Orang
                                    </span>
                                </td>
                                <td class="p-5 text-center">
                                    <span class="inline-flex items-center gap-1.5 <?= $rekap['total_tidak_hadir'] > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-400' ?> px-3 py-1 rounded-lg font-black">
                                        <?= $rekap['total_tidak_hadir'] ?> Orang
                                    </span>
                                </td>
                                <td class="p-5 text-center pr-8">
                                    <a href="<?= base_url('kehadiran/detail/' . $rekap['tanggal']) ?>" class="text-slate-400 hover:text-indigo-600 transition-colors p-2" title="Lihat Rincian">
                                        <i class="fas fa-eye text-lg"></i>
                                    </a>
                                    <a href="<?= base_url('kehadiran/store/' . $rekap['tanggal']) ?>" class="text-slate-400 hover:text-amber-500 transition-colors p-2" title="Edit Data">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100 flex justify-between items-center text-sm font-bold text-slate-500">
            <span>Menampilkan 2 hari kerja</span>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>