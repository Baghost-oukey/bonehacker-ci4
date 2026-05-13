<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="w-full md:w-auto">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tunjangan Karyawan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">Kelola tunjangan harian atau input massal.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-blue-50 border border-blue-100 text-blue-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500/20 shadow-sm">
                <option value="<?= site_url('gaji') ?>">Gaji Karyawan</option>
                <option value="<?= site_url('transaksi-tunjangan') ?>" selected>Tunjangan Terapis</option>
                <option value="<?= site_url('master-gaji') ?>">Master Gaji</option>
                <option value="<?= site_url('kasbon') ?>">Kasbon Karyawan</option>
            </select>
        </div>

        <button id="btnInputMassal" class="w-full md:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2 font-black uppercase tracking-widest">
            <i class="fas fa-users text-base"></i>
            Input Tunjangan Massal
        </button>
    </div>

    <!-- TAMPILAN DESKTOP (TABLE) -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        <table id="table-tunjangan-terapis" class="table-auto w-full text-left border-collapse">
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
        <!-- Rendered via JS -->
        <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
            Memuat data terapis...
        </div>
    </div>
</div>

<div id="modalMassal" class="modal-wrapper fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-black text-slate-800 text-lg">Setting Tunjangan Massal</h3>
                <p class="text-xs text-slate-500 mt-0.5">Set tunjangan ke semua terapis aktif di cabang ini sekaligus</p>
            </div>
            <button class="close-modal text-slate-400 hover:text-slate-600 p-2"><i class="fas fa-times"></i></button>
        </div>
        <form id="formMassal" class="p-6 space-y-5">
            <?= csrf_field(); ?>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Jenis Tunjangan <span class="text-red-500">*</span></label>
                <select name="tunjangan_karyawan_id" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">-- Pilih Jenis --</option>
                    <?php foreach ($master_tunjangan as $mt): ?>
                        <option value="<?= $mt['id'] ?>"><?= esc($mt['nama_tunjangan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Tipe Pemberian <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe" value="bulanan" class="peer sr-only" checked>
                        <div class="flex flex-col items-center gap-1 rounded-xl border-2 border-slate-200 bg-white p-3 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50">
                            <i class="fas fa-calendar-check text-slate-400 text-base"></i>
                            <span class="text-xs font-bold text-slate-600">Bulanan</span>
                            <span class="text-[10px] text-slate-400">Nominal tetap/bulan</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe" value="harian" class="peer sr-only">
                        <div class="flex flex-col items-center gap-1 rounded-xl border-2 border-slate-200 bg-white p-3 text-center transition peer-checked:border-amber-500 peer-checked:bg-amber-50">
                            <i class="fas fa-sun text-slate-400 text-base"></i>
                            <span class="text-xs font-bold text-slate-600">Harian</span>
                            <span class="text-[10px] text-slate-400">Nominal × hari hadir</span>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Nominal (Rp) <span class="text-red-500">*</span></label>
                <input type="text" name="nominal" id="inputNominalMassal" required placeholder="Contoh: 100.000"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-indigo-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <p id="keteranganNominalMassal" class="text-xs text-slate-400 mt-1">Nominal per bulan untuk semua terapis aktif</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700">
                <i class="fas fa-info-circle mr-1"></i>
                Jika terapis sudah punya setting tunjangan jenis ini, nominalnya akan <strong>diperbarui</strong>.
            </div>

            <button type="submit" id="btnSimpanMassal"
                class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-black uppercase tracking-widest text-white shadow-md transition hover:bg-indigo-700">
                <i class="fas fa-users mr-2"></i> Terapkan ke Semua Terapis
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.transaksiTunjanganConfig = {
        csrfName:       "<?= csrf_token() ?>",
        csrfHash:       "<?= csrf_hash() ?>",
        urlFetch:       "<?= base_url('transaksi-tunjangan/fetch') ?>",
        urlStore:       "<?= base_url('transaksi-tunjangan/store') ?>",
        urlDetail:      "<?= base_url('transaksi-tunjangan/detail') ?>",
        urlSaveMassal:  "<?= base_url('transaksi-tunjangan/save-setting-massal') ?>"
    };
</script>
<?= $this->endSection() ?>