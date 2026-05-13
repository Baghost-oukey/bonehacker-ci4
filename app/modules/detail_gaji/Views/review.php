<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-6">
        <a href="<?= base_url('gaji') ?>" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Pengelolaan Gaji
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">

            <!-- Header -->
            <div class="bg-slate-900 px-8 py-6 text-white flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black"><?= esc($terapis['nama']) ?></h2>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">
                        Slip Gaji <?= date('F Y') ?> &bull; Kehadiran: <?= $komponen['kehadiran'] ?> Hari
                    </p>
                </div>
                <span class="px-3 py-1.5 bg-blue-500/20 text-blue-400 rounded-full text-xs font-bold uppercase border border-blue-500/30">
                    <?= esc($terapis['tipe_gaji']) ?>
                </span>
            </div>

            <div class="p-8 space-y-6">

                <!-- TAKE HOME -->
                <div>
                    <h3 class="text-xs font-black text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-wallet"></i> Take Home
                    </h3>
                    <div class="bg-emerald-50/50 rounded-2xl border border-emerald-100 divide-y divide-emerald-100">
                        <div class="flex justify-between px-5 py-3 text-sm">
                            <span class="text-slate-600">Gaji Pokok
                                <?php if ($terapis['tipe_gaji'] === 'harian'): ?>
                                    <span class="text-xs text-slate-400">(Rp <?= number_format($terapis['nominal_gaji'], 0, ',', '.') ?>/hari × <?= $komponen['kehadiran'] ?> hari)</span>
                                <?php endif; ?>
                            </span>
                            <span class="font-bold text-slate-800">Rp <?= number_format($komponen['gaji_pokok'], 0, ',', '.') ?></span>
                        </div>
                        <?php if ($komponen['jaspel_reguler'] > 0): ?>
                        <div class="flex justify-between px-5 py-3 text-sm">
                            <span class="text-slate-600">Jasa Pelayanan Reguler</span>
                            <span class="font-bold text-slate-800">Rp <?= number_format($komponen['jaspel_reguler'], 0, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($komponen['jaspel_kejantanan'] > 0): ?>
                        <div class="flex justify-between px-5 py-3 text-sm">
                            <span class="text-slate-600">Jasa Terapi Kejantanan</span>
                            <span class="font-bold text-slate-800">Rp <?= number_format($komponen['jaspel_kejantanan'], 0, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between px-5 py-3 text-sm bg-emerald-50 rounded-b-2xl">
                            <span class="font-black text-emerald-700">Total Take Home</span>
                            <span class="font-black text-emerald-700">Rp <?= number_format($komponen['total_A'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <!-- B. BENEFIT -->
                <?php if (!empty($komponen['benefit_list'])): ?>
                <div>
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i> Benefit (Ditanggung Perusahaan)
                    </h3>
                    <div class="bg-blue-50/50 rounded-2xl border border-blue-100 divide-y divide-blue-100">
                        <?php foreach ($komponen['benefit_list'] as $b): ?>
                        <div class="flex justify-between px-5 py-3 text-sm">
                            <span class="text-slate-600"><?= esc($b['nama']) ?></span>
                            <span class="font-bold text-slate-800">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="flex justify-between px-5 py-3 text-sm bg-blue-50 rounded-b-2xl">
                            <span class="font-black text-blue-700">Total Benefit</span>
                            <span class="font-black text-blue-700">Rp <?= number_format($komponen['total_B'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- POTONGAN -->
                <?php if ($komponen['total_C'] > 0): ?>
                <div>
                    <h3 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-minus-circle"></i> Potongan
                    </h3>
                    <div class="bg-rose-50/50 rounded-2xl border border-rose-100 divide-y divide-rose-100">
                        <?php foreach ($komponen['potongan_list'] as $p): ?>
                        <div class="flex justify-between px-5 py-3 text-sm">
                            <span class="text-slate-600"><?= esc($p['nama_potongan']) ?></span>
                            <span class="font-bold text-rose-600">- Rp <?= number_format($p['nominal'], 0, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($komponen['total_kasbon'] > 0): ?>
                        <div class="flex justify-between px-5 py-3 text-sm">
                            <span class="text-slate-600">Kasbon Karyawan</span>
                            <span class="font-bold text-rose-600">- Rp <?= number_format($komponen['total_kasbon'], 0, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between px-5 py-3 text-sm bg-rose-50 rounded-b-2xl">
                            <span class="font-black text-rose-700">Total Potongan</span>
                            <span class="font-black text-rose-700">- Rp <?= number_format($komponen['total_C'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- TOTAL PENDAPATAN & BERSIH -->
                <div class="bg-slate-900 rounded-2xl p-6 text-white">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-400">Total Take Home + Benefit</span>
                        <span class="font-bold">Rp <?= number_format($komponen['total_A'] + $komponen['total_B'], 0, ',', '.') ?></span>
                    </div>
                    <?php if ($komponen['total_C'] > 0): ?>
                    <div class="flex justify-between text-sm mb-4 pb-4 border-b border-slate-700">
                        <span class="text-slate-400">Total Potongan</span>
                        <span class="font-bold text-rose-400">- Rp <?= number_format($komponen['total_C'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black uppercase tracking-widest text-slate-300">Pendapatan Bersih</span>
                        <span class="text-3xl font-black text-emerald-400">Rp <?= number_format($komponen['gaji_bersih'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <!-- Tombol Konfirmasi -->
                <form id="formProsesGaji" action="<?= base_url('detail-gaji/proses_simpan') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="terapis_id" value="<?= $terapis['id'] ?>">
                    <button type="submit" id="btnKonfirmasi"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i class="fas fa-check-double"></i> KONFIRMASI & BAYAR
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('resources/js/pages/review_gaji.js') ?>"></script>
<?= $this->endSection() ?>
