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
            <table id="table-queue" class="w-full text-sm">

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

<!-- Modal Tambah Patient -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
            <div>
                <h5 class="text-xl font-bold text-slate-800 tracking-tight">Pilih Pasien</h5>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Cari Data Pasein yang akan dilakukan terapi, data diambil dari riwayat kunjungan pasien jika belum pernah melakukan terapis, daftar kan dulu pasien..</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" data-modal-open="exampleModal"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 active:scale-95 transition-all">
                    <i class="fas fa-user-plus text-xs"></i>
                    Tambah Pasien
                </button>
                <button type="button" data-modal-close class="rounded-xl p-2 text-slate-400 hover:bg-white hover:text-red-500 transition-all">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="px-8 py-4 border-b border-slate-50 bg-white">
            <div class="relative w-full max-w-md">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="searchPatientList" placeholder="Cari ID, Nama, atau No. WA..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 pl-11 pr-4 py-3 text-sm outline-none transition-all focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
            </div>
        </div>

        <div class="p-0 overflow-hidden">
            <div class="max-h-[60vh] overflow-y-auto custom-scrollbar">
                <table id="table-2" class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50/90 backdrop-blur-md text-[10px] uppercase font-bold tracking-widest text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-4 text-left">ID Pasien</th>
                            <th class="px-8 py-4 text-left">Informasi Pasien</th>
                            <th class="px-8 py-4 text-left">Alamat Terdaftar</th>
                            <th class="px-8 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="patientListBody">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/30 px-8 py-4 text-center">
            <p class="text-[11px] text-slate-400 font-medium italic">Menampilkan data rekam medis pasien yang aktif di sistem</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>

<script>
    window.antreanConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('antrean/fetchDataTable') ?>",
        fetchPatientUrl: "<?= site_url('antrean/fetchPatientDataTables') ?>",
        deleteBaseUrl: "<?= site_url('antrean/destroy') ?>",
        pdfUrl: "<?= site_url('antrean/print_pdf_antrean') ?>",
        excelUrl: "<?= site_url('antrean/export_excell_antrean') ?>"
    };
</script>

<?= $this->endSection() ?>