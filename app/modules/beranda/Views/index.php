<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>


<?php $summary_cards = [
    [
        'label' => 'Antrean Hari Ini',
        'value' => number_format($queue_today ?? 0),
        'icon' => 'fa-user-clock',
        'desc' => 'Total antrean pasien hari ini',
    ],
    [
        'label' => 'Cabang Aktif',
        'value' => esc($active_region_name ?? 'Semua Wilayah'),
        'icon' => 'fa-map-marked-alt',
        'desc' => 'Sumber data dashboard saat ini',
        'isText' => true,
    ],
    ['label' => 'Transaksi Hari Ini', 'value' => 'Rp ' . number_format($transaction_today_total ?? 0, 0, ',', '.'), 'icon' => 'fa-wallet', 'desc' => 'Akumulasi nominal transaksi hari ini'],
    ['label' => 'Kunjungan Hari Ini', 'value' => number_format($kunjungan_today ?? 0), 'icon' => 'fa-stethoscope', 'desc' => 'Jumlah kunjungan pasien hari ini'],
]; ?>

<section class="w-full space-y-6 p-4 md:p-6">
    <!-- CARDS -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($summary_cards as $card): ?>
            <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm min-h-35">

                <!-- TOP -->
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">
                        <?= $card['label'] ?>
                    </p>

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50">
                        <i class="fas <?= $card['icon'] ?> text-teal-600 text-sm"></i>
                    </div>
                </div>

                <!-- VALUE -->
                <div class="mt-4">
                    <h3
                        class="<?= isset($card['isText']) ? 'text-lg' : 'text-3xl' ?> font-semibold tracking-tight text-slate-900 truncate">
                        <?= $card['value'] ?>
                    </h3>
                </div>

                <!-- DESC -->
                <p class="mt-4 text-xs text-slate-500">
                    <?= $card['desc'] ?>
                </p>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- CHART -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-7">

        <div class="col-span-4 rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Statistik Harian</h3>
            </div>
            <?= $this->include('App\Views\components\charts\daily-counter-charts') ?>
        </div>

        <div class="col-span-3 rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Ringkasan Pasien</h3>
            </div>
            <?= $this->include('App\Views\components\charts\patient-summary-charts') ?>
        </div>

    </div>

</section>

<?= $this->endSection() ?>