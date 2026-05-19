<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="usersPage" class="w-full px-4 py-6 space-y-6 overflow-x-hidden">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-800 uppercase">Manajemen Karyawan</h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1 uppercase tracking-widest">Kelola Akun & Profil Personel Bonehacker</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" data-modal-open="modalAdd" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-xs font-black tracking-widest uppercase shadow-xl shadow-teal-500/20 hover:bg-teal-700 active:scale-95 transition-all">
                <i class="fas fa-plus"></i> Tambah Karyawan Baru
            </button>
        </div>
    </div>

    <!-- MAIN TABLE -->
    <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md w-full">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="searchInput" placeholder="Cari nama, username, atau ID..." class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-white transition-all shadow-inner">
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tampilkan:</span>
                    <select id="paginationLength" class="bg-transparent text-xs font-black text-slate-700 border-none focus:ring-0 cursor-pointer">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <!-- Desktop Table -->
            <table id="table-user" class="hidden md:table w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-50 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-5 whitespace-nowrap">No</th>
                        <th class="px-6 py-5 whitespace-nowrap">Nama Lengkap</th>
                        <th class="px-6 py-5 whitespace-nowrap">Username</th>
                        <th class="px-6 py-5 whitespace-nowrap">Role</th>
                        <th class="px-6 py-5 whitespace-nowrap">Wilayah</th>
                        <th class="px-6 py-5 whitespace-nowrap text-center">Status</th>
                        <th class="px-6 py-5 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50"></tbody>
            </table>

            <!-- Mobile List -->
            <div id="mobile-users-container" class="md:hidden divide-y divide-slate-100"></div>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-5 border-t border-slate-100 bg-slate-50/50">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p id="paginationInfo" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]"></p>
                <div class="flex items-center gap-3">
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
    <div class="bg-white rounded-t-3xl md:rounded-[32px] shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6 bg-white">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Tambah Karyawan Baru</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formAddUser" action="<?= base_url('karyawan/store') ?>" method="post" class="space-y-5 px-8 py-6 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Nama Lengkap</label>
                    <input type="text" name="realname" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 focus:bg-white transition-all shadow-inner" placeholder="Masukkan nama asli">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Username</label>
                    <input type="text" name="username" id="username_add" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 focus:bg-white transition-all shadow-inner" placeholder="Pilih username unik">
                    <div class="username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Password Baru</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 focus:bg-white transition-all shadow-inner" placeholder="Minimal 6 karakter">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Level Akses (Role)</label>
                <select name="role" id="role_add" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 focus:bg-white transition-all shadow-inner cursor-pointer" required>
                    <option value="">-- Pilih Akses --</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">Terapis</option>
                </select>
            </div>

            <!-- REGION PATIENT (FOR OWNER/ADMIN ONLY) -->
            <div class="space-y-2 hidden" id="regionFieldAdd">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Otoritas Wilayah Pasien</label>
                <select name="regions_patient[]" id="regions_add" multiple class="w-full select2-teal">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= $region->name ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[9px] font-bold text-slate-400 mt-1.5 italic leading-tight">* Admin/Owner dapat memantau data pasien di wilayah yang dipilih.</p>
            </div>

            <!-- EXTRA FIELDS FOR THERAPIST -->
            <div id="extraTerapisFields" class="hidden space-y-4 border-t border-slate-100 pt-4 mt-4">
                <div class="p-5 bg-teal-50/50 rounded-3xl border border-teal-100 space-y-4">
                    <h6 class="text-[10px] font-black text-teal-600 uppercase tracking-[0.2em] flex items-center gap-2">
                        <i class="fas fa-id-card"></i> Data Profil Terapis
                    </h6>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">ID Terapis (NIK)</label>
                            <input type="text" name="terapis_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white transition-all shadow-sm" placeholder="TRP-001">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Jabatan</label>
                            <select name="jabatan_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm">
                                <option value="">-- Pilih Jabatan --</option>
                                <?php foreach ($jabatan as $j): ?>
                                    <option value="<?= $j->id ?>"><?= $j->nama_jabatan ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm" placeholder="Kota Kelahiran">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Alamat Domisili</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm" placeholder="Alamat lengkap..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Wilayah Penempatan</label>
                            <select name="region_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm">
                                <option value="">-- Pilih Wilayah --</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region->id ?>"><?= $region->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Rank / Level</label>
                            <select name="rank" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm">
                                <option value="">-- Pilih Rank --</option>
                                <?php foreach (($rank_options ?? []) as $rank): ?>
                                    <option value="<?= esc($rank->name) ?>"><?= esc($rank->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Tgl Mulai Kerja</label>
                            <input type="date" name="tgl_mulai_kerja" max="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Foto Profil</label>
                            <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center gap-4 pt-6 border-t border-slate-100 shrink-0">
                <button type="button" data-modal-close class="px-10 py-3.5 rounded-full border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnAdd" class="px-10 py-3.5 rounded-full bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-teal-500/20 active:scale-95 transition-all">Simpan Personel</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ACCOUNT -->
<div id="modalEdit" class="modal-wrapper hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-[32px] shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6 bg-white">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Edit Akun Personel</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditUser" action="#" method="post" class="space-y-5 px-8 py-6 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Nama Lengkap</label>
                    <input type="text" name="realname" id="edit_realname" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 shadow-inner">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Username</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 shadow-inner">
                    <div class="edit-username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Password Baru (Opsional)</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 shadow-inner" placeholder="Kosongkan jika tidak diubah">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Level Akses (Role)</label>
                <select name="role" id="edit_role" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 shadow-inner cursor-pointer" required>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">User (Terapis)</option>
                </select>
            </div>

            <div id="regionFieldEdit" class="hidden space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Otoritas Wilayah Pasien</label>
                <select name="regions_patient[]" id="edit_regions" multiple class="w-full select2-regions">
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= $r->id ?>"><?= $r->name ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[9px] font-bold text-slate-400 mt-1.5 italic leading-tight">* Admin/Owner dapat memantau data pasien di wilayah yang dipilih.</p>
            </div>

            <div class="flex items-center justify-center gap-4 pt-6 border-t border-slate-100 shrink-0">
                <button type="button" data-modal-close class="px-10 py-3.5 rounded-full border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnEdit" class="px-10 py-3.5 rounded-full bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-teal-500/20 active:scale-95 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Quick Create Account (Simplified) -->
<div id="modalQuickCreateAccount" class="modal-wrapper hidden fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-[32px] shadow-2xl w-full max-w-md overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Buat Akun Login</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Akses Sistem Terapis</p>
            </div>
            <button type="button" data-modal-close class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        
        <form id="formQuickCreateAccount" action="#" class="p-8 space-y-5">
            <input type="hidden" id="quick-karyawan-id" name="karyawan_id">
            
            <div class="bg-teal-50/50 rounded-2xl p-4 border border-teal-100/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white text-teal-600 flex items-center justify-center shadow-sm border border-teal-100">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-teal-600 uppercase tracking-widest">Terapis</p>
                    <p id="quick-realname" class="text-sm font-black text-slate-800"></p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Username</label>
                <input type="text" id="quick-username" name="username" required 
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 focus:bg-white transition-all shadow-inner" 
                    placeholder="Masukkan username">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1">Password</label>
                <div class="relative">
                    <input type="password" id="quick-password" name="password" required 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50/50 focus:bg-white transition-all shadow-inner" 
                        placeholder="Minimal 6 karakter" minlength="6" value="password123">
                    <button type="button" onclick="const p=document.getElementById('quick-password'); p.type=p.type==='password'?'text':'password'; this.querySelector('i').classList.toggle('fa-eye'); this.querySelector('i').classList.toggle('fa-eye-slash');" 
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tight mt-1.5 ml-1">Default: password123</p>
            </div>

            <div class="flex items-center justify-center gap-4 pt-6 border-t border-slate-100 shrink-0">
                <button type="button" data-modal-close class="px-10 py-3.5 rounded-full border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitQuickAccount" class="px-10 py-3.5 rounded-full bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-teal-500/20 active:scale-95 transition-all">Simpan & Buat Akun</button>
            </div>
        </form>
      </div>
</div>

<style>
    /* Select2 Modern Overrides to perfectly match premium rounded-2xl inputs */
    .select2-container--default .select2-selection--multiple {
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        padding: 5px 12px !important;
        min-height: 46px !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        outline: none !important;
        transition: all 0.2s ease-in-out !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #0d9488 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1) !important;
    }
    .select2-selection__choice {
        background-color: #ccfbf1 !important;
        border: 1px solid #99f6e4 !important;
        color: #0f766e !important;
        border-radius: 9999px !important;
        font-weight: bold !important;
        font-size: 10px !important;
        padding: 2px 10px !important;
        margin: 2px 4px !important;
    }
    .select2-selection__choice__remove {
        color: #0d9488 !important;
        margin-right: 5px !important;
    }
    .select2-selection__choice__remove:hover {
        color: #0f766e !important;
        background-color: transparent !important;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        margin-top: 0 !important;
        font-size: 13px !important;
        font-family: inherit !important;
        color: #475569 !important;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
<?= $this->endSection() ?>
