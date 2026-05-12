<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="monitorGajiPage" class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Gaji Saya</h1>
        <p class="text-sm font-bold text-slate-500 mt-1 uppercase tracking-widest">Pantau penghasilan dan riwayat pembayaran Anda.</p>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="p-4 mb-6 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500"></i>
            <span class="font-bold"><?= esc(session()->getFlashdata('success')) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Estimasi Bulan Ini -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="bg-teal-600 px-6 py-5">
                    <h3 class="text-xs font-black text-white uppercase tracking-[0.2em]">Estimasi Bulan Ini</h3>
                    <p class="text-white text-lg font-black mt-1 uppercase tracking-tight"><?= date('F Y') ?></p>
                </div>
                
                <div class="p-6 space-y-6">
                    <?php if ($estimasi) : ?>
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Gaji Pokok (<?= esc($estimasi['tipe_gaji']) ?>)</span>
                                <span class="text-sm font-black text-slate-800">Rp <?= number_format($estimasi['nominal_gaji'], 0, ',', '.') ?></span>
                            </div>

                            <?php if ($estimasi['tipe_gaji'] === 'harian') : ?>
                                <div class="flex justify-between items-end p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] font-black text-slate-500 uppercase">Kehadiran (Bulan Ini)</span>
                                    <span class="text-xs font-black text-slate-700"><?= $estimasi['current_kehadiran'] ?> Hari</span>
                                </div>
                                <div class="flex justify-between items-end">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Gaji Pokok</span>
                                    <span class="text-sm font-black text-slate-800">Rp <?= number_format($estimasi['nominal_gaji'] * $estimasi['current_kehadiran'], 0, ',', '.') ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black text-indigo-500 uppercase tracking-widest">Tunjangan Berjalan</span>
                                <span class="text-sm font-black text-indigo-600">Rp <?= number_format($estimasi['total_tunjangan'], 0, ',', '.') ?></span>
                            </div>

                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black text-rose-500 uppercase tracking-widest">Potongan Kasbon</span>
                                <span class="text-sm font-black text-rose-600">- Rp <?= number_format($estimasi['total_kasbon'], 0, ',', '.') ?></span>
                            </div>

                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex justify-between items-center bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-[0.2em]">Take Home Pay</span>
                                    <span class="text-xl font-black text-emerald-700">
                                        <?php 
                                            $totalGaji = ($estimasi['tipe_gaji'] === 'harian') ? ($estimasi['nominal_gaji'] * $estimasi['current_kehadiran']) : $estimasi['nominal_gaji'];
                                            $bersih = $totalGaji + $estimasi['total_tunjangan'] - $estimasi['total_kasbon'];
                                            echo 'Rp ' . number_format(max(0, $bersih), 0, ',', '.');
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="py-10 text-center">
                            <i class="fas fa-exclamation-triangle text-amber-400 text-3xl mb-3"></i>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pengaturan Gaji Belum Diset</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <p class="text-[9px] font-black text-slate-400 uppercase leading-relaxed tracking-widest">
                        * Angka di atas adalah estimasi sementara berdasarkan data tindakan dan kehadiran yang tercatat hari ini.
                    </p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Riwayat Penggajian -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden flex flex-col h-full">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Riwayat Pembayaran</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Daftar gaji yang telah dicairkan.</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                        <i class="fas fa-history"></i>
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Periode</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Kehadiran</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Gaji Pokok</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Tunjangan</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Potongan</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if (empty($history)) : ?>
                                <tr>
                                    <td colspan="6" class="px-8 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="h-16 w-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-200 text-2xl">
                                                <i class="fas fa-folder-open"></i>
                                            </div>
                                            <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Belum ada riwayat pembayaran</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($history as $h) : ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-slate-800 uppercase tracking-tight"><?= date('F Y', mktime(0, 0, 0, $h['periode_bulan'], 1, $h['periode_tahun'])) ?></span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Dibayar: <?= date('d/m/Y', strtotime($h['tanggal_bayar'])) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-black">
                                                <?= $h['total_kehadiran'] ?> HARI
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <span class="text-xs font-bold text-slate-600">Rp <?= number_format($h['gaji_pokok_total'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <span class="text-xs font-bold text-indigo-600">+ Rp <?= number_format($h['total_tunjangan'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <span class="text-xs font-bold text-rose-500">- Rp <?= number_format($h['total_potongan'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <span class="text-sm font-black text-emerald-600">Rp <?= number_format($h['gaji_bersih'], 0, ',', '.') ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
