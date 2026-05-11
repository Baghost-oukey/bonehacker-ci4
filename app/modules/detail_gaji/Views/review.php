<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Review Slip Gaji</h1>
        <p class="text-slate-500 mt-2">Konfirmasi rincian pembayaran sebelum diproses ke sistem keuangan.</p>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <!-- Header Card -->
            <div class="bg-slate-900 p-8 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold"><?= esc($terapis['nama']) ?></h2>
                        <p class="text-slate-400 mt-1 uppercase tracking-widest text-xs font-bold"><?= date('F Y') ?></p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-1.5 bg-blue-500/20 text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider border border-blue-500/30">
                            <?= esc($terapis['tipe_gaji']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Body Slip -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Sisi Kiri: Pendapatan -->
                    <div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <i class="fas fa-plus-circle text-emerald-500"></i> Pendapatan
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Gaji Pokok / Dasar</span>
                                <span class="font-bold text-slate-800">Rp <?= number_format($terapis['nominal_gaji'], 0, ',', '.') ?></span>
                            </div>
                            <?php if ($terapis['tipe_gaji'] === 'harian'): ?>
                            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-dashed border-slate-200">
                                <span class="text-xs text-slate-500 italic">Dikalikan Kehadiran (<?= $terapis['current_kehadiran'] ?> Hari)</span>
                                <span class="font-bold text-slate-800">Rp <?= number_format($terapis['nominal_gaji'] * $terapis['current_kehadiran'], 0, ',', '.') ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Total Tunjangan</span>
                                <span class="font-bold text-indigo-600">+ Rp <?= number_format($terapis['total_tunjangan'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Potongan -->
                    <div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <i class="fas fa-minus-circle text-rose-500"></i> Potongan
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Kasbon Karyawan</span>
                                <span class="font-bold text-rose-600">- Rp <?= number_format($terapis['total_kasbon'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-10 border-slate-100">

                <!-- Ringkasan Akhir -->
                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Gaji Bersih (Take Home Pay)</p>
                            <?php 
                                $pokok = ($terapis['tipe_gaji'] === 'harian') ? ($terapis['nominal_gaji'] * $terapis['current_kehadiran']) : $terapis['nominal_gaji'];
                                $bersih = $pokok + $terapis['total_tunjangan'] - $terapis['total_kasbon'];
                            ?>
                            <h4 class="text-4xl font-black text-slate-900 mt-1">Rp <?= number_format($bersih, 0, ',', '.') ?></h4>
                        </div>
                        
                        <form id="formProsesGaji" action="<?= base_url('detail-gaji/proses_simpan') ?>" method="POST" class="w-full md:w-auto">
                            <?= csrf_field() ?>
                            <input type="hidden" name="terapis_id" value="<?= $terapis['id'] ?>">
                            <input type="hidden" name="total_kehadiran" value="<?= $terapis['current_kehadiran'] ?>">
                            <button type="submit" id="btnKonfirmasi" class="w-full md:w-auto px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-3">
                                <i class="fas fa-check-double"></i> KONFIRMASI & BAYAR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="<?= base_url('gaji') ?>" class="text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pengelolaan Gaji
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('resources/js/pages/review_gaji.js') ?>"></script>
<?= $this->endSection() ?>
