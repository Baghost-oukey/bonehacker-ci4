<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="resultPage" class="w-full px-4 py-4 md:py-6 space-y-6 overflow-x-hidden">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola tag hasil pemeriksaan untuk kategorisasi data pasien
            </p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('tag-keluhan') ?>">Tag Keluhan</option>
                <option value="<?= site_url('tag-rekam-medis') ?>">Tag Rekam Medis</option>
                <option value="<?= site_url('tag-pemeriksaan') ?>" selected>Tag Pemeriksaan</option>
            </select>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <button type="button" data-modal-open="modalTambah"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-teal-700 w-full md:w-auto shadow-sm active:scale-95">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Tag
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
                    Daftar Tags Hasil Pemeriksaan
                </h3>
                <p class="text-sm text-slate-500">
                    Data tag hasil pemeriksaan yang terdaftar dalam sistem
                </p>
            </div>

            <!-- FILTERS & ACTIONS ROW -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- LEFT: SEARCH -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari tag hasil pemeriksaan..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Result Container (KODE KITA) -->
        <div id="mobile-result-container" class="md:hidden divide-y divide-slate-100">
            <div class="px-6 py-12 text-center text-slate-400 italic text-sm">
                <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                Memuat data tag hasil pemeriksaan...
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto hidden md:block">
            <table id="table-result" class="w-full text-sm">
                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold w-12">No</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Tag</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Deskripsi</th>
                        <th class="px-6 py-3.5 text-center font-semibold w-32">Jumlah Data</th>
                        <th class="px-6 py-3.5 text-center font-semibold w-32">Aksi</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data tag hasil pemeriksaan...
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
            Data tag hasil pemeriksaan yang digunakan untuk kategorisasi
        </div>
    </div>
</section>

<!-- Modal Tambah Tag -->
<div id="modalTambah" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Tag Hasil Pemeriksaan</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="addResultForm" action="<?= site_url('tag-pemeriksaan/store') ?>" method="post" class="space-y-4 p-5">
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama Tag Hasil Pemeriksaan <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="add_name"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="name-feedback text-xs mt-1 hidden"></div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Deskripsi</label>
                <input type="text" name="deskripsi" id="add_deskripsi"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="add_submitBtn"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Tag -->
<div id="modalEdit" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Ubah Tag Hasil Pemeriksaan</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="editResultForm" action="" method="post" class="space-y-4 p-5">
            <?= csrf_field() ?>
            <input type="hidden" id="edit_id" name="id">

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama Tag Hasil Pemeriksaan <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit_name"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    required>
                <div class="edit-name-feedback text-xs mt-1 hidden"></div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Deskripsi</label>
                <input type="text" name="deskripsi" id="edit_deskripsi"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="edit_submitBtn"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Peringatan!</h3>
        <p class="text-sm text-slate-500">Yakin menghapus data ini? Tag ini akan dihapus dari semua riwayat terkait.</p>

        <form id="deleteResultForm" action="" method="post" class="flex justify-end gap-2">
            <?= csrf_field() ?>

            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button type="submit"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Ya, Hapus
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Script -->
<script>
    window.resultConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('tag-pemeriksaan/fetch') ?>",
        storeUrl: "<?= base_url('tag-pemeriksaan/store') ?>",
        checkNameUrl: "<?= base_url('tag-pemeriksaan/check_name_exists') ?>"
    };
</script>

<!-- External JS -->
<!-- <script src="<?= base_url('js/pages/tag_pemeriksaan.js') ?>"></script> -->
<?= $this->endSection() ?>
