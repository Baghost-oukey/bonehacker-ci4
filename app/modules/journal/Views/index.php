<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="journalPage" class="w-full space-y-6 py-4 md:py-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Pantau dan kelola data jurnal pemeriksaan secara efisien
            </p>
        </div>

        <div class="w-full md:w-auto">
            <?php if (session()->get('role') === 'superadmin'): ?>
            <button type="button" id="btnOpenExport"
                class="inline-flex w-full md:w-auto items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-file-export text-slate-500"></i>
                <span>Export Data</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden mx-auto">
        <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 md:px-6">
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
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-1 gap-3 sm:flex sm:items-center sm:gap-3">
                    <div class="w-full sm:w-72">
                        <input type="text" id="customSearch" placeholder="Ketik nama pasien..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-2">
                        <input type="date" id="start_date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15" value="<?= date('Y-m-d') ?>">
                        <input type="date" id="end_date" class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-3 flex-1">
                        <div class="w-full sm:w-56">
                            <?php if (session()->get('role') === 'user'): ?>
                                <input type="hidden" id="region" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                            <?php else: ?>
                                <select id="region" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15 bg-white">
                                    <option value="">Semua Wilayah</option>
                                    <?php foreach ($wilayah as $value): ?>
                                        <?php $selected = (session()->get('active_region') == $value->id) ? 'selected' : ''; ?>
                                        <option value="<?= $value->id ?>" <?= $selected ?>><?= esc($value->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <button id="btn-reset"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 hover:border-red-300">
                            <i class="fas fa-undo text-sm"></i>
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Card Container (KODE KITA) -->
        <div id="mobile-journal-container" class="md:hidden divide-y divide-slate-100 bg-white">
            <div class="p-12 text-center text-slate-400 italic text-sm">
                <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                Memuat data jurnal...
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-journal" class="w-full text-sm hidden md:table">
                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">No</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Pasien</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Rekam Medis</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Hasil Pemeriksaan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tindakan</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400 italic text-sm">
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
                        <select id="journalPaginationLength"
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
                        <span id="journalPaginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                    </div>
                </div>

                <!-- RIGHT: PAGINATION BUTTONS -->
                <div class="flex items-center justify-center gap-1.5 sm:justify-end">
                    <button id="journalPaginationPrev"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-300">
                        <i class="fas fa-chevron-left text-xs mr-1"></i>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>
                    <div id="journalPaginationNumbers" class="flex items-center gap-1"></div>
                    <button id="journalPaginationNext"
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
                <select id="period_picker" name="period"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="today">Hari Ini</option>
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

<!-- MODAL LIST REKAM MEDIS HARI INI -->
<div id="modalListRiwayatHariIni" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 transition-opacity">
    <div class="w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <!-- HEADER -->
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-white">
            <div>
                <h5 class="text-xl font-bold text-slate-800">Riwayat Kunjungan Hari Ini</h5>
                <p class="text-sm text-slate-500">Daftar rekam medis pasien untuk tanggal hari ini</p>
            </div>
            <button type="button" data-modal-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- PATIENT INFO STRIP -->
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Nama Pasien</label>
                    <p id="modal-list-name" class="text-sm font-bold text-slate-700">-</p>
                </div>
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Kontak</label>
                    <p id="modal-list-phone" class="text-sm font-medium text-slate-600">-</p>
                </div>
                <div>
                    <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Alamat</label>
                    <p id="modal-list-address" class="text-sm text-slate-600 truncate">-</p>
                </div>
            </div>
        </div>

        <!-- TABLE BODY -->
        <div class="flex-1 overflow-y-auto p-6 bg-white">
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <table id="table-list-hari-ini" class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Keluhan</th>
                            <th class="px-4 py-3 text-left">Rekam Medis</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-center">Durasi</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Data injected via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 flex justify-end">
            <button type="button" data-modal-close class="rounded-lg border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL REKAM MEDIS DETAIL -->
<?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>

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

    // Konfigurasi untuk modal rekam medis (digunakan oleh card_riwayat.php)
    window.patientConfig = {
        patientId: null,
        queueId: null,
        patientRegionId: null,
        csrfTokenName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        urls: {
            historyFetchBase: '<?= site_url("history/fetch") ?>',
            historyFetch: '<?= site_url("history/fetch") ?>/',
            historyStore: '<?= site_url("history/store") ?>',
            historyDestroy: '<?= site_url("history/destroy") ?>',
            complaintTags: '<?= site_url("tag-keluhan/get_tags") ?>',
            medisTags: '<?= site_url("tag-rekam-medis/tags") ?>',
            resultTags: '<?= site_url("tag-pemeriksaan/get_tags") ?>',
            terapisByRegion: '<?= site_url("history/terapis-by-region") ?>'
        }
    };

    // Store current patient info for detail modal
    let currentPatientInfo = {};

    /**
     * Langsung buka rekam medis spesifik dari tabel jurnal (berdasarkan historyId)
     */
    function openJournalMedicalRecord(btn) {
        const raw = btn.getAttribute('data-medis');
        if (!raw) return;

        let info;
        try {
            info = JSON.parse(raw);
        } catch (e) {
            return;
        }

        const { patientId, historyId, patientName, patientPhone, patientAddress, patientAge } = info;
        currentPatientInfo = info;

        if (!historyId || !patientId) return;

        // Set patientConfig agar modal rekam medis tahu konteksnya
        window.patientConfig.patientId = patientId;
        window.patientConfig.queueId   = null;

        // Isi header modal
        const nameEl    = document.getElementById('modal-patient-name');
        const ageEl     = document.getElementById('modal-patient-age');
        const addressEl = document.getElementById('modal-patient-address');
        const phoneEl   = document.getElementById('modal-patient-phone');
        if (nameEl)    nameEl.textContent    = patientName    || '-';
        if (ageEl)     ageEl.textContent     = patientAge     || '-';
        if (addressEl) addressEl.textContent = patientAddress || '-';
        if (phoneEl)   phoneEl.textContent   = patientPhone   || '-';

        // Langsung buka detail history yang spesifik
        if (window.PatientHistoryPage) {
            window.PatientHistoryPage.config.patientId = patientId;
            window.PatientHistoryPage.show(historyId);
        }
    }

    function fetchTodayHistory(patientId, date) {
        const tbody = document.querySelector('#table-list-hari-ini tbody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-slate-400 italic"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>';

        $.ajax({
            url: window.patientConfig.urls.historyFetchBase + '/' + patientId,
            type: "POST",
            dataType: "json",
            data: {
                [window.patientConfig.csrfTokenName]: window.patientConfig.csrfHash,
                draw: 1,
                start: 0,
                length: 100,
                filter_date: date
            },
            success: function(response) {
                const freshToken = response.new_token || response.csrf_hash;
                if (freshToken) {
                    window.patientConfig.csrfHash = freshToken;
                    window.journalConfig.csrfHash = freshToken;
                }

                tbody.innerHTML = '';
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-slate-400 italic">Tidak ada data rekam medis hari ini</td></tr>';
                    return;
                }

                response.data.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition border-b border-slate-100';
                    tr.innerHTML = `
                        <td class="px-4 py-3 text-center text-xs">${index + 1}</td>
                        <td class="px-4 py-3 text-xs">${row.complaint || '-'}</td>
                        <td class="px-4 py-3 text-xs">${row.medhis || '-'}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">${row.date || '-'}</td>
                        <td class="px-4 py-3 text-center text-xs">${row.duration || '-'}</td>
                        <td class="px-4 py-3 text-center text-xs">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium ${row.type === 'draft' ? 'bg-amber-50 text-amber-700' : 'bg-teal-50 text-teal-700'} uppercase">${row.type || "-"}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick="viewDetailHistory('${row.id}', '${patientId}')" class="text-teal-600 hover:bg-teal-50 p-1.5 rounded transition" title="Lihat Detail"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            },
            error: () => {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-red-400 italic">Gagal memuat data</td></tr>';
            }
        });
    }

    function viewDetailHistory(historyId, patientId) {
        if (window.PatientHistoryPage && historyId) {
            // Tutup modal list dulu
            closeModal(document.getElementById('modalListRiwayatHariIni'));

            // Populate header detail modal (card_riwayat.php elements)
            const nameEl = document.getElementById('modal-patient-name');
            const ageEl = document.getElementById('modal-patient-age');
            const addressEl = document.getElementById('modal-patient-address');
            const phoneEl = document.getElementById('modal-patient-phone');

            if (nameEl) nameEl.textContent = currentPatientInfo.patientName || '-';
            if (ageEl) ageEl.textContent = currentPatientInfo.patientAge || '-';
            if (addressEl) addressEl.textContent = currentPatientInfo.patientAddress || '-';
            if (phoneEl) phoneEl.textContent = currentPatientInfo.patientPhone || '-';

            window.PatientHistoryPage.config.patientId = patientId;
            window.PatientHistoryPage.show(historyId, false, true);
        }
    }
</script>

<?= $this->endSection() ?>