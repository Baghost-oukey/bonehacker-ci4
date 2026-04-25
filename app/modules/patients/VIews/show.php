<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section class="w-full space-y-6 p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-sidebar-foreground">
                <?= $title ?>
            </h1>
            <p class="text-sm text-sidebar-foreground/60 mt-1">
                Edit data dan informasi pasien
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= site_url('dashboard') ?>" 
               class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border px-4 py-2.5 text-sm font-medium text-sidebar-foreground transition hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                <i class="fas fa-arrow-left text-sm"></i>
                Kembali
            </a>
            <button type="submit" form="patientForm" 
                    class="inline-flex items-center gap-2 rounded-lg bg-sidebar-foreground px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sidebar-foreground/90 shadow-sm">
                <i class="fas fa-save"></i>
                Simpan Perubahan
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="patientForm" action="<?= site_url('patient/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $patient_id ?>">

        <div class="space-y-6">
            <!-- Card Biodata -->
            <?= $this->include('App\modules\patients\Views\component\card_biodata') ?>
            
            <!-- Card Riwayat -->
            <?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>
        </div>
    </form>

    <!-- Card File (outside form) -->
    <?= $this->include('App\modules\patients\Views\component\card_file') ?>
</section>

<?= $this->endSection() ?>