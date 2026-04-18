<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section class="max-w-7xl space-y-6 p-4 md:p-6">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">Dashboard</p>
            <h1 class="text-3xl font-bold text-slate-900"><?= esc($title) ?></h1>
        </div>
        <div class="rounded-2xl bg-slate-100 px-4 py-2 text-slate-700">
            Halo <?= esc($realname) ?> !!!
        </div>
    </div>

    <?php if (!empty($greeting)): ?>
        <div
            class="mb-6 overflow-hidden rounded-2xl bg-linear-to-r from-green-50 to-cyan-50 px-4 py-3 text-center text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
            <marquee behavior="scroll" direction="left" scrollamount="5">
                <?= esc($greeting) ?>
            </marquee>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl bg-slate-50 p-4 md:p-6">
        <div class="mb-4 text-center">
            <h2 class="text-2xl font-semibold text-blue-700">Daily Counter</h2>
        </div>

        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
            <table class="min-w-full border-separate border-spacing-0 bg-white text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="border-b border-slate-200 px-4 py-3 text-center font-bold whitespace-nowrap">Minggu
                        </th>
                        <th class="border-b border-slate-200 px-4 py-3 text-center whitespace-nowrap">Senin</th>
                        <th class="border-b border-slate-200 px-4 py-3 text-center whitespace-nowrap">Selasa</th>
                        <th class="border-b border-slate-200 px-4 py-3 text-center whitespace-nowrap">Rabu</th>
                        <th class="border-b border-slate-200 px-4 py-3 text-center whitespace-nowrap">Kamis</th>
                        <th class="border-b border-slate-200 px-4 py-3 text-center whitespace-nowrap">Jumat</th>
                        <th class="border-b border-slate-200 px-4 py-3 text-center whitespace-nowrap">Sabtu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($daily_counts)): ?>
                        <?php foreach ($daily_counts as $week): ?>
                            <tr class="border-t border-slate-200">
                                <?php foreach ($week as $day): ?>
                                    <td class="min-w-30 align-top whitespace-nowrap px-4 py-5">
                                        <span class="mb-2 block text-xs text-slate-500"><?= $day['formatted_date'] ?></span>
                                        <div class="text-center">
                                            <span class="text-lg font-bold text-blue-600"><?= $day['daily_count'] ?></span>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
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
            <div class="rounded-2xl border-l-[7px] border-l-[#6777EF] bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-3">
                    <i class="fas fa-genderless text-slate-500"></i>
                    <h5 class="text-lg font-semibold text-slate-900"><?= $row['label'] ?></h5>
                </div>
                <div class="space-y-3 text-sm text-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-injured text-slate-500"></i>
                        <span>Jumlah Pasien:</span>
                        <span class="font-semibold text-slate-900"><?= number_format($row['p']) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-stethoscope text-slate-500"></i>
                        <span>Jumlah Kunjungan:</span>
                        <span class="font-semibold text-slate-900"><?= number_format($row['k']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?= $this->endSection() ?>