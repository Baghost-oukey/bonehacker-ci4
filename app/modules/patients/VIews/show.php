<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="patientShowPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">Informasi lengkap dan riwayat rekam medis pasien</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= site_url('journal') ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <i class="fas fa-arrow-left text-teal-600"></i> Kembali ke Jurnal
            </a>
        </div>
    </div>

    <!-- CONTENT - Semua dalam satu halaman -->
    <div class="space-y-6">
        <!-- BIODATA -->
        <?= $this->include('App\modules\patients\Views\component\card_biodata') ?>

        <!-- FILE -->
        <?= $this->include('App\modules\patients\Views\component\card_file') ?>

        <!-- RIWAYAT PERUBAHAN DATA -->
        <?= $this->include('App\modules\patients\Views\component\card_history_changes') ?>

        <!-- RIWAYAT -->
        <?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.patientConfig = {
        patientId: '<?= esc($patient_id ?? "") ?>',
        queueId: '<?= esc($queue_id ?? "") ?>',
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
            fileBase: '<?= base_url("patient_file") ?>',
            fileUpload: '<?= site_url("patient/update_files") ?>',
            terapisByRegion: '<?= site_url("history/terapis-by-region") ?>'
        },
        fileUrlsData: <?= !empty($file_urls) ? (is_array($file_urls) ? json_encode($file_urls) : $file_urls) : '[]' ?>
    };

    // Auto load data saat halaman siap
    document.addEventListener("DOMContentLoaded", function() {
        if (window.PatientHistoryPage && typeof window.PatientHistoryPage.loadPatient === 'function') {
            // Tampilkan container history (karena ini halaman detail)
            const container = document.getElementById('patientHistoryContainer');
            if (container) container.classList.remove('hidden');
            
            // Load data history
            window.PatientHistoryPage.loadPatient('<?= $patient_id ?>', null, null);
        }
    });
</script>

<?= $this->endSection() ?>