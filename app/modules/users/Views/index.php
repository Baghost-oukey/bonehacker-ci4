<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="usersPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data pengguna dan hak akses sistem
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" data-modal-open="modalAdd"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah User
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <!-- HEADER -->
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <!-- TITLE SECTION -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    Data Users
                </h3>
                <p class="text-sm text-slate-500">
                    Daftar pengguna yang terdaftar dalam sistem
                </p>
            </div>

            <!-- FILTERS & ACTIONS ROW -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- LEFT: SEARCH -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari user..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table id="table-user" class="w-full text-sm">
                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">No</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Username</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Role</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Wilayah</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data user...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION & INFO -->
        <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- LEFT: SHOW ENTRIES & INFO -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <!-- SHOW ENTRIES -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-slate-600">Tampilkan</label>
                        <select id="paginationLength"
                            class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs font-medium text-slate-600">data per halaman</span>
                    </div>

                    <!-- INFO TEXT -->
                    <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                        <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                    </div>
                </div>

                <!-- RIGHT: PAGINATION BUTTONS -->
                <div class="flex items-center justify-center gap-1.5 sm:justify-end">
                    <button id="paginationPrev"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-300">
                        <i class="fas fa-chevron-left text-xs mr-1"></i>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>
                    <div id="paginationNumbers" class="flex items-center gap-1"></div>
                    <button id="paginationNext"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-300">
                        <span class="hidden sm:inline">Berikutnya</span>
                        <i class="fas fa-chevron-right text-xs ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            Data user dengan hak akses sistem
        </div>
    </div>
</section>

<!-- Modal Tambah User -->
<div id="modalAdd" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Data User</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formAddUser" action="<?= base_url('users/store') ?>" method="post" class="space-y-4 p-5 needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="realname"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama user tidak boleh kosong</div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" id="username_add"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="username-feedback text-xs mt-1 hidden"></div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Password tidak boleh kosong</div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Role <span class="text-red-500">*</span></label>
                <select name="role" id="role_add" data-target="#regionFieldAdd"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                    <option value="">-- Pilih Role --</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">Admin</option>
                </select>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Role harus dipilih</div>
            </div>

            <div class="space-y-1 hidden" id="regionFieldAdd">
                <label class="text-sm font-medium text-slate-700">Wilayah</label>
                <select name="regions_patient[]" id="regions_add" multiple
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= $region->name ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-400 mt-1">*Dapat memilih lebih dari satu wilayah</p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="submitBtnAdd"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div id="modalEdit" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Ubah Data User</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formEditUser" action="" method="post" class="space-y-4 p-5 needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="realname" id="edit_realname"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama user tidak boleh kosong</div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" id="edit_username"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="edit-username-feedback text-xs mt-1 hidden"></div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Password <small class="text-slate-400">(Kosongkan jika tidak diubah)</small></label>
                <input type="password" name="password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    placeholder="••••••••">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Role <span class="text-red-500">*</span></label>
                <select name="role" id="edit_role" data-target="#regionFieldEdit"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                    <option value="superadmin">Super Admin</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Role harus dipilih</div>
            </div>

            <div class="space-y-1 hidden" id="regionFieldEdit">
                <label class="text-sm font-medium text-slate-700">Wilayah</label>
                <select name="regions_patient[]" id="edit_regions" multiple
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= $region->name ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-400 mt-1">*Dapat memilih lebih dari satu wilayah</p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="submitBtnEdit"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.</p>

        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button id="confirmDelete"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Script -->
<script>
    window.usersConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('users/fetch') ?>",
        storeUrl: "<?= base_url('users/store') ?>",
        checkUsernameUrl: "<?= base_url('users/check_username_exists') ?>",
        baseUrl: "<?= base_url('users') ?>"
    };
</script>

<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?= $this->endSection() ?>
