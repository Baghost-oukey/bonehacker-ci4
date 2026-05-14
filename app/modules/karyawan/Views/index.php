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
                    <tr class="border-b border-slate-100 bg-slate-50/30">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Lengkap</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Username</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Role</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Wilayah</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
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
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Tambah Karyawan Baru</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formAddUser" action="<?= base_url('karyawan/store') ?>" method="post" class="space-y-5 p-6 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
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
                <select name="role" id="role_add" data-target="#extraTerapisFields" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" required>
                    <option value="">-- Pilih Akses --</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">User (Terapis)</option>
                </select>
            </div>

            <!-- REGION FOR MANAGEMENT -->
            <div id="regionFieldAdd" class="hidden space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Wilayah Pantauan Pasien</label>
                <select name="regions[]" id="regions_add" multiple class="w-full select2-regions">
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= $r->id ?>"><?= $r->name ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[9px] font-bold text-slate-400 mt-1 italic leading-tight">* Admin/Owner dapat memantau data pasien di wilayah yang dipilih.</p>
            </div>

            <!-- EXTRA FIELDS FOR THERAPIST -->
            <div id="extraTerapisFields" class="hidden space-y-4 border-t border-slate-100 pt-4 mt-4">
                <div class="p-5 bg-teal-50/50 rounded-3xl border border-teal-100 space-y-4">
                    <h6 class="text-[10px] font-black text-teal-600 uppercase tracking-[0.2em] flex items-center gap-2">
                        <i class="fas fa-id-card"></i> Data Profil Terapis
                    </h6>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">ID Terapis (NIK)</label>
                            <input type="text" name="terapis_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white transition-all" placeholder="TRP-001">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Jabatan</label>
                            <select name="jabatan_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white">
                                <option value="">-- Pilih Jabatan --</option>
                                <?php foreach ($jabatan as $j): ?>
                                    <option value="<?= $j->id ?>"><?= $j->nama_jabatan ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white" placeholder="Kota Kelahiran">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Alamat Domisili</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white" placeholder="Alamat lengkap..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Wilayah Penempatan</label>
                            <select name="region_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white">
                                <option value="">-- Pilih Cabang --</option>
                                <?php foreach ($regions as $r): ?>
                                    <option value="<?= $r->id ?>"><?= $r->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Rank</label>
                            <select name="rank" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-white">
                                <option value="SS">SS (Supreme)</option>
                                <option value="S">S (Senior)</option>
                                <option value="A">A (Standard)</option>
                                <option value="B">B (Junior)</option>
                                <option value="C">C (Apprentice)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" data-modal-close class="px-6 py-3 rounded-2xl border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnAdd" class="px-8 py-3.5 rounded-2xl bg-teal-600 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-teal-500/20 hover:bg-teal-700 transition-all">Simpan Personel</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ACCOUNT -->
<div id="modalEdit" class="modal-wrapper hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Edit Akun Personel</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditUser" action="" method="post" class="space-y-5 p-6 max-h-[75vh] overflow-y-auto needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <input type="text" name="realname" id="edit_realname" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 shadow-inner">
                    <div class="edit-username-feedback text-[9px] font-bold uppercase tracking-tighter mt-1 hidden"></div>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Password Baru (Opsional)</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 shadow-inner" placeholder="Kosongkan jika tidak diubah">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Level Akses (Role)</label>
                <select name="role" id="edit_role" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 outline-none bg-slate-50 shadow-inner" required>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">User (Terapis)</option>
                </select>
            </div>

            <div id="regionFieldEdit" class="hidden space-y-1.5">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Wilayah Pantauan Pasien</label>
                <select name="regions[]" id="edit_regions" multiple class="w-full select2-regions">
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= $r->id ?>"><?= $r->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" data-modal-close class="px-6 py-3 rounded-2xl border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtnEdit" class="px-8 py-3.5 rounded-2xl bg-teal-600 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-teal-500/20 hover:bg-teal-700 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.karyawanConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('karyawan/fetch') ?>",
        checkUsernameUrl: "<?= base_url('karyawan/checkUsername') ?>",
        viewPatientUrl: "<?= base_url('karyawan/view_patient') ?>"
    };
</script>

<?= $this->endSection() ?>
