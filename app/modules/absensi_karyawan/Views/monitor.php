<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="monitorPresensiPage" class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 uppercase tracking-tight">Presensi Saya</h1>
            <p class="text-sm font-semibold text-slate-500 mt-1 uppercase tracking-widest">Pantau kehadiran harian Anda yang menentukan gaji.</p>
        </div>
        
        <!-- Filter Bulan & Tahun -->
        <form action="" method="get" class="flex flex-wrap items-center gap-2">
            <select name="bulan" class="bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all uppercase tracking-wider">
                <?php
                $bulan_list = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                foreach ($bulan_list as $val => $label) :
                    $selected = ($filter_bulan == $val) ? 'selected' : '';
                    echo "<option value=\"$val\" $selected>$label</option>";
                endforeach;
                ?>
            </select>
            <select name="tahun" class="bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all uppercase tracking-wider">
                <?php
                $current_year = date('Y');
                for ($y = $current_year; $y >= $current_year - 2; $y--) :
                    $selected = ($filter_tahun == $y) ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                endfor;
                ?>
            </select>
            <button type="submit" class="bg-teal-600 text-white p-2.5 rounded-xl hover:bg-teal-700 transition-colors">
                <i class="fas fa-search px-1"></i>
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Cards -->
        <?php
        $total_hadir = 0;
        $total_tidak_hadir = 0;
        foreach ($presensi as $p) {
            if ($p['status'] === 'Hadir') $total_hadir++;
            else $total_tidak_hadir++;
        }
        ?>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Hadir</p>
                    <h4 class="text-2xl font-bold text-slate-800 tracking-tight"><?= $total_hadir ?> <span class="text-xs text-slate-400 font-semibold uppercase ml-1">Hari</span></h4>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                    <i class="fas fa-user-times text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Absen / Izin</p>
                    <h4 class="text-2xl font-bold text-slate-800 tracking-tight"><?= $total_tidak_hadir ?> <span class="text-xs text-slate-400 font-semibold uppercase ml-1">Hari</span></h4>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-teal-600 p-6 rounded-3xl shadow-lg shadow-teal-200 border border-teal-500 flex items-center justify-between">
            <div class="flex items-center gap-4 text-white">
                <div class="h-12 w-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-white/70 uppercase tracking-widest">Status Gaji Harian</p>
                    <p class="text-sm font-medium text-white mt-0.5">Kehadiran menentukan akumulasi gaji harian Anda.</p>
                </div>
            </div>
            <a href="<?= base_url('gaji/monitor') ?>" class="bg-white text-teal-600 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-colors shrink-0">
                Cek Gaji
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
            <div>
                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Detail Kehadiran</h3>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-0.5">Daftar presensi untuk periode terpilih.</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterangan</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Jam Input</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($presensi)) : ?>
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="h-16 w-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-200 text-2xl">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <p class="text-xs font-bold text-slate-300 uppercase tracking-widest">Tidak ada data presensi bulan ini</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($presensi as $p) : ?>
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 uppercase tracking-tight"><?= date('d F Y', strtotime($p['tanggal'])) ?></span>
                                        <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest"><?= date('l', strtotime($p['tanggal'])) ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <?php if ($p['status'] === 'Hadir') : ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 uppercase tracking-widest border border-emerald-100">
                                            Hadir
                                        </span>
                                    <?php else : ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 uppercase tracking-widest border border-rose-100">
                                            <?= esc($p['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-xs font-semibold text-slate-500"><?= $p['keterangan'] ? esc($p['keterangan']) : '-' ?></span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= date('H:i', strtotime($p['created_at'])) ?> WIB</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
