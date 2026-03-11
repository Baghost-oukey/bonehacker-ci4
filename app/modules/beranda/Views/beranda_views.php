<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }
    .table-container {
        margin: 20px auto;
        max-width: 100%;
        overflow-x: auto;
    }
    .text-red {
        color: red;
    }
    .bg-gray {
        background-color: #e0e0e0;
    }
    .table-bordered th,
    .table-bordered td {
        white-space: nowrap;
    }
    .calendar-cell {
        min-width: 120px;
        vertical-align: top !important;
    }
    .date-number {
        font-size: 0.8rem;
        color: #888;
        display: block;
        margin-bottom: 5px;
    }
    .count-number {
        font-size: 1.2rem;
        font-weight: 700;
        color: #6777EF;
    }
</style>

<section class="section">
    <div class="section-header">
        <h1><?= esc($title) ?></h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Halo <?= esc($realname) ?> !!!</h3>
                    </div>

                    <?php if (!empty($greeting)): ?>
                        <div class="d-flex justify-content-center mb-3">
                            <marquee behavior="scroll" direction="left" scrollamount="5">
                                <h3><?= esc($greeting) ?></h3>
                            </marquee>
                        </div>
                    <?php endif; ?>

                    <div class="table-container bg-white rounded shadow-md p-6">
                        <h1 class="text-2xl font-semibold text-blue-700 text-center mb-4">Daily Counter</h1>
                        <table class="table table-bordered table-responsive-md">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">Minggu</th>
                                    <th class="text-center">Senin</th>
                                    <th class="text-center">Selasa</th>
                                    <th class="text-center">Rabu</th>
                                    <th class="text-center">Kamis</th>
                                    <th class="text-center">Jumat</th>
                                    <th class="text-center">Sabtu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($daily_counts)): ?>
                                    <?php foreach ($daily_counts as $week): ?>
                                        <tr>
                                            <?php foreach ($week as $day): ?>
                                                <td class="calendar-cell">
                                                    <span class="date-number"><?= $day['formatted_date'] ?></span>
                                                    <div class="text-center">
                                                        <span class="count-number"><?= $day['daily_count'] ?></span>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-body shadow-sm mt-3">
                        <div class="row">
                            <?php 
                            $stats_data = [
                                ['label' => 'Kemarin', 'p' => $pasien_yesterday, 'k' => $kunjungan_yesterday],
                                ['label' => 'Hari Ini', 'p' => $pasien_today, 'k' => $kunjungan_today],
                                ['label' => 'Bulan Kemarin', 'p' => $pasien_lastmonth, 'k' => $kunjungan_lastmonth],
                                ['label' => 'Bulan Ini', 'p' => $pasien_thismonth, 'k' => $kunjungan_thismonth],
                                ['label' => 'Tahun Kemarin', 'p' => $pasien_lastyear, 'k' => $kunjungan_lastyear],
                                ['label' => 'Tahun Ini', 'p' => $pasien_thisyear, 'k' => $kunjungan_thisyear],
                                ['label' => 'Semua Tahun', 'p' => $pasien_all, 'k' => $kunjungan_all],
                            ];
                            ?>

                            <?php foreach ($stats_data as $row): ?>
                                <div class="col-md-6 col-lg-3 mb-4 d-flex align-items-stretch">
                                    <div class="card bg-white text-dark w-100 shadow-sm" style="border-left: 7px solid #6777EF;">
                                        <div class="card-header border-bottom border-primary">
                                            <h5 class="card-title">
                                                <i class="fas fa-genderless"></i> <?= $row['label'] ?>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-injured mr-2 mb-1"></i>
                                                <p class="card-text mb-1"> Jumlah Pasien: <p><?= number_format($row['p']) ?></p></p>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-stethoscope mr-2 mb-1"></i>
                                                <p class="card-text mb-1">Jumlah Kunjungan: <p><?= number_format($row['k']) ?></p></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>