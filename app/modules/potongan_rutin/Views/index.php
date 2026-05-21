<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="w-full md:w-auto">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Potongan Rutin</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">Kelola potongan rutin bulanan atau input massal.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full lg:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('gaji') ?>">💵 Gaji Karyawan</option>
                <option value="<?= site_url('transaksi-tunjangan') ?>">💰 Tunjangan Terapis</option>
                <option value="<?= site_url('potongan-rutin') ?>" selected>➖ Potongan Rutin</option>
                <option value="<?= site_url('master-gaji') ?>">⚙️ Master Gaji</option>
                <option value="<?= site_url('kasbon') ?>">💸 Kasbon Karyawan</option>
            </select>
        </div>

        <button id="btnInputMassal" class="w-full md:w-auto px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm rounded-xl transition-all flex items-center justify-center gap-2 font-medium">
            <i class="fas fa-users text-base"></i>
            Input Potongan Massal
        </button>
    </div>

    <!-- TAMPILAN DESKTOP (TABLE) -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        <table id="table-potongan-terapis" class="table-auto w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-widest">
                    <th class="py-4 px-4 w-12 text-center">No</th>
                    <th class="py-4 px-4">Nama Terapis</th>
                    <th class="py-4 px-4 text-center">Jabatan</th>
                    <th class="py-4 px-4 text-center text-nowrap">Status Keuangan</th>
                    <th class="py-4 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
            </tbody>
        </table>
    </div>

    <!-- TAMPILAN MOBILE (CARDS) -->
    <div id="mobile-card-container" class="md:hidden space-y-4 pb-20">
        <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
            Memuat data terapis...
        </div>
    </div>
</div>

<div id="modalMassal" class="modal-wrapper fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h5 class="text-lg font-semibold text-slate-800">Setting Potongan Massal</h5>
                <p class="text-sm text-slate-500 mt-0.5">Set potongan ke semua terapis aktif di cabang ini sekaligus</p>
            </div>
            <button class="close-modal rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>
        <form id="formMassal" class="space-y-4 p-5">
            <?= csrf_field(); ?>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama Potongan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_potongan" required placeholder="Contoh: BPJS Kesehatan, Koperasi"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nominal (Rp) <span class="text-red-500">*</span></label>
                <input type="text" name="nominal" id="inputNominalMassal" required placeholder="Contoh: 100.000"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                <p class="text-xs text-slate-400 mt-1">Nominal potongan per bulan untuk semua terapis aktif</p>
            </div>

            <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-700">
                <i class="fas fa-info-circle mr-1"></i>
                Jika terapis sudah memiliki potongan dengan nama yang sama, nominalnya akan <strong>diperbarui</strong>.
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" class="close-modal rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnSimpanMassal"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    <i class="fas fa-users mr-1.5"></i> Terapkan ke Semua Terapis
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.potonganRutinConfig = {
        csrfName:      "<?= csrf_token() ?>",
        csrfHash:      "<?= csrf_hash() ?>",
        urlFetch:      "<?= base_url('potongan-rutin/fetch') ?>",
        urlDetail:     "<?= base_url('potongan-rutin/detail') ?>",
        urlSaveMassal: "<?= base_url('potongan-rutin/save-setting-massal') ?>"
    };
</script>
<?= $this->endSection() ?>
