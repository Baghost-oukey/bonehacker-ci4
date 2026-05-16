<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<?php if ($role === 'user' && !empty(session()->get('terapis_id'))): ?>
    <!-- TERAPIS VIEW -->
    
    <!-- Mobile/Tablet: Grid Menu -->
    <div class="lg:hidden w-full p-4">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Beranda</h2>
            <p class="text-sm text-slate-500">Akses cepat menu BoneHacker</p>
        </div>
        <?= $this->include('App\Views\components\mobile_grid_menu') ?>
    </div>

    <!-- Desktop: Statistik Terapis -->
    <style>
        @media (max-width: 1023px) {
            .desktop-stats-terapis { display: none !important; }
        }
        @media (min-width: 1024px) {
            .desktop-stats-terapis { display: block !important; }
        }
    </style>
    <div class="desktop-stats-terapis w-full space-y-6 p-4 md:p-6">
        
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Dashboard Terapis</h2>
            <p class="text-sm text-slate-500">Periode: <?= esc($bulan_display ?? date('F Y')) ?></p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            
            <!-- Total Pasien -->
            <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm min-h-35">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Pasien Bulan Ini</p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                        <i class="fas fa-users text-blue-600 text-sm"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-semibold tracking-tight text-slate-900">
                        <?= number_format($statistik_pasien['total_pasien']) ?>
                    </h3>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    Pasien yang Anda tangani
                </p>
            </div>

            <!-- Hari Kerja -->
            <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm min-h-35">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Hari Kerja</p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50">
                        <i class="fas fa-calendar-check text-teal-600 text-sm"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-semibold tracking-tight text-slate-900">
                        <?= number_format($statistik_pasien['hari_kerja']) ?>
                    </h3>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    Hari dengan pasien
                </p>
            </div>

            <!-- Rata-rata Pasien -->
            <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm min-h-35">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Rata-rata Per Hari</p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50">
                        <i class="fas fa-chart-line text-purple-600 text-sm"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-semibold tracking-tight text-slate-900">
                        <?= number_format($statistik_pasien['rata_rata'], 1) ?>
                    </h3>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    Pasien per hari kerja
                </p>
            </div>

            <!-- Total Jaspel -->
            <div class="flex flex-col justify-between rounded-2xl bg-white p-5 shadow-sm min-h-35">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Jaspel</p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                        <i class="fas fa-money-bill-wave text-emerald-600 text-sm"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-xl font-semibold tracking-tight text-slate-900">
                        Rp <?= number_format($jaspel_harian['total_jaspel'], 0, ',', '.') ?>
                    </h3>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    Jasa pelayanan bulan ini
                </p>
            </div>

        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            
            <!-- Statistik Pasien Per Hari -->
            <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-700">Pasien Per Hari</h3>
                    <p class="text-xs text-slate-500 mt-1">Jumlah pasien yang Anda tangani setiap hari</p>
                </div>
                <div class="p-5 max-h-96 overflow-y-auto">
                    <?php if (!empty($statistik_pasien['per_hari'])): ?>
                        <div class="space-y-2">
                            <?php foreach ($statistik_pasien['per_hari'] as $tanggal => $jumlah): ?>
                                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                    <span class="text-sm text-slate-600">
                                        <?= date('d M Y', strtotime($tanggal)) ?>
                                    </span>
                                    <span class="text-sm font-semibold text-slate-900">
                                        <?= $jumlah ?> pasien
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-slate-500 text-center py-8">Belum ada data pasien bulan ini</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rekap Kehadiran -->
            <?php if ($rekap_kehadiran !== null): ?>
                <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="text-lg font-semibold text-slate-700">Rekap Kehadiran</h3>
                        <p class="text-xs text-slate-500 mt-1">Status kehadiran Anda bulan ini</p>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-emerald-50 rounded-lg p-3">
                                <p class="text-xs text-emerald-600 font-medium">Hadir</p>
                                <p class="text-2xl font-bold text-emerald-700"><?= $rekap_kehadiran['hadir'] ?></p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-3">
                                <p class="text-xs text-yellow-600 font-medium">Izin</p>
                                <p class="text-2xl font-bold text-yellow-700"><?= $rekap_kehadiran['izin'] ?></p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <p class="text-xs text-blue-600 font-medium">Sakit</p>
                                <p class="text-2xl font-bold text-blue-700"><?= $rekap_kehadiran['sakit'] ?></p>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-3">
                                <p class="text-xs text-orange-600 font-medium">Cuti</p>
                                <p class="text-2xl font-bold text-orange-700"><?= $rekap_kehadiran['cuti'] ?></p>
                            </div>
                        </div>
                        <?php if ($rekap_kehadiran['alpha'] > 0): ?>
                            <div class="bg-red-50 rounded-lg p-3">
                                <p class="text-xs text-red-600 font-medium">Alpha</p>
                                <p class="text-2xl font-bold text-red-700"><?= $rekap_kehadiran['alpha'] ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Jaspel Harian -->
        <?php if ($jaspel_harian['settings_exist']): ?>
            <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-700">Jaspel Harian</h3>
                    <p class="text-xs text-slate-500 mt-1">Rincian jasa pelayanan per hari kerja</p>
                </div>
                <div class="p-5">
                    <?php if (!empty($jaspel_harian['per_hari'])): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Tanggal</th>
                                        <th class="px-4 py-3 text-center">Pasien Reguler</th>
                                        <th class="px-4 py-3 text-center">Pasien Kejantanan</th>
                                        <th class="px-4 py-3 text-right">Jaspel Reguler</th>
                                        <th class="px-4 py-3 text-right">Jaspel Kejantanan</th>
                                        <th class="px-4 py-3 text-right font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($jaspel_harian['per_hari'] as $tanggal => $data): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 text-slate-700">
                                                <?= date('d M Y', strtotime($tanggal)) ?>
                                            </td>
                                            <td class="px-4 py-3 text-center text-slate-600">
                                                <?= $data['pasien_reguler'] ?>
                                            </td>
                                            <td class="px-4 py-3 text-center text-slate-600">
                                                <?= $data['pasien_kejantanan'] ?>
                                            </td>
                                            <td class="px-4 py-3 text-right text-teal-600">
                                                Rp <?= number_format($data['reguler'], 0, ',', '.') ?>
                                            </td>
                                            <td class="px-4 py-3 text-right text-blue-600">
                                                Rp <?= number_format($data['kejantanan'], 0, ',', '.') ?>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                                                Rp <?= number_format($data['total'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-slate-50 font-semibold">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right text-slate-700">Total Bulan Ini:</td>
                                        <td class="px-4 py-3 text-right text-teal-700">
                                            Rp <?= number_format($jaspel_harian['total_reguler'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-4 py-3 text-right text-blue-700">
                                            Rp <?= number_format($jaspel_harian['total_kejantanan'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-4 py-3 text-right text-emerald-700">
                                            Rp <?= number_format($jaspel_harian['total_jaspel'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-slate-500 text-center py-8">Belum ada data jaspel bulan ini</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-yellow-800">Pengaturan Jaspel Belum Tersedia</h4>
                        <p class="text-sm text-yellow-700 mt-1">Hubungi admin untuk mengatur nominal jaspel di cabang Anda.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

<?php else: ?>
    <!-- NON-TERAPIS VIEW (SUPERADMIN/OWNER/ADMIN) -->
    
    <!-- Mobile/Tablet: Grid Menu -->
    <div class="lg:hidden w-full p-4">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Beranda</h2>
            <p class="text-sm text-slate-500">Akses cepat menu BoneHacker</p>
        </div>
        <?= $this->include('App\Views\components\mobile_grid_menu') ?>
    </div>

    <!-- Desktop: Dashboard dengan Statistik -->
    <style>
        @media (max-width: 1023px) {
            .desktop-stats-admin { display: none !important; }
        }
        @media (min-width: 1024px) {
            .desktop-stats-admin { display: block !important; }
        }
    </style>
    <div class="desktop-stats-admin w-full space-y-6 p-4 md:p-6">
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

    </div>
<?php endif; ?>

<?= $this->endSection() ?>
