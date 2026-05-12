<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section class="w-full space-y-6 p-4">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Menu Utama</h2>
        <p class="text-sm text-slate-500">Akses cepat fitur BoneHacker</p>
    </div>

    <?= $this->include('App\Views\components\mobile_grid_menu') ?>
</section>

<?= $this->endSection() ?>
