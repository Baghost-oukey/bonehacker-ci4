<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="patientShowPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">Edit data dan informasi pasien</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= site_url('beranda') ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- TAB NAV -->
    <div class="border-b border-slate-200">
        <nav class="flex gap-6" id="patient-tabs">
            <button data-tab="biodata"
                class="tab-btn border-b-2 border-teal-600 text-teal-600 pb-3 text-sm font-medium transition">
                Biodata
            </button>
            <button data-tab="riwayat"
                class="tab-btn border-b-2 border-transparent text-slate-500 hover:text-slate-700 pb-3 text-sm font-medium transition">
                Riwayat
            </button>
            <button data-tab="file"
                class="tab-btn border-b-2 border-transparent text-slate-500 hover:text-slate-700 pb-3 text-sm font-medium transition">
                File
            </button>
        </nav>
    </div>

    <!-- TAB CONTENT -->
    <div class="mt-6">
        <!-- BIODATA -->
        <div id="tab-biodata" class="tab-content">
            <?= $this->include('App\modules\patients\Views\component\card_biodata') ?>
        </div>

        <!-- RIWAYAT -->
        <div id="tab-riwayat" class="tab-content hidden">
            <?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>
        </div>

        <!-- FILE -->
        <div id="tab-file" class="tab-content hidden">
            <?= $this->include('App\modules\patients\Views\component\card_file') ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.patientConfig = {
        patientId: '<?= esc($patient_id ?? "") ?>',
        queueId: '<?= esc($queue_id ?? "") ?>',
        csrfTokenName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        urls: {
            historyFetch: '<?= site_url("history/fetch/" . ($patient_id ?? 0)) ?>',
            historyStore: '<?= site_url("history/store") ?>',
            historyDestroy: '<?= site_url("history/destroy") ?>',
            complaintTags: '<?= site_url("tag-keluhan/get_tags") ?>',
            medisTags: '<?= site_url("tag-rekam-medis/tags") ?>',
            resultTags: '<?= site_url("tag-pemeriksaan/get_tags") ?>',
            fileBase: '<?= base_url("patient_file") ?>',
            fileUpload: '<?= site_url("patient/update_files") ?>'
        },
        fileUrlsData: <?= !empty($file_urls) ? (is_array($file_urls) ? json_encode($file_urls) : $file_urls) : '[]' ?>
    };
</script>

<?= $this->endSection() ?>