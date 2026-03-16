<?= $this->extend('layout/layout') ?> 
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= esc($title) ?></h1>
    </div>

    <div class="section-body">
        <form action="<?= site_url('patient/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?> <input type="hidden" name="id" value="<?= $patient_id ?>">

            <div class="row">
                <div class="col-md-12">
                    <?= $this->include('App\modules\patients\Views\component\card_biodata') ?>
                </div>
                <div class="col-md-12">
                    <?= $this->include('App\modules\patients\Views\component\card_file') ?>
                </div>
                <div class="col-md-12">
                    <?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>
                </div>
            </div>

        </form>

    </div>
</section>


<?= $this->endSection() ?>