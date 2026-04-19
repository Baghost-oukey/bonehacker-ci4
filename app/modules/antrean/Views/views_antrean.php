<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="antreanPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data pasien dan antrean secara efisien
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <?php
            $regionQuery = '';
            if (isset($regions_patient) && !empty($regions_patient)) {
                $val = is_array($regions_patient) ? implode(',', $regions_patient) : $regions_patient;
                $regionQuery = '?region=' . $val;
            }
            ?>
            <a href="<?= site_url('antrean/daftar-antrean') . $regionQuery ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-sync-alt text-slate-500"></i>
                Lihat Antrean
            </a>

            <button type="button" data-modal-open="exampleModal"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Pasien
            </button>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <!-- HEADER -->
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <!-- TITLE SECTION -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    Data Antrean
                </h3>
                <p class="text-sm text-slate-500">
                    Monitoring dan pengelolaan antrean pasien secara real-time
                </p>
            </div>

            <!-- FILTERS & ACTIONS ROW -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- LEFT: SEARCH & DATE RANGE -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <!-- SEARCH -->
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari pasien..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>

                    <!-- DATE RANGE -->
                    <div class="flex items-center gap-2 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-slate-400 text-base"></i>
                            <input type="date" id="startDate"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                        <span class="text-slate-300">-</span>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-check text-slate-400 text-base"></i>
                            <input type="date" id="endDate"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <!-- RIGHT: ACTION BUTTONS -->
                <div class="flex items-center gap-2">
                    <?php if (session()->get('role') === 'superadmin'): ?>
                        <button id="btnPdf"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 hover:border-red-300">
                            <i class="fas fa-file-pdf text-sm"></i>
                            <span class="hidden sm:inline">PDF</span>
                        </button>

                        <button id="btnExcel"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-300">
                            <i class="fas fa-file-excel text-sm"></i>
                            <span class="hidden sm:inline">Excel</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table id="table-1" class="w-full text-sm">

                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Usia</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">No. WA</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-right font-semibold">
                            <?= session()->get('role') === 'superadmin' ? 'Aksi' : 'Keterangan' ?>
                        </th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data antrean...
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
                    <div id="paginationNumbers" class="flex items-center gap-1">
                        <!-- pages will be generated here -->
                    </div>
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
            Data ditampilkan berdasarkan filter tanggal dan wilayah pengguna
        </div>

    </div>
</section>

<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Yakin ingin menghapus data ini?</p>

        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close class="px-4 py-2 text-sm border rounded-lg">Batal</button>
            <button id="confirmDelete" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                Hapus
            </button>
        </div>
    </div>
</div>

<div id="modalPatient" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-5xl p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Daftar Pasien</h3>
            <button type="button" data-modal-close class="text-slate-500 hover:text-black">✕</button>
        </div>

        <div class="overflow-x-auto">
            <table id="table-2" class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>

<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Data Pasien</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>
        <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data"
            class="space-y-4 p-5" novalidate="">
            <?= csrf_field() ?>
            <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-1">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="name" required autofocus>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jenis Kelamin</label>
                        <select
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            name="gender" required>
                            <option value="">-- Pilih --</option>
                            <option value="Man">Laki-laki</option>
                            <option value="Woman">Perempuan</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-slate-700">Pasien Rentan</div>
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_suspective" class="custom-switch-input"
                                id="isSuspectiveCheckbox">
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">YA</span>
                        </label>
                    </div>
                </div>

                <div id="keterangan_rentan" class="hidden space-y-1">
                    <label class="text-sm font-medium text-slate-700">Keterangan Rentan</label>
                    <textarea
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="ket_rentan" rows="3"></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Domestik</label>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="dom1" name="domestic" value="dalam_negeri"
                                class="custom-control-input" checked>
                            <label class="custom-control-label" for="dom1">Dalam Negeri</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="dom2" name="domestic" value="luar_negeri"
                                class="custom-control-input">
                            <label class="custom-control-label" for="dom2">Luar Negeri</label>
                        </div>
                    </div>
                </div>

                <div class="hidden space-y-1" id="country-group">
                    <label class="text-sm font-medium text-slate-700">Negara</label>
                    <select
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="country_id">
                        <option value="">PILIH</option>
                        <?php foreach ($negara as $v): ?>
                            <option value="<?= $v->id ?>"><?= $v->country ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1" id="desa-group">
                    <label class="text-sm font-medium text-slate-700">Pencarian Desa</label>
                    <select class="select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="desa_id"
                        id="desa_id" style="width: 100%;">
                        <option value="">Temukan Desa</option>
                    </select>
                </div>

                <div class="space-y-1" id="region-group">
                    <label class="text-sm font-medium text-slate-700">Wilayah</label>
                    <?php
                    $role = session()->get('role');
                    $sess_region_name = session()->get('region_name');
                    $sess_region_id = session()->get('region_patient');
                    $active_region = session()->get('active_region');
                    ?>

                    <?php if ($role === 'user'): ?>
                        <input type="text"
                            class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600"
                            value="<?= $sess_region_name ?>" readonly>
                        <input type="hidden" name="region_id"
                            value="<?= is_array($sess_region_id) ? $sess_region_id[0] : $sess_region_id ?>">
                    <?php else: ?>
                        <select
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            name="region_id" id="region_id">
                            <option value="">PILIH</option>
                            <?php foreach ($wilayah as $v): ?>
                                <?php
                                $selected = '';
                                if (!empty($active_region) && $active_region !== 'all') {
                                    $selected = $v->id == $active_region ? 'selected' : '';
                                }
                                ?>
                                <option value="<?= $v->id ?>" <?= $selected ?>><?= $v->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Umur</label>
                        <input type="number"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            name="age">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">No. WhatsApp</label>
                        <input type="number" id="phone"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            name="phone">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Alamat Jalan</label>
                    <textarea
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="address" rows="3"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Tanggal Kedatangan</label>
                    <input type="datetime-local"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="visit_date" required>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="submitBtn"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan
                    Pasien</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>

<script>
    window.antreanConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('antrean/fetchDataTable') ?>",
        deleteBaseUrl: "<?= site_url('antrean/destroy') ?>",
        pdfUrl: "<?= site_url('antrean/print_pdf_antrean') ?>",
        excelUrl: "<?= site_url('antrean/export_excell_antrean') ?>"
    };
</script>

<?= $this->endSection() ?>