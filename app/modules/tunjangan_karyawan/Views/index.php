<?= $this->extend('layout/layout'); ?>
<?= $this->section('content'); ?>

<div class="p-4 sm:p-6 bg-slate-50 min-h-screen">

    <!-- PAGE HEADER -->
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Master Gaji</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola gaji pokok terapis dan master item tunjangan.</p>
    </div>

    <!-- TAB NAVIGATION -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6 overflow-hidden">
        <div class="flex border-b border-slate-100">
            <button id="tabTerapis"
                class="tab-btn px-6 py-4 text-sm font-semibold border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2"
                data-target="contentTerapis">
                <i class="fas fa-users text-xs"></i>
                Gaji Terapis
            </button>
            <button id="tabMaster"
                class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition flex items-center gap-2"
                data-target="contentMaster">
                <i class="fas fa-list-alt text-xs"></i>
                Master Item Gaji
            </button>
        </div>

        <!-- ===== TAB 1: GAJI TERAPIS ===== -->
        <div id="contentTerapis" class="tab-content">

            <!-- DESKTOP TABLE -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Terapis</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Jabatan</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Wilayah</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipe Gaji</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Gaji Dasar</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Tunjangan</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Potongan</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        <?php if (empty($terapis_gaji)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                    Belum ada data terapis aktif.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($terapis_gaji as $t): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-sm flex-shrink-0">
                                                <?= strtoupper(substr($t['nama'], 0, 1)) ?>
                                            </div>
                                            <span class="font-semibold text-slate-800"><?= esc($t['nama']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500"><?= esc($t['nama_jabatan'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-slate-500"><?= esc($t['wilayah'] ?? '-') ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($t['tipe_gaji'] === 'bulanan'): ?>
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold rounded uppercase tracking-wide">Bulanan</span>
                                        <?php elseif ($t['tipe_gaji'] === 'harian'): ?>
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded uppercase tracking-wide">Harian</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-teal-50 text-teal-700 text-[10px] font-bold rounded">Belum Diset</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (isset($t['tipe_gaji']) && ($t['tipe_gaji'] === 'bulanan' || $t['tipe_gaji'] === 'harian')): ?>
                                            <span class="font-semibold text-slate-700">Rp <?= number_format($t['nominal_gaji'], 0, ',', '.') ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-teal-50 text-teal-700 text-[10px] font-bold rounded">Belum Diset</span>
                                        <?php endif; ?>
                                        <?php if ($t['potong_absen'] == 1): ?>
                                            <div class="text-[9px] text-rose-400 font-semibold mt-0.5">✕ Potong Absen</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600">
                                            <?= $t['allowance_count'] ?> item
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-600">
                                            <?= $t['potongan_count'] ?> item
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            onclick="bukaModalGajiPokok(this)"
                                            class="btn-atur-gaji inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-bold rounded-lg transition"
                                            data-id="<?= $t['id'] ?>"
                                            data-nama="<?= esc($t['nama']) ?>"
                                            data-tipe="<?= esc($t['tipe_gaji'] ?? '') ?>"
                                            data-nominal="<?= (float)($t['nominal_gaji'] ?? 0) ?>"
                                            data-potong="<?= (int)($t['potong_absen'] ?? 0) ?>"
                                            data-potong-nominal="<?= (float)($t['nominal_potong_absen'] ?? 0) ?>">
                                            <i class="fas fa-pen text-[10px]"></i> Atur Gaji
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS -->
            <div class="md:hidden divide-y divide-slate-100">
                <?php if (empty($terapis_gaji)): ?>
                    <div class="p-8 text-center text-slate-400 italic text-sm">Belum ada data terapis aktif.</div>
                <?php else: ?>
                    <?php foreach ($terapis_gaji as $t): ?>
                        <div class="p-4 flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-sm flex-shrink-0">
                                    <?= strtoupper(substr($t['nama'], 0, 1)) ?>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 text-sm truncate"><?= esc($t['nama']) ?></p>
                                    <p class="text-[11px] text-slate-400"><?= esc($t['nama_jabatan'] ?? '-') ?> &bull; <?= esc($t['wilayah'] ?? '-') ?></p>
                                    <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                                        <?php if ($t['tipe_gaji'] === 'bulanan'): ?>
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold rounded">Bulanan</span>
                                            <span class="text-xs font-semibold text-slate-600">Rp <?= number_format($t['nominal_gaji'], 0, ',', '.') ?></span>
                                        <?php elseif ($t['tipe_gaji'] === 'harian'): ?>
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded">Harian</span>
                                            <span class="text-xs font-semibold text-slate-600">Rp <?= number_format($t['nominal_gaji'], 0, ',', '.') ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-teal-50 text-teal-700 text-[10px] font-bold rounded">Belum Diset</span>
                                        <?php endif; ?>
                                        <span class="text-[10px] text-indigo-500 font-semibold"><?= $t['allowance_count'] ?> tunjangan</span>
                                        <span class="text-[10px] text-rose-400 font-semibold"><?= $t['potongan_count'] ?> potongan</span>
                                    </div>
                                </div>
                            </div>
                            <button
                                class="btn-atur-gaji flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-[11px] font-bold rounded-lg transition"
                                onclick="bukaModalGajiPokok(this)"
                                data-id="<?= $t['id'] ?>"
                                data-nama="<?= esc($t['nama']) ?>"
                                data-tipe="<?= esc($t['tipe_gaji'] ?? '') ?>"
                                data-nominal="<?= (float)($t['nominal_gaji'] ?? 0) ?>"
                                data-potong="<?= (int)($t['potong_absen'] ?? 0) ?>"
                                data-potong-nominal="<?= (float)($t['nominal_potong_absen'] ?? 0) ?>">
                                <i class="fas fa-pen text-[9px]"></i> Atur
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== TAB 2: MASTER ITEM GAJI ===== -->
        <div id="contentMaster" class="tab-content hidden">

            <!-- Header with Add Button -->
            <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
                <p class="text-sm text-slate-500">Daftar master item tunjangan, benefit, dan potongan.</p>
                <button id="btnTambahTunjangan"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm shadow-indigo-200">
                    <i class="fas fa-plus text-[10px]"></i> Tambah Item
                </button>
            </div>

            <!-- DESKTOP TABLE -->
            <div class="hidden md:block overflow-x-auto">
                <table id="tableTunjangan" class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-10 text-center">No</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Item</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipe</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Nominal</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Terapis</th>
                            <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableTunjanganBody" class="divide-y divide-slate-50 text-sm">
                        <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 text-sm italic">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS -->
            <div id="mobile-card-container" class="md:hidden divide-y divide-slate-100">
                <div class="p-8 text-center text-slate-400 text-sm italic">Memuat data...</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: ATUR GAJI POKOK ===== -->
<div id="modalGajiPokok" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="flex items-start justify-between px-6 py-5 border-b border-slate-100">
            <div>
                <h5 class="text-base font-bold text-slate-800">Pengaturan Gaji Pokok</h5>
                <p id="modalGajiNamaTerapis" class="text-xs text-slate-400 mt-0.5">—</p>
            </div>
            <button id="btnTutupModalGaji" class="text-slate-300 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-100 ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <form id="formGajiPokok" class="px-6 py-5 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" id="gaji_terapis_id" name="terapis_id">

            <!-- Tipe Gaji -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tipe Gaji <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-slate-200 cursor-pointer transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="tipe_gaji" value="bulanan" class="text-indigo-600" required>
                        <div>
                            <p class="text-sm font-bold text-slate-700">Bulanan</p>
                            <p class="text-[10px] text-slate-400">Tetap per bulan</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-slate-200 cursor-pointer transition has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="tipe_gaji" value="harian" class="text-amber-600" required>
                        <div>
                            <p class="text-sm font-bold text-slate-700">Harian</p>
                            <p class="text-[10px] text-slate-400">Per hari hadir</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Nominal -->
            <div>
                <label for="gaji_nominal" class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal Gaji Pokok (Rp) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                    <input type="text" id="gaji_nominal" name="nominal_gaji" required
                        placeholder="0"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white transition">
                </div>
            </div>

            <!-- Potong Absen -->
            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                <input type="checkbox" id="gaji_potong_absen" name="potong_absen" value="1" class="rounded text-indigo-600">
                <div>
                    <p class="text-sm font-semibold text-slate-700">Potong Absen</p>
                    <p class="text-[11px] text-slate-400">Kurangi gaji otomatis jika tidak hadir</p>
                </div>
            </label>

            <!-- Nominal Potongan Absen -->
            <div id="group_nominal_potong_absen" class="hidden transition-all duration-200">
                <label for="gaji_nominal_potong_absen" class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal Potongan Absen Per Hari (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                    <input type="text" id="gaji_nominal_potong_absen" name="nominal_potong_absen"
                        placeholder="0"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-sm font-bold text-slate-800 bg-slate-50 focus:bg-white transition">
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center gap-3 pt-1">
                <button type="button" id="btnBatalGaji"
                    class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="btnSimpanGaji"
                    class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold transition shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL: TAMBAH/EDIT MASTER ITEM ===== -->
<div id="modalTunjangan" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <h5 id="modalTitle" class="text-base font-bold text-slate-800">Tambah Item Gaji</h5>
            <button type="button" class="btn-close-modal text-slate-300 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formTunjangan" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="id_tunjangan" name="id">

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Item <span class="text-red-500">*</span></label>
                <input type="text" id="nama_tunjangan" name="nama_tunjangan" required
                    placeholder="Contoh: BPJS Kesehatan, Tunjangan Makan"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-sm bg-slate-50 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select id="kategori" name="kategori" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none text-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="tunjangan">Tunjangan (Cash)</option>
                        <option value="benefit">Benefit (Non-Cash)</option>
                        <option value="potongan">Potongan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tipe <span class="text-red-500">*</span></label>
                    <select id="tipe" name="tipe" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none text-sm bg-white">
                        <option value="bulanan">Bulanan (tetap/bln)</option>
                        <option value="harian">Harian (× hari hadir)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal (Rp) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                    <input type="text" id="nominal" name="nominal" required placeholder="0"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-sm font-bold text-indigo-600 bg-slate-50 focus:bg-white transition">
                </div>
                <p id="keteranganNominal" class="text-[11px] text-slate-400 mt-1">Nominal tetap per bulan</p>
            </div>

            <!-- Pilih Terapis -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Terapis yang Mendapat Item Ini</label>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="selectAllTerapis" class="rounded text-indigo-600">
                    <label for="selectAllTerapis" class="text-xs font-semibold text-slate-500 cursor-pointer">Pilih Semua</label>
                </div>
                <div class="border border-slate-200 rounded-xl max-h-44 overflow-y-auto p-3 space-y-1.5 bg-slate-50">
                    <?php if (empty($terapis)): ?>
                        <p class="text-xs text-slate-400 italic">Tidak ada terapis aktif</p>
                    <?php else: ?>
                        <?php foreach ($terapis as $t): ?>
                            <label class="flex items-center gap-2.5 cursor-pointer hover:bg-white rounded-lg p-1.5 transition">
                                <input type="checkbox" name="terapis_ids[]" value="<?= $t['id'] ?>"
                                    class="terapis-checkbox rounded text-indigo-600">
                                <span class="text-sm text-slate-700"><?= esc($t['nama']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="button" class="btn-close-modal flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btnSimpan" class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold transition shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
window.masterTunjanganConfig = {
    csrfName:  '<?= csrf_token() ?>',
    csrfHash:  '<?= csrf_hash() ?>',
    urlFetch:  '<?= base_url('master-gaji/fetch') ?>',
    urlStore:  '<?= base_url('master-gaji/store') ?>',
    urlDetail: '<?= base_url('master-gaji/detail') ?>',
    urlDelete: '<?= base_url('master-gaji/delete') ?>',
    urlSaveGajiPokok: '<?= base_url('master-gaji/save-gaji-pokok') ?>'
};

// ─── GLOBAL FUNCTION TO OPEN SALARY MODAL ─────────────────────────
window.bukaModalGajiPokok = function (btn) {
    const $btn          = $(btn);
    const id            = $btn.data('id');
    const nama          = $btn.data('nama');
    const tipeGaji      = $btn.data('tipe') || '';
    const nominal       = parseFloat($btn.data('nominal')) || 0;
    const potong        = parseInt($btn.data('potong')) || 0;
    const potongNominal = parseFloat($btn.data('potong-nominal')) || 0;

    $('#gaji_terapis_id').val(id);
    $('#modalGajiNamaTerapis').text(nama);
    $('#gaji_nominal').val(nominal > 0 ? nominal.toLocaleString('id-ID') : '');
    $('#gaji_potong_absen').prop('checked', potong === 1);
    $('#gaji_nominal_potong_absen').val(potongNominal > 0 ? potongNominal.toLocaleString('id-ID') : '');

    $('input[name="tipe_gaji"]').prop('checked', false);
    if (tipeGaji === 'bulanan' || tipeGaji === 'harian') {
        $('input[name="tipe_gaji"][value="' + tipeGaji + '"]').prop('checked', true);
    }

    // Toggle potong absen group based on values
    togglePotongAbsenGroup();

    $('#modalGajiPokok').removeClass('hidden').addClass('flex');
};

function togglePotongAbsenGroup() {
    const tipeGaji = $('input[name="tipe_gaji"]:checked').val();
    const potongAbsen = $('#gaji_potong_absen').is(':checked');
    if (tipeGaji === 'bulanan' && potongAbsen) {
        $('#group_nominal_potong_absen').removeClass('hidden');
    } else {
        $('#group_nominal_potong_absen').addClass('hidden');
    }
}

$(document).ready(function () {
    const cfg = window.masterTunjanganConfig;

    // ─── TAB SWITCHING ──────────────────────────────────────────────
    $('.tab-btn').on('click', function () {
        const target = $(this).data('target');

        $('.tab-btn')
            .removeClass('border-indigo-600 text-indigo-600 font-semibold')
            .addClass('border-transparent text-slate-500 font-medium');
        $(this)
            .addClass('border-indigo-600 text-indigo-600 font-semibold')
            .removeClass('border-transparent text-slate-500 font-medium');

        $('.tab-content').addClass('hidden');
        $('#' + target).removeClass('hidden');

        if (target === 'contentMaster') {
            loadTable();
        }
    });

    // ─── LOAD MASTER TABLE ──────────────────────────────────────────
    function loadTable() {
        $.ajax({
            url: cfg.urlFetch, type: 'POST',
            data: { [cfg.csrfName]: cfg.csrfHash },
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                const tbody = $('#tableTunjanganBody').empty();

                if (!res.data || res.data.length === 0) {
                    tbody.html('<tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 italic text-sm">Belum ada data master item gaji.</td></tr>');
                    $('#mobile-card-container').html('<div class="p-8 text-center text-slate-400 text-sm italic">Belum ada data master item gaji.</div>');
                    return;
                }

                // Simpan data master items untuk diakses secara global
                window.masterItemsData = res.data;

                res.data.forEach(row => {
                    tbody.append(`
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-400 text-xs">${row.no}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">${row.nama_tunjangan}</td>
                            <td class="px-6 py-4">${row.kategori}</td>
                            <td class="px-6 py-4">${row.tipe}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-700">${row.nominal}</td>
                            <td class="px-6 py-4 text-center text-xs text-slate-500">${row.terapis_count}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="viewTerapisList(${row.id})" class="p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition" title="Lihat Penerima"><i class="fas fa-eye text-xs"></i></button>
                                    <button onclick="editItem(${row.id})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><i class="fas fa-pen text-xs"></i></button>
                                    <button onclick="deleteItem(${row.id})" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                // Mobile cards
                const mc = $('#mobile-card-container').empty();
                res.data.forEach(row => {
                    mc.append(`
                        <div class="flex items-center justify-between gap-4 p-4">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 text-sm">${row.nama_tunjangan}</p>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    ${row.kategori}
                                    <span class="text-[10px] text-slate-400">${row.terapis_count} terapis</span>
                                </div>
                                <p class="text-xs font-bold text-slate-600 mt-0.5">${row.nominal}</p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button onclick="viewTerapisList(${row.id})" class="p-2 bg-slate-100 text-slate-600 rounded-lg active:scale-95 transition" title="Lihat Penerima"><i class="fas fa-eye text-xs"></i></button>
                                <button onclick="editItem(${row.id})" class="p-2 bg-slate-100 text-slate-600 rounded-lg active:scale-95 transition"><i class="fas fa-pen text-xs"></i></button>
                                <button onclick="deleteItem(${row.id})" class="p-2 bg-rose-50 text-rose-500 rounded-lg active:scale-95 transition"><i class="fas fa-trash text-xs"></i></button>
                            </div>
                        </div>
                    `);
                });
            }
        });
    }

    // ─── FORMAT RUPIAH ──────────────────────────────────────────────
    function formatRupiah(el) {
        let v = el.value.replace(/[^0-9]/g, '');
        el.value = v ? parseInt(v).toLocaleString('id-ID') : '';
    }
    $('#nominal').on('input', function () { formatRupiah(this); });
    $('#gaji_nominal').on('input', function () { formatRupiah(this); });
    $('#gaji_nominal_potong_absen').on('input', function () { formatRupiah(this); });

    $('input[name="tipe_gaji"]').on('change', togglePotongAbsenGroup);
    $('#gaji_potong_absen').on('change', togglePotongAbsenGroup);

    $('#tipe').on('change', function () {
        $('#keteranganNominal').text(this.value === 'harian'
            ? 'Nominal per hari hadir (× jumlah hari hadir)'
            : 'Nominal tetap per bulan');
    });

    // ─── SELECT ALL TERAPIS ─────────────────────────────────────────
    $('#selectAllTerapis').on('change', function () {
        $('.terapis-checkbox').prop('checked', this.checked);
    });
    $(document).on('change', '.terapis-checkbox', function () {
        const total = $('.terapis-checkbox').length;
        const checked = $('.terapis-checkbox:checked').length;
        $('#selectAllTerapis')
            .prop('checked', total === checked && total > 0)
            .prop('indeterminate', checked > 0 && checked < total);
    });

    // ─── MODAL MASTER ITEM ──────────────────────────────────────────
    $('#btnTambahTunjangan').on('click', function () {
        $('#modalTitle').text('Tambah Item Gaji');
        $('#formTunjangan')[0].reset();
        $('#id_tunjangan').val('');
        $('.terapis-checkbox').prop('checked', false);
        $('#selectAllTerapis').prop('checked', false);
        $('#modalTunjangan').removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '.btn-close-modal', function () {
        $('#modalTunjangan').removeClass('flex').addClass('hidden');
    });
    $('#modalTunjangan').on('click', function (e) {
        if (e.target === this) $(this).removeClass('flex').addClass('hidden');
    });

    $('#formTunjangan').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnSimpan');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i> Menyimpan...');
        $.ajax({
            url: cfg.urlStore, type: 'POST',
            data: $(this).serialize() + '&' + cfg.csrfName + '=' + cfg.csrfHash,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    $('#modalTunjangan').removeClass('flex').addClass('hidden');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    loadTable();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan' });
                }
            },
            complete: () => btn.prop('disabled', false).html('Simpan')
        });
    });

    // (window.bukaModalGajiPokok is defined globally above)

    $('#btnTutupModalGaji, #btnBatalGaji').on('click', function () {
        $('#modalGajiPokok').removeClass('flex').addClass('hidden');
    });
    $('#modalGajiPokok').on('click', function (e) {
        if (e.target === this) $(this).removeClass('flex').addClass('hidden');
    });

    $('#formGajiPokok').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnSimpanGaji');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
        $.ajax({
            url: cfg.urlSaveGajiPokok, type: 'POST',
            data: $(this).serialize() + '&' + cfg.csrfName + '=' + cfg.csrfHash,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    $('#modalGajiPokok').removeClass('flex').addClass('hidden');
                    Swal.fire({ icon: 'success', title: 'Tersimpan!', text: res.message, timer: 1500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
            },
            complete: () => btn.prop('disabled', false).html('Simpan')
        });
    });
});

// ─── EDIT ITEM MASTER ──────────────────────────────────────────────
window.editItem = function (id) {
    const cfg = window.masterTunjanganConfig;
    $.ajax({
        url: cfg.urlDetail + '/' + id, type: 'GET', dataType: 'json',
        success: function (res) {
            if (!res.data) return;
            const d = res.data;
            $('#modalTitle').text('Edit Item Gaji');
            $('#id_tunjangan').val(d.id);
            $('#nama_tunjangan').val(d.nama_tunjangan);
            $('#kategori').val(d.kategori);
            $('#tipe').val(d.tipe).trigger('change');
            $('#nominal').val(parseInt(d.nominal).toLocaleString('id-ID'));
            const ids = JSON.parse(d.terapis_ids || '[]');
            $('.terapis-checkbox').each(function () {
                $(this).prop('checked', ids.includes(parseInt($(this).val())));
            });
            const total = $('.terapis-checkbox').length;
            const checked = $('.terapis-checkbox:checked').length;
            $('#selectAllTerapis').prop('checked', total === checked && total > 0);
            $('#modalTunjangan').removeClass('hidden').addClass('flex');
        }
    });
};

// ─── DELETE ITEM MASTER ────────────────────────────────────────────
window.deleteItem = function (id) {
    const cfg = window.masterTunjanganConfig;
    Swal.fire({
        title: 'Hapus item ini?', text: 'Tindakan ini tidak bisa dibatalkan.', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#0f172a',
        confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: cfg.urlDelete + '/' + id, type: 'POST',
            data: { [cfg.csrfName]: cfg.csrfHash }, dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                Swal.fire({ icon: 'success', title: 'Dihapus!', timer: 1200, showConfirmButton: false });
                setTimeout(() => location.reload(), 1200);
            }
        });
    });
};

// ─── VIEW TERAPIS LIST ─────────────────────────────────────────────
window.viewTerapisList = function (id) {
    if (!window.masterItemsData) {
        console.error("masterItemsData is not initialized!");
        return;
    }
    const item = window.masterItemsData.find(x => parseInt(x.id) === parseInt(id));
    if (!item) {
        console.warn("Item not found in masterItemsData for ID: " + id, window.masterItemsData);
        return;
    }

    let titleText = '';
    let categoryText = '';
    
    if (item.kategori.includes('Tunjangan')) {
        titleText = 'Penerima Tunjangan';
        categoryText = 'tunjangan';
    } else if (item.kategori.includes('Benefit')) {
        titleText = 'Penerima Benefit';
        categoryText = 'benefit';
    } else {
        titleText = 'Penerima Potongan';
        categoryText = 'potongan';
    }

    const name = item.nama_tunjangan;
    const listNames = item.terapis_names || [];

    let htmlContent = `
        <div class="text-left">
            <!-- Header -->
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="fas fa-eye text-indigo-500"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 leading-tight">${titleText}</h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">${name}</p>
                </div>
            </div>
    `;

    if (listNames.length === 0) {
        htmlContent += `
            <div class="text-center py-8">
                <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-slash text-base"></i>
                </div>
                <p class="text-xs text-slate-500 font-semibold">Belum ada terapis yang mendapatkan ${categoryText} ini.</p>
            </div>
        `;
    } else {
        htmlContent += `
            <div class="space-y-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1">Total: ${listNames.length} Terapis</p>
                <div class="max-h-60 overflow-y-auto border border-slate-100 rounded-xl p-1.5 bg-white space-y-0.5 custom-scrollbar">
        `;
        
        listNames.forEach((nama) => {
            htmlContent += `
                <div class="flex items-center gap-3 py-2 px-2.5 hover:bg-slate-50 rounded-lg transition duration-150">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        ${nama.charAt(0).toUpperCase()}
                    </div>
                    <span class="text-sm font-semibold text-slate-700 truncate">${nama}</span>
                </div>
            `;
        });
        
        htmlContent += `
                </div>
            </div>
        `;
    }

    htmlContent += `</div>`;

    Swal.fire({
        html: htmlContent,
        width: '440px',
        showConfirmButton: true,
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#0f172a',
        customClass: {
            popup: 'rounded-2xl p-6',
            confirmButton: 'px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm w-full transition active:scale-95'
        }
    });
};
</script>
<?= $this->endSection(); ?>
