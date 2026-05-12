<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="jabatanPage" class="w-full px-2 py-4 md:px-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-800">Pengaturan Jabatan</h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Kelola daftar jabatan dan wewenang tim Anda.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-slate-50 border border-slate-100 text-slate-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-500/20">
                <option value="<?= site_url('logs') ?>">Logs</option>
                <option value="<?= site_url('whatsapp') ?>">WhatsApp</option>
                <option value="<?= site_url('log_whatsapp') ?>">Log WhatsApp</option>
                <option value="<?= site_url('jabatan') ?>" selected>Jabatan</option>
                <option value="<?= site_url('greeting') ?>">Greetings</option>
            </select>
        </div>
        <button type="button" class="w-full md:w-auto flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black hover:bg-teal-700 transition-all shadow-lg shadow-teal-500/25 active:scale-[0.98] outline-none" data-toggle="modal" data-target="#jabatanModal">
            <i class="fas fa-plus text-xs"></i> 
            <span class="uppercase tracking-widest">Tambah Jabatan</span>
        </button>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- CONTROL BAR -->
        <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-800">Daftar Jabatan</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Data Master Jabatan</p>
                </div>
                <div id="search-container" class="relative w-full md:w-80 group">
                    <!-- DataTables search will be injected here -->
                </div>
            </div>
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block overflow-x-auto w-full">
            <table id="table-jabatan" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-50 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-5 whitespace-nowrap">No</th>
                        <th class="px-6 py-5 whitespace-nowrap">Nama Jabatan</th>
                        <th class="px-6 py-5 whitespace-nowrap">Deskripsi</th>
                        <th class="px-6 py-5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50"></tbody>
            </table>
        </div>

        <!-- Mobile View (Card Container) -->
        <div id="mobile-jabatan-container" class="md:hidden divide-y divide-slate-50">
            <!-- Cards will be injected via JS rowCallback -->
        </div>

        <!-- Footer / Pagination Area -->
        <div id="table-footer" class="p-6 bg-white border-t border-slate-50">
            <!-- DataTables pagination will be here -->
        </div>
    </div>
</div>

<!-- MODALS -->
<div id="jabatanModal" class="modal fade hidden" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
            <div class="modal-header bg-white border-b border-slate-100 p-6">
                <h5 class="modal-title text-lg font-black text-slate-800 uppercase tracking-tight">Tambah Jabatan</h5>
                <button type="button" class="close text-slate-400 hover:text-red-500 transition-colors outline-none" data-dismiss="modal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="addJabatanForm" action="<?= base_url('jabatan/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Jabatan</label>
                        <input type="text" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" id="add_name" name="name" required placeholder="Contoh: Admin Utama">
                        <div class="invalid-feedback text-[10px] font-bold text-red-500 uppercase tracking-tighter mt-1" id="add_nameError">Nama jabatan sudah ada.</div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Deskripsi Singkat</label>
                        <textarea class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" id="add_deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan tanggung jawab jabatan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 p-6 flex flex-col md:flex-row gap-3">
                    <button type="button" class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all" data-dismiss="modal">Batal</button>
                    <button type="submit" class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all disabled:opacity-50" id="add_submitBtn" disabled>Simpan Jabatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal_edit_jabatan" class="modal fade hidden" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
            <div class="modal-header bg-white border-b border-slate-100 p-6">
                <h5 class="modal-title text-lg font-black text-slate-800 uppercase tracking-tight">Edit Jabatan</h5>
                <button type="button" class="close text-slate-400 hover:text-red-500 transition-colors outline-none" data-dismiss="modal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="editjabatanForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Jabatan</label>
                        <input type="text" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" id="edit_name" name="name" required>
                        <div class="invalid-feedback text-[10px] font-bold text-red-500 uppercase tracking-tighter mt-1" id="edit_nameError">Nama Jabatan sudah ada.</div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Deskripsi</label>
                        <textarea class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 p-6 flex flex-col md:flex-row gap-3">
                    <button type="button" class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all" data-dismiss="modal">Batal</button>
                    <button type="submit" class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all disabled:opacity-50" id="edit_submitBtn" disabled>Simpan Perubahan</button>
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
<?= $this->endSection() ?>