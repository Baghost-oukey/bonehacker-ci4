<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="journalPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Pantau dan kelola data jurnal pemeriksaan secara efisien
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <?php if (session()->get('role') === 'superadmin'): ?>
            <button type="button" id="btnOpenExport"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-file-export text-slate-500"></i>
                Export Data
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden mx-auto">
        <!-- HEADER -->
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <!-- TITLE SECTION -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    Data Jurnal Pemeriksaan
                </h3>
                <p class="text-sm text-slate-500">
                    Monitoring dan pengelolaan data jurnal pemeriksaan pasien
                </p>
            </div>

            <!-- FILTERS & ACTIONS ROW -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- LEFT: SEARCH & DATE RANGE -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <!-- SEARCH -->
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="customSearch" placeholder="Ketik nama pasien..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>

                    <!-- DATE RANGE -->
                    <!-- <div class="flex items-center gap-2 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-slate-400 text-base"></i>
                            <input type="date" id="start_date"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                        <span class="text-slate-300">-</span>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-check text-slate-400 text-base"></i>
                            <input type="date" id="end_date"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div> -->
                    <input type="date" id="start_date" class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15" value="<?= date('Y-m-d') ?>">

                    <input type="date" id="end_date" class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- RIGHT: ACTION BUTTONS -->
                <div class="flex items-center gap-2">
                    <!-- REGION FILTER -->
                    <?php if (session()->get('role') === 'user'): ?>
                        <input type="hidden" id="region" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                    <?php else: ?>
                        <select id="region" class="w-full sm:w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                            <option value="">Semua Wilayah</option>
                            <?php foreach ($wilayah as $value): ?>
                                <?php $selected = (session()->get('active_region') == $value->id) ? 'selected' : ''; ?>
                                <option value="<?= $value->id ?>" <?= $selected ?>><?= esc($value->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <button id="btn-reset"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 hover:border-red-300">
                        <i class="fas fa-undo text-sm"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </button>

                    <!-- <button id="btn-search"
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        <i class="fas fa-search text-white"></i>
                        Cari
                    </button> -->
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table id="table-journal" class="w-full text-sm">
                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">No</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Pasien</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Hasil Pemeriksaan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tindakan</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data jurnal pemeriksaan...
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

<!-- Modal Export -->
<div id="modalExportJournal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Export Data Jurnal</h5>
            <button type="button" class="btn-close-modal rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('journal/export_file_journal') ?>" method="GET" target="_blank" class="space-y-4 p-5">
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Periode Laporan</label>
                <select id="period_picker"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="all">Seluruh Data</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="last_year">Tahun Lalu</option>
                    <option value="custom">Pilih Tanggal Sendiri</option>
                </select>
            </div>

            <div id="custom_date_container" class="grid grid-cols-2 gap-4" style="display: none;">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Mulai</label>
                    <input type="date" name="start_date" id="exp_start_date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Selesai</label>
                    <input type="date" name="end_date" id="exp_end_date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Target Wilayah</label>
                <?php if (session()->get('role') === 'user'): ?>
                    <input type="text"
                        class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600"
                        value="<?= session()->get('region_name') ?>" readonly>
                    <input type="hidden" name="region_id"
                        value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                <?php else: ?>
                    <select name="region_id" id="export_region"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">Semua Wilayah</option>
                        <?php foreach ($wilayah as $r): ?>
                            <option value="<?= $r->id ?>"><?= esc($r->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Format Dokumen</label>
                <select name="format_type"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="excel">Microsoft Excel (.xlsx)</option>
                    <option value="pdf">PDF Document (.pdf)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" class="btn-close-modal rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Unduh Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Script -->
<script>
    window.journalConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('journal/fetch') ?>",
        exportUrl: "<?= site_url('journal/export_file_journal') ?>"
    };
</script>

<?= $this->endSection() ?>