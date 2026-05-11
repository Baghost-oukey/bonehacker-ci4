<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="patientHistoryPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">Daftar lengkap riwayat kunjungan dan rekam medis pasien</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= site_url('journal') ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <i class="fas fa-arrow-left text-teal-600"></i> Kembali ke Jurnal
            </a>
        </div>
    </div>

    <!-- PATIENT SUMMARY CARD -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Pasien</label>
                <p class="text-lg font-bold text-slate-800"><?= esc($patient->name) ?></p>
                <p class="text-sm text-slate-500"><?= ($patient->gender == 'Man') ? 'Laki-laki' : 'Perempuan' ?>, <?= esc($patient->age) ?> Tahun</p>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kontak & Alamat</label>
                <p class="text-sm font-medium text-slate-700"><i class="fab fa-whatsapp text-emerald-500 mr-1.5"></i> <?= esc($patient->phone ?: '-') ?></p>
                <p class="text-xs text-slate-500 leading-relaxed truncate-2" title="<?= esc($patient->address) ?>">
                    <?= esc($patient->address ?: '-') ?>
                    <?php if(!empty($address->desa_nama)): ?>
                        <br><?= esc($address->desa_nama) ?>, <?= esc($address->kecamatan_nama) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Pendaftaran</label>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                        Terdaftar: <?= date('d M Y', strtotime($patient->created_at)) ?>
                    </span>
                    <?php if(isset($patient->is_suspective) && $patient->is_suspective): ?>
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                            Rentan
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT: RIWAYAT KUNJUNGAN -->
    <div class="space-y-6">
        <!-- Tabel Riwayat (Include Component) -->
        <?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.patientConfig = {
        patientId: '<?= esc($patient_id ?? "") ?>',
        queueId: '',
        patientRegionId: '<?= esc($patient->region_id ?? "") ?>',
        csrfTokenName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        urls: {
            historyFetchBase: '<?= site_url("history/fetch") ?>',
            historyFetch: '<?= site_url("history/fetch/" . ($patient_id ?? 0)) ?>',
            historyStore: '<?= site_url("history/store") ?>',
            historyDestroy: '<?= site_url("history/destroy") ?>',
            complaintTags: '<?= site_url("tag-keluhan/get_tags") ?>',
            medisTags: '<?= site_url("tag-rekam-medis/tags") ?>',
            resultTags: '<?= site_url("tag-pemeriksaan/get_tags") ?>',
            terapisByRegion: '<?= site_url("history/terapis-by-region") ?>'
        }
    };

    // Auto load data saat halaman siap
    document.addEventListener("DOMContentLoaded", function() {
        if (window.PatientHistoryPage && typeof window.PatientHistoryPage.loadPatient === 'function') {
            // Langsung tampilkan container history (karena ini halaman dedicated)
            const container = document.getElementById('patientHistoryContainer');
            if (container) container.classList.remove('hidden');
            
            // Load data
            window.PatientHistoryPage.loadPatient('<?= $patient_id ?>', null, null);
        }
    });
</script>
<?= $this->endSection() ?>
