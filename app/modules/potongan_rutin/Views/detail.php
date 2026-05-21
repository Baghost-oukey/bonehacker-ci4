<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <a href="<?= base_url('potongan-rutin') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-600 transition-colors mb-6">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Terapis
    </a>

    <!-- Header Terapis -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row items-center gap-6">
        <div class="w-16 h-16 rounded-full bg-teal-600 flex items-center justify-center text-white text-2xl font-extrabold shrink-0">
            <?= strtoupper(substr($terapis['nama'], 0, 1)) ?>
        </div>
        <div class="flex-1 text-center sm:text-left">
            <h1 class="text-xl font-extrabold text-slate-900"><?= esc($terapis['nama']) ?></h1>
            <p class="text-sm text-slate-500 uppercase tracking-widest font-semibold mt-0.5"><?= esc($terapis['nama_jabatan'] ?? 'Terapis') ?></p>
        </div>
        <div class="bg-slate-50 px-5 py-3 rounded-xl border border-slate-100 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gaji Pokok</p>
            <p class="text-lg font-black text-slate-800">Rp <?= number_format($terapis['nominal_gaji'] ?? 0, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Kiri: Setting Potongan Rutin Aktif -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Potongan Rutin Aktif</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Dipotong otomatis dari gaji bulanan terapis</p>
                </div>
                <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2.5 py-1 rounded-full">
                    <?= count($settings) ?> aktif
                </span>
            </div>

            <div class="divide-y divide-slate-50">
                <?php if (empty($settings)): ?>
                    <div class="p-8 text-center text-slate-400 text-sm italic">
                        <i class="fas fa-inbox text-2xl mb-2 block text-slate-300"></i>
                        Belum ada potongan rutin yang diset
                    </div>
                <?php else: ?>
                    <?php foreach ($settings as $s): ?>
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-rose-50 text-rose-600">
                                    <i class="fas fa-minus-circle text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800"><?= esc($s['nama_potongan']) ?></p>
                                    <p class="text-xs text-slate-500">
                                        Nominal: <span class="text-rose-600 font-semibold">Rp <?= number_format($s['nominal'], 0, ',', '.') ?></span>/bulan
                                    </p>
                                </div>
                            </div>
                            <button onclick="hapusSetting(<?= $s['id'] ?>)"
                                class="text-slate-300 hover:text-red-500 transition p-1.5 rounded-lg hover:bg-red-50">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Total Estimasi Potongan -->
            <?php if (!empty($settings)): ?>
                <?php
                    $totalPotongan = array_sum(array_column($settings, 'nominal'));
                ?>
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3 space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Total Potongan Rutin</span>
                        <span class="font-bold text-rose-600">Rp <?= number_format($totalPotongan, 0, ',', '.') ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kanan: Form Tambah/Update Setting -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                <h3 class="text-sm font-bold text-slate-800">Tambah Potongan Rutin</h3>
                <p class="text-xs text-slate-500 mt-0.5">Set nama dan nominal potongan rutin bulanan untuk terapis ini</p>
            </div>

            <form id="formSaveSetting" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="terapis_id" value="<?= $terapis['id'] ?>">

                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1.5 block">Nama Potongan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_potongan" required placeholder="Contoh: BPJS Kesehatan, Koperasi, Kasbon Mandiri"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1.5 block">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" name="nominal" id="inputNominalSetting" required placeholder="Contoh: 50.000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                    <p class="text-xs text-slate-400 mt-1">Nominal potongan rutin bulanan</p>
                </div>

                <button type="submit" id="btnSaveSetting"
                    class="w-full rounded-lg bg-teal-600 py-2.5 text-sm font-medium text-white transition hover:bg-teal-700">
                    <i class="fas fa-save mr-1.5"></i> Simpan Setting
                </button>
            </form>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.potonganDetailConfig = {
    csrfName:       '<?= csrf_token() ?>',
    csrfHash:       '<?= csrf_hash() ?>',
    saveSettingUrl: '<?= base_url('potongan-rutin/save-setting') ?>',
    deleteUrl:      '<?= base_url('potongan-rutin/delete-setting') ?>',
};
</script>
<?= $this->endSection() ?>
