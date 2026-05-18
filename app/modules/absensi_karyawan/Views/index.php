<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-7xl">

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Riwayat Presensi</h1>
            <p class="text-slate-500 text-sm mt-1">Pantau rekap kehadiran harian seluruh terapis.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full lg:hidden mt-4">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= base_url('kehadiran') ?>" selected>📅 Rekap Presensi</option>
                <option value="<?= base_url('kehadiran/tambah') ?>">✍️ Input Presensi Baru</option>
                <option value="<?= base_url('kehadiran/cuti') ?>">🏖️ Cuti Karyawan</option>
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <form action="<?= base_url('kehadiran') ?>" method="GET" class="flex items-center gap-2">
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-slate-400"></i>
                    <select name="bulan" onchange="this.form.submit()" class="bg-transparent border-none text-sm font-semibold text-slate-700 focus:ring-0 p-0 cursor-pointer">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $filter_bulan == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm flex items-center gap-2">
                    <select name="tahun" onchange="this.form.submit()" class="bg-transparent border-none text-sm font-semibold text-slate-700 focus:ring-0 p-0 cursor-pointer">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>

            <a href="<?= base_url('kehadiran/export?bulan=' . $filter_bulan . '&tahun=' . $filter_tahun) ?>" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-sm py-2.5 px-4 rounded-xl transition-all shadow-sm flex items-center gap-2">
                <i class="fas fa-file-excel text-emerald-600"></i> Export
            </a>

            <a href="<?= base_url('kehadiran/tambah') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm uppercase tracking-widest py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Presensi
            </a>
        </div>
    </div>

    <!-- DUAL LAYOUT: MOBILE CARDS & DESKTOP TABLE -->
    <div class="space-y-4 md:space-y-0">
        
        <!-- MOBILE CARD FEED (Shown on Mobile < 768px) -->
        <div class="block md:hidden space-y-4">
            <?php if (empty($rekap_harian)): ?>
                <div class="bg-white border border-slate-200 rounded-3xl p-12 text-center text-slate-400 font-semibold shadow-sm">
                    <i class="fas fa-inbox text-3xl mb-3 text-slate-300 block"></i>
                    Belum ada riwayat presensi.
                </div>
            <?php else: ?>
                <?php foreach ($rekap_harian as $rekap): ?>
                    <?php
                        $bulan_indo = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $tanggal = date('d', strtotime($rekap['tanggal']));
                        $bulan = $bulan_indo[(int)date('m', strtotime($rekap['tanggal']))];
                        $tahun = date('Y', strtotime($rekap['tanggal']));
                        $dateString = "$tanggal $bulan $tahun";
                    ?>
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                        <!-- Header: Tanggal & Aksi -->
                        <div class="flex items-center justify-between border-b border-slate-50 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 text-sm uppercase tracking-tight"><?= $dateString ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Telah Diinput</p>
                                </div>
                            </div>
                            <!-- Aksi -->
                            <div class="flex items-center gap-1 bg-slate-50 rounded-xl p-1 border border-slate-100">
                                <a href="<?= base_url('kehadiran/detail/' . $rekap['tanggal']) ?>" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-white transition-all" title="Lihat Rincian">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="<?= base_url('kehadiran/presensi/' . $rekap['tanggal']) ?>" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:text-amber-500 hover:bg-white transition-all" title="Edit Data">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Grid Statistik Kehadiran -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <!-- Hadir -->
                            <div class="bg-slate-50/50 rounded-2xl p-3 border border-slate-100/50 flex flex-col items-center justify-center text-center gap-1">
                                <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Hadir</span>
                                <span class="inline-flex h-8 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 text-sm font-black border border-emerald-100 shadow-sm mt-1">
                                    <?= $rekap['total_hadir'] ?>
                                </span>
                            </div>

                            <!-- Tanpa Keterangan -->
                            <div class="bg-slate-50/50 rounded-2xl p-3 border border-slate-100/50 flex flex-col items-center justify-center text-center gap-1">
                                <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Tanpa Keterangan</span>
                                <span class="inline-flex h-8 w-12 items-center justify-center rounded-xl <?= $rekap['total_tidak_hadir'] > 0 ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-slate-100 text-slate-400 border-slate-200' ?> text-sm font-black border shadow-sm mt-1">
                                    <?= $rekap['total_tidak_hadir'] ?>
                                </span>
                            </div>

                            <!-- Izin -->
                            <div class="bg-slate-50/50 rounded-2xl p-3 border border-slate-100/50 flex flex-col items-center justify-center text-center gap-1">
                                <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Izin</span>
                                <span class="inline-flex h-8 w-12 items-center justify-center rounded-xl <?= $rekap['total_izin'] > 0 ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-slate-100 text-slate-400 border-slate-200' ?> text-sm font-black border shadow-sm mt-1">
                                    <?= $rekap['total_izin'] ?>
                                </span>
                            </div>

                            <!-- Cuti -->
                            <div class="bg-slate-50/50 rounded-2xl p-3 border border-slate-100/50 flex flex-col items-center justify-center text-center gap-1">
                                <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Cuti</span>
                                <span class="inline-flex h-8 w-12 items-center justify-center rounded-xl <?= $rekap['total_cuti'] > 0 ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-slate-100 text-slate-400 border-slate-200' ?> text-sm font-black border shadow-sm mt-1">
                                    <?= $rekap['total_cuti'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="bg-indigo-50/50 border border-indigo-100/50 rounded-2xl p-4 text-center text-xs font-bold text-indigo-700 uppercase tracking-widest shadow-sm">
                    📅 Menampilkan <?= count($rekap_harian) ?> hari kerja
                </div>
            <?php endif; ?>
        </div>

        <!-- DESKTOP TABLE (Shown on Desktop >= 768px) -->
        <div class="hidden md:block bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            <th class="p-5 pl-8">Tanggal</th>
                            <th class="p-5 text-center">Hadir</th>
                            <th class="p-5 text-center">Tanpa Keterangan</th>
                            <th class="p-5 text-center">Izin</th>
                            <th class="p-5 text-center">Cuti</th>
                            <th class="p-5 text-center pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        <?php if (empty($rekap_harian)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 font-semibold">Belum ada riwayat presensi.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekap_harian as $rekap): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="p-5 pl-8">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                                <i class="fas fa-calendar-day"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800">
                                                    <?php
                                                        $bulan_indo = [
                                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                        ];
                                                        $tanggal = date('d', strtotime($rekap['tanggal']));
                                                        $bulan = $bulan_indo[(int)date('m', strtotime($rekap['tanggal']))];
                                                        $tahun = date('Y', strtotime($rekap['tanggal']));
                                                        echo "$tanggal $bulan $tahun";
                                                    ?>
                                                </p>
                                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Telah diinput</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg font-bold">
                                            <?= $rekap['total_hadir'] ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 <?= $rekap['total_tidak_hadir'] > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-400' ?> px-3 py-1 rounded-lg font-bold">
                                            <?= $rekap['total_tidak_hadir'] ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 <?= $rekap['total_izin'] > 0 ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' ?> px-3 py-1 rounded-lg font-bold">
                                            <?= $rekap['total_izin'] ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 <?= $rekap['total_cuti'] > 0 ? 'bg-orange-50 text-orange-600' : 'bg-slate-100 text-slate-400' ?> px-3 py-1 rounded-lg font-bold">
                                            <?= $rekap['total_cuti'] ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-center pr-8">
                                        <a href="<?= base_url('kehadiran/detail/' . $rekap['tanggal']) ?>" class="text-slate-400 hover:text-indigo-600 transition-colors p-2" title="Lihat Rincian">
                                            <i class="fas fa-eye text-lg"></i>
                                        </a>
                                        <a href="<?= base_url('kehadiran/presensi/' . $rekap['tanggal']) ?>" class="text-slate-400 hover:text-amber-500 transition-colors p-2" title="Edit Data">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-5 border-t border-slate-100 flex justify-between items-center text-sm font-semibold text-slate-500">
                <span>Menampilkan <?= count($rekap_harian) ?> hari kerja</span>
            </div>
        </div>

    </div>

</div>
<?= $this->endSection(); ?>