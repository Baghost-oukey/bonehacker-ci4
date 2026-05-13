<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="usersPage" class="w-full px-2 py-4 md:px-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-800 uppercase">Manajemen User</h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Kelola data pengguna dan wewenang sistem secara terpusat.</p>
        </div>
        <button type="button" data-modal-open="modalAdd" class="w-full md:w-auto flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black hover:bg-teal-700 transition-all shadow-lg shadow-teal-500/25 active:scale-[0.98] outline-none">
            <i class="fas fa-plus text-xs"></i> 
            <span class="uppercase tracking-widest">Tambah User Baru</span>
        </button>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- CONTROL BAR -->
        <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="hidden sm:block">
                    <h2 class="text-lg font-black text-slate-800">Daftar Pengguna</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Sistem Keamanan & Akses</p>
                </div>
                <div class="relative w-full md:w-80 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                        <i class="fas fa-search text-slate-300 group-focus-within:text-teal-500 transition-colors"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan nama atau username..."
                        class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none shadow-inner">
                </div>
            </div>
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block overflow-x-auto w-full">
            <table id="table-user" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-50 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-5 whitespace-nowrap">No</th>
                        <th class="px-6 py-5 whitespace-nowrap">Nama Lengkap</th>
                        <th class="px-6 py-5 whitespace-nowrap">Username</th>
                        <th class="px-6 py-5 whitespace-nowrap">Role</th>
                        <th class="px-6 py-5 whitespace-nowrap">Cakupan Wilayah</th>
                        <th class="px-6 py-5 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    <!-- Data will be injected via JS -->
                </tbody>
            </table>
        </div>

        <!-- Mobile View (Card Container) -->
        <div id="mobile-users-container" class="md:hidden divide-y divide-slate-50">
            <!-- Cards will be injected via JS -->
        </div>

        <!-- Footer / Pagination -->
        <div class="p-6 bg-slate-50/50 border-t border-slate-100">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tampilkan</label>
                        <select id="paginationLength" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 outline-none focus:border-teal-500 transition-all shadow-sm">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic" id="paginationInfo">
                        Menampilkan 0 - 0 dari 0 data
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2">
                    <button id="paginationPrev" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 transition-all disabled:opacity-20">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                    </button>
                    <div id="paginationNumbers" class="flex items-center gap-1.5"></div>
                    <button id="paginationNext" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 transition-all disabled:opacity-20">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ADD USER -->
<div id="modalAdd" class="modal-wrapper hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Tambah User Baru</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formAddUser" action="<?= base_url('users/store') ?>" method="post" class="space-y-5 p-6 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <input type="text" name="realname" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" placeholder="Masukkan nama asli">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                    <input type="text" name="username" id="username_add" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" placeholder="Pilih username unik">
                    <div class="username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Password Baru</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" placeholder="Minimal 6 karakter">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Level Akses (Role)</label>
                <select name="role" id="role_add" data-target="#regionFieldAdd" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" required>
                    <option value="">-- Pilih Akses --</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">User (Terapis)</option>
                </select>
            </div>
            <div class="space-y-1.5 hidden" id="regionFieldAdd">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Otoritas Wilayah</label>
                <select name="regions_patient[]" id="regions_add" multiple class="w-full select2-teal">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= $region->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col md:flex-row gap-3 border-t border-slate-100 pt-5 mt-4">
                <button type="button" data-modal-close class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnAdd" class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div id="modalEdit" class="modal-wrapper hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Edit Data User</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditUser" action="" method="post" class="space-y-5 p-6 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <input type="text" name="realname" id="edit_realname" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                    <div class="edit-username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Ganti Password <span class="normal-case italic opacity-50">(Kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" placeholder="••••••••">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Level Akses (Role)</label>
                <select name="role" id="edit_role" data-target="#regionFieldEdit" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" required>
                    <option value="superadmin">Super Admin</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="user">User (Terapis)</option>
                </select>
            </div>
            <div class="space-y-1.5 hidden" id="regionFieldEdit">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Otoritas Wilayah</label>
                <select name="regions_patient[]" id="edit_regions" multiple class="w-full select2-teal">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= $region->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col md:flex-row gap-3 border-t border-slate-100 pt-5 mt-4">
                <button type="button" data-modal-close class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnEdit" class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Select2 Modern Overrides */
    .select2-container--default .select2-selection--multiple {
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        padding: 4px 8px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #0d9488 !important;
        background-color: #ffffff !important;
    }
    .select2-selection__choice {
        background-color: #ccfbf1 !important;
        border: 1px solid #99f6e4 !important;
        color: #0f766e !important;
        border-radius: 8px !important;
        font-weight: bold !important;
        font-size: 11px !important;
        padding: 2px 8px !important;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.usersConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('users/fetch') ?>",
        storeUrl: "<?= base_url('users/store') ?>",
        checkUsernameUrl: "<?= base_url('users/check_username_exists') ?>",
        viewPatientUrl: "<?= base_url('users/view_patient') ?>",
        baseUrl: "<?= base_url('users') ?>"
    };
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?= $this->endSection() ?>
