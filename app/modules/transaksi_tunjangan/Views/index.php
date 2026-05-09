<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Tunjangan Karyawan</h1>
            <p class="text-sm text-slate-500 mt-1.5 font-medium">Pilih karyawan untuk mengelola tunjangan harian atau input massal.</p>
        </div>
        <button id="btnInputMassal" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center gap-2 font-black uppercase tracking-widest">
            <i class="fas fa-users text-lg"></i>
            Input Tunjangan Semua
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        <table id="table-tunjangan-terapis" class="table-auto w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-widest">
                    <th class="py-4 px-4 w-12 text-center">No</th>
                    <th class="py-4 px-4">Nama Terapis</th>
                    <th class="py-4 px-4 text-center">Jabatan</th>
                    <th class="py-4 px-4 text-center">Status Keuangan</th>
                    <th class="py-4 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
            </tbody>
        </table>
    </div>
</div>

<div id="modalMassal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg">Distribusi Tunjangan Massal</h3>
            <button class="close-modal text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="formMassal" class="p-8 space-y-5">
            <?= csrf_field(); ?>
            <input type="hidden" name="tipe_input" value="massal">

            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-4">
                <p class="text-[11px] text-indigo-700 font-bold uppercase tracking-tight">Info:</p>
                <p class="text-[10px] text-indigo-600 leading-tight">Tunjangan ini akan dibagikan ke seluruh terapis dengan status <span class="font-black">AKTIF</span> secara otomatis.</p>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Pilih Jenis Tunjangan</label>
                <select name="tunjangan_karyawan_id" required class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Jenis --</option>
                    <?php foreach ($master_tunjangan as $mt): ?>
                        <option value="<?= $mt['id'] ?>"><?= esc($mt['nama_tunjangan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Nominal (Rp)</label>
                    <input type="text" name="nominal" id="inputNominalMassal" required placeholder="Rp 0" class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <button type="submit" id="btnSimpanMassal" class="w-full bg-indigo-600 py-4 rounded-2xl text-white font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                Proses Distribusi
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.transaksiTunjanganConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        urlFetch: "<?= base_url('transaksi-tunjangan/fetch') ?>",
        urlStore: "<?= base_url('transaksi-tunjangan/store') ?>",
        urlDetail: "<?= base_url('transaksi-tunjangan/detail') ?>"
    };
</script>
<?= $this->endSection() ?>