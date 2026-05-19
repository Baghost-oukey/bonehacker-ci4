<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="usersPage" class="w-full px-2 py-4 md:px-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-semibold text-teal-700">Manajemen Karyawan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data akun dan profil karyawan secara terpusat.</p>
        </div>
        <button type="button" data-modal-open="modalAdd" class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition-colors">
            <i class="fas fa-plus-circle text-white"></i> 
            <span>Tambah Karyawan Baru</span>
        </button>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- CONTROL BAR -->
        <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="hidden sm:block">
                    <h2 class="text-lg font-semibold text-slate-800">Daftar Pengguna</h2>
                    <p class="text-xs text-slate-400 mt-1">Sistem Keamanan &amp; Akses</p>
                </div>
                <div class="relative w-full md:w-80 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                        <i class="fas fa-search text-slate-300 group-focus-within:text-teal-500 transition-colors"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan nama atau username..."
                        class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                </div>
            </div>
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block overflow-x-auto w-full">
            <table id="table-user" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 text-left whitespace-nowrap">No</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Nama Lengkap</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Username</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Role</th>
                        <th class="px-6 py-4 text-left whitespace-nowrap">Wilayah</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
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
                        <label class="text-xs font-medium text-slate-500">Tampilkan</label>
                        <select id="paginationLength" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 outline-none focus:border-teal-500 transition-all shadow-sm">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="text-xs font-medium text-slate-500 italic" id="paginationInfo">
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
<div id="modalAdd" class="modal-wrapper hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 bg-white">
            <h5 class="text-lg font-semibold text-slate-800">Tambah User Baru</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none text-2xl font-semibold">
                &times;
            </button>
        </div>

        <form id="formAddUser" action="<?= base_url('karyawan/store') ?>" method="post" class="space-y-4 p-5 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text" name="realname" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="Masukkan nama asli">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Username</label>
                    <input type="text" name="username" id="username_add" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="Pilih username unik">
                    <div class="username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Password Baru</label>
                <input type="password" name="password" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="Minimal 6 karakter">
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Level Akses (Role)</label>
                <select name="role" id="role_add" data-target="#extraTerapisFields" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" required>
                    <option value="">-- Pilih Akses --</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">Terapis</option>
                </select>
            </div>

            <!-- EXTRA FIELDS FOR THERAPIST -->
            <div id="extraTerapisFields" class="hidden space-y-4 border-t border-slate-200 pt-4 mt-4">
                <p class="text-sm font-semibold text-teal-600 mb-2">Data Profil Terapis</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">ID Terapis (NIK/ID)</label>
                        <input type="text" name="terapis_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="Contoh: TSI-001">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jabatan</label>
                        <select name="jabatan_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach ($jabatan as $j): ?>
                                <option value="<?= $j->id ?>"><?= $j->nama_jabatan ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="Kota Kelahiran">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Alamat Domisili</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="Alamat lengkap..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Wilayah Penempatan</label>
                        <select name="region_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                            <option value="">-- Pilih Wilayah --</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= $region->id ?>"><?= $region->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Rank / Level</label>
                        <select name="rank" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                            <option value="">-- Pilih Rank --</option>
                            <?php foreach (($rank_options ?? []) as $rank): ?>
                                <option value="<?= esc($rank->name) ?>"><?= esc($rank->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tgl Mulai Kerja</label>
                        <input type="date" name="tgl_mulai_kerja" max="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Foto Profil</label>
                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- REGION PATIENT (FOR OWNER/ADMIN ONLY) -->
            <div class="hidden" id="regionFieldAdd">
                <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4 space-y-2">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-map-marker-alt text-teal-500 text-xs"></i>
                        <label class="text-sm font-medium text-slate-700">Otoritas Wilayah Pasien</label>
                    </div>
                    <p class="text-xs text-slate-400 mb-3">Pilih wilayah yang bisa dikelola oleh akun ini.</p>
                    <select name="regions_patient[]" id="regions_add" multiple class="w-full select2-teal">
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region->id ?>"><?= $region->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnAdd" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div id="modalEdit" class="modal-wrapper hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 bg-white">
            <h5 class="text-lg font-semibold text-slate-800">Edit Data User</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none text-2xl font-semibold">
                &times;
            </button>
        </div>

        <form id="formEditUser" action="#" method="post" class="space-y-4 p-5 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text" name="realname" id="edit_realname" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Username</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                    <div class="edit-username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Ganti Password <span class="italic font-normal text-slate-400">(Kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" placeholder="••••••••">
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Level Akses (Role)</label>
                <select name="role" id="edit_role" data-target="#regionFieldEdit" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" required>
                    <option value="superadmin">Super Admin</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="user">Terapis</option>
                </select>
            </div>
            <div class="space-y-1 hidden" id="regionFieldEdit">
                <label class="text-sm font-medium text-slate-700">Otoritas Wilayah</label>
                <select name="regions_patient[]" id="edit_regions" multiple class="w-full select2-teal">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= $region->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnEdit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Quick Create Account (Simplified) -->
<div id="modalQuickCreateAccount" class="modal-wrapper hidden fixed inset-0 z-[60] items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Buat Akun Login</h3>
                <p class="text-xs text-slate-400 mt-0.5">Akses Sistem Terapis</p>
            </div>
            <button type="button" data-modal-close class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 text-2xl font-semibold leading-none">
                &times;
            </button>
        </div>
        
        <form id="formQuickCreateAccount" action="#" class="p-5 space-y-4">
            <input type="hidden" id="quick-karyawan-id" name="karyawan_id">
            
            <div class="bg-teal-50/50 rounded-lg p-4 border border-teal-100/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-white text-teal-600 flex items-center justify-center shadow-sm border border-teal-100">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-teal-600">Terapis</p>
                    <p id="quick-realname" class="text-sm font-semibold text-slate-800"></p>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Username</label>
                <input type="text" id="quick-username" name="username" required 
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" 
                    placeholder="Masukkan username">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Password</label>
                <div class="relative">
                    <input type="password" id="quick-password" name="password" required 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white" 
                        placeholder="Minimal 6 karakter" minlength="6">
                    <button type="button" onclick="const p=document.getElementById('quick-password'); p.type=p.type==='password'?'text':'password'; this.querySelector('i').classList.toggle('fa-eye'); this.querySelector('i').classList.toggle('fa-eye-slash');" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-400 mt-1">Isi password awal khusus untuk akun ini.</p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitQuickAccount" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition-all">Simpan</button>
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
    window.karyawanConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('karyawan/fetch') ?>",
        storeUrl: "<?= base_url('karyawan/store') ?>",
        checkUsernameUrl: "<?= base_url('karyawan/check_username_exists') ?>",
        activeUrl: "<?= base_url('karyawan/active') ?>",
        nonActiveUrl: "<?= base_url('karyawan/nonActive') ?>",
        viewPatientUrl: "<?= base_url('karyawan/view_patient') ?>",
        baseUrl: "<?= base_url('karyawan') ?>",
        generateUserUrl: "<?= base_url('karyawan/generate_user') ?>"
    };
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?= $this->endSection() ?>
