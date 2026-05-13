<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div id="masterTunjanganPage" class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Master Gaji</h2>
            <p class="text-sm text-slate-500">Kelola jenis tunjangan dan potongan karyawan.</p>
        </div>
        <button id="btnTambahTunjangan" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition duration-150 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Item
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5">
            <table id="tableTunjangan" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-sm uppercase tracking-wider border-b border-slate-200">
                        <th class="p-4 font-semibold w-16 text-center">No</th>
                        <th class="p-4 font-semibold">Nama Item</th>
                        <th class="p-4 font-semibold w-40">Kategori</th>
                        <th class="p-4 font-semibold w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTunjangan" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 transition-all duration-300">
    
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl transform transition-all animate-in zoom-in-95 duration-200">
        
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-white">
            <h5 id="modalTitle" class="text-lg font-bold text-slate-800 tracking-tight">Tambah Item Gaji</h5>
            <button type="button" class="btn-close-modal rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-800 transition-colors">&times;</button>
        </div>

        <form id="formTunjangan" class="space-y-5 p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="id_tunjangan" name="id">

            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700">Nama Item</label>
                <input type="text" id="nama_tunjangan" name="nama_tunjangan" 
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all bg-slate-50/50" 
                    placeholder="Contoh: BPJS Kesehatan, Tunjangan Makan" required>
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700">Kategori</label>
                <select id="kategori" name="kategori" 
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all bg-white" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="tunjangan">Tunjangan (Benefit)</option>
                    <option value="potongan">Potongan</option>
                </select>
                <p class="text-xs text-slate-400">Tunjangan = ditanggung perusahaan. Potongan = dipotong dari gaji karyawan.</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-2">
                <button type="button" class="btn-close-modal rounded-xl border border-slate-300 px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" id="btnSimpan" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-bold text-white hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all">
                    <i class="fas fa-save mr-1.5"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    window.masterTunjanganConfig = {
        csrfName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        urlFetch: '<?= base_url('master-gaji/fetch') ?>',
        urlStore: '<?= base_url('master-gaji/store') ?>',
        urlDelete: '<?= base_url('master-gaji/delete') ?>'
    };
</script>

<?= $this->endSection(); ?>