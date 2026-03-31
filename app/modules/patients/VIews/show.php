<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
        <div class="section-header-breadcrumb">
            <a href="<?= site_url('dashboard') ?>" class="btn btn-primary">Kembali</a>
        </div>
    </div>

    <div class="section-body">
        <form action="<?= site_url('patient/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $patient_id ?>">

            <div class="row">
                <div class="col-md-12">
                    <?= $this->include('App\modules\patients\Views\component\card_biodata') ?>
                </div>
                <div class="col-md-12">
                    <?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>
                </div>
            </div>

        </form>

    </div>
</section>

<?= $this->include('App\modules\patients\Views\component\card_file') ?>


<?= $this->endSection() ?>