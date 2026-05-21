<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <a href="<?= base_url('transaksi-tunjangan') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-600 transition-colors mb-6">
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

        <!-- Kiri: Setting Tunjangan Aktif -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Tunjangan Aktif</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Dihitung otomatis saat proses gaji</p>
                </div>
                <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2.5 py-1 rounded-full">
                    <?= count($settings) ?> aktif
                </span>
            </div>

            <div class="divide-y divide-slate-50">
                <?php if (empty($settings)): ?>
                    <div class="p-8 text-center text-slate-400 text-sm italic">
                        <i class="fas fa-inbox text-2xl mb-2 block text-slate-300"></i>
                        Belum ada tunjangan yang diset
                    </div>
                <?php else: ?>
                    <?php foreach ($settings as $s): ?>
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                                    <?= $s['tipe'] === 'bulanan' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' ?>">
                                    <i class="fas <?= $s['tipe'] === 'bulanan' ? 'fa-calendar-check' : 'fa-sun' ?> text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800"><?= esc($s['nama_tunjangan']) ?></p>
                                    <p class="text-xs text-slate-500">
                                        <?php if ($s['tipe'] === 'bulanan'): ?>
                                            <span class="text-blue-600 font-semibold">Bulanan</span> — Rp <?= number_format($s['nominal'], 0, ',', '.') ?>/bulan
                                        <?php else: ?>
                                            <span class="text-amber-600 font-semibold">Harian</span> — Rp <?= number_format($s['nominal'], 0, ',', '.') ?>/hari hadir
                                        <?php endif; ?>
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

            <!-- Total estimasi -->
            <?php if (!empty($settings)): ?>
                <?php
                    $totalBulanan = array_sum(array_column(array_filter($settings, fn($s) => $s['tipe'] === 'bulanan'), 'nominal'));
                    $totalHarian  = array_sum(array_column(array_filter($settings, fn($s) => $s['tipe'] === 'harian'), 'nominal'));
                ?>
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3 space-y-1">
                    <?php if ($totalBulanan > 0): ?>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Tunjangan bulanan tetap</span>
                            <span class="font-bold text-blue-600">Rp <?= number_format($totalBulanan, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($totalHarian > 0): ?>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Tunjangan harian (per hari hadir)</span>
                            <span class="font-bold text-amber-600">Rp <?= number_format($totalHarian, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kanan: Form Tambah Setting -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                <h3 class="text-sm font-bold text-slate-800">Tambah Tunjangan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Set jenis dan nominal tunjangan untuk terapis ini</p>
            </div>

            <form id="formSaveSetting" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="terapis_id" value="<?= $terapis['id'] ?>">

                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1.5 block">Jenis Tunjangan <span class="text-red-500">*</span></label>
                    <select name="tunjangan_karyawan_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                        <option value="">-- Pilih Jenis --</option>
                        <?php foreach ($master_tunjangan as $mt): ?>
                            <option value="<?= $mt['id'] ?>"><?= esc($mt['nama_tunjangan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1.5 block">Tipe Pemberian <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe" value="bulanan" class="peer sr-only" checked>
                            <div class="flex flex-col items-center gap-1.5 rounded-lg border-2 border-slate-200 bg-white p-3 text-center transition peer-checked:border-teal-500 peer-checked:bg-teal-50">
                                <i class="fas fa-calendar-check text-slate-400 peer-checked:text-blue-600 text-lg"></i>
                                <span class="text-xs font-bold text-slate-600">Bulanan</span>
                                <span class="text-[10px] text-slate-400">Nominal tetap/bulan</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe" value="harian" class="peer sr-only">
                            <div class="flex flex-col items-center gap-1.5 rounded-lg border-2 border-slate-200 bg-white p-3 text-center transition peer-checked:border-teal-500 peer-checked:bg-teal-50">
                                <i class="fas fa-sun text-slate-400 text-lg"></i>
                                <span class="text-xs font-bold text-slate-600">Harian</span>
                                <span class="text-[10px] text-slate-400">Nominal × hari hadir</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1.5 block">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" name="nominal" id="inputNominalSetting" required placeholder="Contoh: 100.000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                    <p id="keteranganNominal" class="text-xs text-slate-400 mt-1">Nominal per bulan</p>
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
window.tunjanganDetailConfig = {
    csrfName:       '<?= csrf_token() ?>',
    csrfHash:       '<?= csrf_hash() ?>',
    saveSettingUrl: '<?= base_url('transaksi-tunjangan/save-setting') ?>',
    deleteUrl:      '<?= base_url('transaksi-tunjangan/delete-setting') ?>',
};

$(document).ready(function () {
    const cfg = window.tunjanganDetailConfig;

    // Format rupiah
    $('#inputNominalSetting').on('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? 'Rp ' + parseInt(v).toLocaleString('id-ID') : '';
    });

    // Update keterangan nominal saat tipe berubah
    $('input[name="tipe"]').on('change', function () {
        const ket = this.value === 'harian'
            ? 'Nominal per hari hadir (dikalikan jumlah hari hadir saat proses gaji)'
            : 'Nominal tetap per bulan';
        $('#keteranganNominal').text(ket);
    });

    // Submit setting
    $('#formSaveSetting').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnSaveSetting');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...');

        $.ajax({
            url: cfg.saveSettingUrl,
            type: 'POST',
            data: $(this).serialize() + '&' + cfg.csrfName + '=' + cfg.csrfHash,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1200, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            complete: () => btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Setting')
        });
    });
});

// Hapus setting
window.hapusSetting = function (id) {
    Swal.fire({
        title: 'Hapus tunjangan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;
        const cfg = window.tunjanganDetailConfig;
        $.ajax({
            url: cfg.deleteUrl + '/' + id,
            type: 'POST',
            data: { [cfg.csrfName]: cfg.csrfHash },
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1000, showConfirmButton: false });
                setTimeout(() => location.reload(), 1000);
            }
        });
    });
};
</script>
<?= $this->endSection() ?>
