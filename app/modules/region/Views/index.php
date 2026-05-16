<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="regionPage" class="w-full space-y-6 py-4 md:py-6">
    <?php
    $sess_role = session()->get('role');
    $sess_active_id = session()->get('active_region');
    $sess_active_name = session()->get('active_region_name');
    $sess_region_name = session()->get('region_name');

    $region_label = ($sess_role === 'user' && !empty(session()->get('terapis_id')))
        ? $sess_region_name
        : (($sess_active_id === 'all' || !$sess_active_id) ? 'Semua Wilayah' : $sess_active_name);
    ?>

    <input type="hidden" id="activeRegion" value="<?= esc($sess_active_id ?? 'all') ?>">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data cabang untuk mengatur wilayah operasional dan distribusi pasien dengan lebih efektif.
            </p>
        </div>

        <?php if ($sess_role === 'superadmin'): ?>
        <div class="w-full md:w-auto">
            <button type="button" data-modal-open="modalTambahRegion"
                class="inline-flex w-full md:w-auto items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Cabang
            </button>
        </div>
        <?php endif; ?>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 md:px-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Daftar Cabang</h3>
                <p class="text-sm text-slate-500">Wilayah aktif: <?= esc($region_label) ?></p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1 sm:flex-none sm:w-80">
                    <input type="text" id="searchInput" placeholder="Cari cabang..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                </div>
            </div>
        </div>

        <!-- Table -->
        <!-- Mobile Card Container (KODE KITA) -->
        <div id="mobile-region-container" class="md:hidden divide-y divide-slate-100 bg-white">
            <div class="p-12 text-center text-slate-400 italic text-sm">
                <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                Memuat data cabang...
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-region" class="w-full text-sm hidden md:table">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Cabang</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Waktu Buat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Terakhir Diperbarui</th>
                        <?php if (session()->get('role') === 'superadmin'): ?>
                            <th class="px-6 py-3.5 text-right font-semibold">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="<?= session()->get('role') === 'superadmin' ? 5 : 4 ?>" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data cabang...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
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

                    <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                        <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                    </div>
                </div>

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

        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            Data ditampilkan berdasarkan filter wilayah pengguna
        </div>
    </div>
</section>

<!-- Modal Tambah Region -->
<div id="modalTambahRegion" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Data Cabang</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('region/store'); ?>" method="post" id="formTambahRegion"
            class="space-y-4 p-5 needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama Cabang</label>
                <input type="text"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    name="name" required autofocus>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama Cabang tidak boleh kosong</div>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Alamat</label>
                <textarea
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    name="address" rows="2"></textarea>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nomor HP</label>
                <input type="text"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    name="phone">
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnSimpanRegion"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Region -->
<div id="modalEditRegion" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Ubah Data Cabang</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formEditRegion" action="" method="post" class="space-y-4 p-5 needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama Cabang</label>
                <input type="text"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    id="editName" name="name" required>
                <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama Cabang tidak boleh kosong</div>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Alamat</label>
                <textarea
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    id="editAddress" name="address" rows="2"></textarea>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nomor HP</label>
                <input type="text"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                    id="editPhone" name="phone">
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnUpdateRegion"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Hapus Region -->
<div id="modalDeleteRegion" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Peringatan!</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formDeleteRegion" action="" method="post" class="space-y-4 p-5">
            <?= csrf_field() ?>
            <div class="space-y-1">
                <p class="text-sm text-slate-600">Apakah Anda yakin ingin menghapus data Cabang ini? Tindakan ini tidak
                    dapat dibatalkan.</p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnConfirmDelete"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Ya,
                    Hapus</button>
            </div>
        </form>
    </div>
</div>


<!-- TEMPLATE LOADING DATA -->
<div id="datatable-loader" class="hidden">
    <div class="fixed inset-0 z-10001 flex flex-col items-center justify-center p-4 pointer-events-none">
        <div class="flex flex-col items-center bg-white p-5 rounded-2xl shadow-xl border border-slate-100 animate-fade-up">
            <div class="relative flex h-10 w-10 items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
            </div>
            <span class="mt-3 text-[10px] font-black text-slate-500 tracking-widest uppercase">
                Memuat Data...
            </span>
        </div>

    </div>
</div>
<!-- END -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    window.regionConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('region/fetch') ?>",
        storeUrl: "<?= site_url('region/store') ?>",
        baseUrl: "<?= site_url('region') ?>",
        role: "<?= esc($sess_role) ?>"
    };
</script>

<?= $this->endSection() ?>