<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight"><?= $title ?></h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar jabatan dan hak akses secara efisien.</p>
        </div>
        <a href="#" class="btn btn-primary flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold hover:bg-teal-700 transition-all shadow-md shadow-teal-500/20 border-0 outline-none" data-toggle="modal" data-target="#jabatanModal">
            <i class="fas fa-plus"></i> Tambah Data
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Daftar Jabatan</h2>
            </div>
            <div id="search-container" class="relative w-full md:w-80 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-20">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table id="table-jabatan" class="w-full text-left border-collapse table table-striped">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 whitespace-nowrap border-0">No</th>
                        <th class="px-6 py-4 whitespace-nowrap border-0">Nama</th>
                        <th class="px-6 py-4 whitespace-nowrap border-0">Deskripsi</th>
                        <th class="px-6 py-4 whitespace-nowrap text-right border-0">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="jabatanModal" class="modal fade hidden" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header bg-white border-b border-slate-100 p-6">
                <h5 class="modal-title text-lg font-black text-slate-800">Tambah Data Jabatan</h5>
                <button type="button" class="close text-slate-400 hover:text-red-500 outline-none" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addJabatanForm" action="<?= base_url('jabatan/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-6 space-y-4">
                    <div class="form-group mb-0">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Nama Jabatan</label>
                        <input type="text" class="form-control w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white" id="add_name" name="name" required>
                        <div class="invalid-feedback text-xs font-bold mt-1" id="add_nameError">Nama jabatan tidak boleh kosong</div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Deskripsi</label>
                        <input type="text" class="form-control w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white" id="add_deskripsi" name="deskripsi">
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 p-6 flex justify-end gap-3">
                    <button type="button" class="btn btn-secondary px-5 py-2.5 rounded-xl text-sm font-bold border-0 shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold border-0 shadow-md shadow-teal-500/20 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed" id="add_submitBtn" disabled>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal_edit_jabatan" class="modal fade hidden" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header bg-white border-b border-slate-100 p-6">
                <h5 class="modal-title text-lg font-black text-slate-800">Ubah Data Jabatan</h5>
                <button type="button" class="close text-slate-400 hover:text-red-500 outline-none" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editjabatanForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-6 space-y-4">
                    <div class="form-group mb-0">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Nama Jabatan</label>
                        <input type="text" class="form-control w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white" id="edit_name" name="name" required>
                        <div class="invalid-feedback text-xs font-bold mt-1" id="edit_nameError">Nama Jabatan tidak boleh kosong</div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Deskripsi</label>
                        <input type="text" class="form-control w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white" id="edit_deskripsi" name="deskripsi">
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 p-6 flex justify-end gap-3">
                    <button type="button" class="btn btn-secondary px-5 py-2.5 rounded-xl text-sm font-bold border-0 shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold border-0 shadow-md shadow-teal-500/20 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed" id="edit_submitBtn" disabled>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.jabatanConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('jabatan/fetch') ?>",
        checkNameUrl: "<?= base_url('jabatan/check_name_exists') ?>",
        flashSuccess: "<?= session()->getFlashdata('success') ?? '' ?>",
        flashError: "<?= session()->getFlashdata('error') ?? '' ?>"
    };
</script>

<script src="<?= base_url('assets/js/page/jabatan.js') ?>"></script>
<?= $this->endSection() ?>