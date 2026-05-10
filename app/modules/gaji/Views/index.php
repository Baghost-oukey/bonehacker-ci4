<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="gajiPage" class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Analisis & Kelola Gaji</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem penggajian otomatis terintegrasi dengan data kehadiran dan tindakan.</p>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Wrapper Utama -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">

        <!-- Tab Navigasi -->
        <div class="flex border-b border-slate-200 px-6 pt-2">
            <button class="tab-btn active px-4 py-3 font-semibold text-sm border-b-2 border-blue-600 text-blue-600" data-target="tab-estimasi">
                Periode Berjalan
            </button>
            <button class="tab-btn px-4 py-3 font-medium text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700" data-target="tab-riwayat">
                Riwayat Transaksi
            </button>
        </div>

        <!-- TAB 1: PERIODE BERJALAN (BELUM DIBAYAR) -->
        <div id="tab-estimasi" class="tab-content p-6 block">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                            <th class="p-4 rounded-tl-lg">Nama Terapis</th>
                            <th class="p-4">Wilayah</th>
                            <th class="p-4">Tipe & Gaji Dasar</th>
                            <th class="p-4 text-center">Tindakan</th>
                            <th class="p-4 text-right rounded-tr-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach ($estimasi_gaji as $row) : ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <span class="font-medium text-slate-800"><?= esc($row['nama']) ?></span>
                                </td>
                                <td class="p-4 text-slate-600"><?= esc($row['wilayah']) ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <?php if ($row['tipe_gaji'] === 'Belum Diset') : ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-600 rounded text-xs font-semibold">Belum Diset</span>
                                        <?php else : ?>
                                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium uppercase"><?= esc($row['tipe_gaji']) ?></span>
                                            <span class="text-slate-600 font-medium">Rp <?= number_format($row['nominal_gaji'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                        <!-- Tombol Gear Modal Setting -->
                                        <button class="btn-setting text-slate-400 hover:text-blue-600 transition" 
                                                data-terapis-id="<?= $row['terapis_id'] ?>" 
                                                data-tipe-gaji="<?= esc($row['tipe_gaji']) ?>"
                                                data-nominal="<?= $row['nominal_gaji'] ?? 0 ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                        <?= esc($row['jml_tindakan'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="btn-proses-gaji inline-flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white rounded-md hover:bg-slate-800 transition text-sm"
                                            data-terapis-id="<?= $row['terapis_id'] ?>">
                                        Proses Gaji
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: RIWAYAT TRANSAKSI -->
        <div id="tab-riwayat" class="tab-content p-6 hidden">
            <!-- Filter Riwayat -->
            <form action="<?= base_url('gaji') ?>" method="GET" class="mb-6 flex flex-wrap items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Bulan:</label>
                    <select name="bulan" onchange="this.form.submit()" class="text-sm font-bold border-slate-200 rounded-lg focus:ring-blue-500">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $filter_bulan == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Tahun:</label>
                    <select name="tahun" onchange="this.form.submit()" class="text-sm font-bold border-slate-200 rounded-lg focus:ring-blue-500">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="ml-auto">
                    <p class="text-sm text-slate-500">Menampilkan riwayat gaji yang sudah lunas.</p>
                </div>
            </form>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                        <th class="p-4">Tanggal Bayar</th>
                        <th class="p-4">Nama Terapis</th>
                        <th class="p-4">Periode</th>
                        <th class="p-4 text-right">Total Dibayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (empty($riwayat_gaji)): ?>
                        <tr>
                            <td colspan="4" class="text-center p-4 text-slate-400">Belum ada riwayat penggajian</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riwayat_gaji as $rw): ?>
                            <tr>
                                <td class="p-4 text-slate-600"><?= date('d M Y', strtotime($rw['tanggal_bayar'])) ?></td>
                                <td class="p-4 font-medium text-slate-800"><?= esc($rw['nama']) ?></td>
                                <td class="p-4 text-slate-600"><?= esc($rw['periode_bulan']) . '/' . esc($rw['periode_tahun']) ?></td>
                                <td class="p-4 text-right font-bold text-green-600">Rp <?= number_format($rw['gaji_bersih'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- OFFCANVAS PROSES GAJI (Sliding dari Kanan) -->
<div id="offcanvasProses" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-lg font-bold text-slate-800">Rincian Penggajian</h3>
        <button class="btn-close-offcanvas text-slate-400 hover:text-slate-600 p-2">&times;</button>
    </div>

    <div class="flex-1 overflow-y-auto p-6" id="offcanvasContent">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-10 hidden">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900 mx-auto mb-4"></div>
            <p class="text-sm text-slate-500">Menghitung kalkulasi gaji...</p>
        </div>

        <!-- Form Konten -->
        <form id="formBayarGaji" action="<?= base_url('gaji/proses_bayar') ?>" method="POST" class="hidden">
            <?= csrf_field() ?>
            <input type="hidden" name="terapis_id" id="oc_terapis_id">
            <input type="hidden" name="tipe_gaji" id="oc_tipe_gaji" value="bulanan">

            <div class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-100">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Penerima</p>
                <p class="font-bold text-slate-800 text-lg" id="oc_nama_terapis">-</p>
                <p class="mt-2 text-xs text-slate-500" id="oc_tipe_info">-</p>
            </div>

            <!-- Input Dinamis (Admin bisa menyesuaikan hari kehadiran jika perlu) -->
            <div class="mb-4" id="oc_kehadiran_group">
                <label class="block text-sm font-medium text-slate-700 mb-1" id="oc_kehadiran_label">Total Kehadiran (Hari)</label>
                <input type="number" name="total_kehadiran" id="oc_kehadiran" value="0" class="w-full border-slate-200 rounded-lg bg-white" required>
                <p class="mt-2 text-xs text-slate-500">Jika tipe gaji harian, gaji akan dikalkulasi berdasarkan jumlah kehadiran.</p>
            </div>

            <hr class="my-6 border-slate-100">

            <!-- Rincian Readonly -->
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Gaji Pokok / Dasar</span>
                    <input type="text" name="gaji_pokok_total" id="oc_gaji_pokok" class="text-right font-medium text-slate-800 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-indigo-700">Total Tunjangan</span>
                    <input type="text" name="total_tunjangan" id="oc_tunjangan" class="text-right font-medium text-indigo-700 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-red-500">Potongan Kasbon</span>
                    <input type="text" name="total_potongan" id="oc_potongan" class="text-right font-medium text-red-600 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between items-center pt-4 border-t border-slate-200 mt-4">
                    <span class="font-bold text-slate-800 text-base">Gaji Bersih (Take Home)</span>
                    <input type="text" name="gaji_bersih" id="oc_bersih" class="text-right font-bold text-green-600 text-lg border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full py-3 bg-slate-900 text-white rounded-xl font-medium hover:bg-slate-800 transition shadow-md">
                    Setujui & Bayarkan
                </button>
            </div>
        </form>
    </div>
</div>
<div id="offcanvasBackdrop" class="offcanvas-backdrop fixed inset-0 bg-slate-900/20 z-40 hidden transition-opacity"></div>

<!-- MODAL SETTING GAJI (Posisinya Fixed di atas layar) -->
<div id="modalSetting" class="fixed inset-0 z-[100] flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform scale-95 transition-transform" id="modalSettingContent">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Atur Gaji Dasar</h3>
            <button type="button" class="btn-close-modal-setting text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Form menembak ke route: gaji/setting/save -->
        <form action="<?= base_url('gaji/setting/save') ?>" method="POST" class="p-6">
            <!-- CSRF Token (Penting untuk keamanan Enterprise) -->
            <?= csrf_field() ?>

            <input type="hidden" name="terapis_id" id="set_terapis_id">

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Gaji</label>
                <select name="tipe_gaji" id="set_tipe_gaji" class="w-full border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="bulanan">Bulanan (Gaji Tetap)</option>
                    <option value="harian">Harian (Per Kehadiran)</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp)</label>
                <input type="text" name="nominal_gaji" id="set_nominal_gaji" class="w-full border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 input-rupiah" placeholder="Contoh: 3.000.000" required>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="btn-close-modal-setting px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Global untuk Gaji -->
<script>
    window.gajiConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('gaji/fetch_estimasi') ?>",
        detailUrl: "<?= base_url('gaji/detail') ?>", // Untuk Offcanvas
        saveSettingUrl: "<?= base_url('gaji/setting/save') ?>",
        prosesBayarUrl: "<?= base_url('gaji/proses_bayar') ?>"
    };
</script>
<?= $this->endSection() ?>