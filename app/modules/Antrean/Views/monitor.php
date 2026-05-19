<?php
$isLogin = session()->get('isLogin');
$isSuperAdminOrOwner = ($role === 'superadmin' || $role === 'owner');

if ($isLogin):
    echo $this->extend('layout/layout');
    echo $this->section('content');
else:
?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Monitoring Antrean Pasien | Bone Hacker</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= base_url('build/assets/app.css') ?>">
        <style>
            body {
                background: linear-gradient(135deg, #fce7d2 0%, #e1e9f0 50%, #c5e1f5 100%);
                background-attachment: fixed;
                font-family: 'Outfit', sans-serif;
            }
        </style>
    </head>

    <body class="p-4 md:p-6 min-h-screen">
    <?php endif; ?>

    <div id="monitoringAntreanPage" class="w-full space-y-6 py-4 md:py-6">
        <!-- Load Google Font Outfit explicitly for maximum compatibility -->
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <style>
            /* Force Outfit font on every single element inside #monitoringAntreanPage */
            #monitoringAntreanPage,
            #monitoringAntreanPage * {
                font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
        </style>

        <?php if ($isSuperAdminOrOwner): ?>
            <!-- ==================== SUPER ADMIN & OWNER VIEW (GRID CABANG) ==================== -->
            <!-- HEADER -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between px-2">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-800 uppercase">
                        <?= esc($title) ?>
                    </h1>
                    <p class="text-sm text-slate-500 font-semibold">
                        Status Real-time Antrean Pasien Cabang Bone Hacker
                    </p>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700 shadow-sm border border-slate-200">
                        <i class="fa-solid fa-calendar-days text-teal-600"></i>
                        <span>Hari Ini: <?= date('d F Y') ?></span>
                    </div>
                </div>
            </div>

            <!-- STATS PANEL (RINGKASAN SEMUA CABANG) -->
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200/50 shadow-sm">
                <div class="mb-5 px-2">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Ringkasan Semua Cabang</h3>
                </div>
                <div class="flex flex-row gap-2 divide-x divide-slate-100 text-center">
                    <!-- Menunggu -->
                    <div class="flex-1 flex flex-col items-center justify-center p-2 min-w-0">
                        <span class="text-3xl md:text-5xl font-black text-amber-500"><?= esc($totalWaiting) ?></span>
                        <span class="text-[9px] md:text-xs font-black uppercase tracking-wider text-slate-400 mt-2">Total Menunggu</span>
                    </div>
                    <!-- Terapi -->
                    <div class="flex-1 flex flex-col items-center justify-center p-2 min-w-0">
                        <span class="text-3xl md:text-5xl font-black text-blue-500"><?= esc($totalProcessing) ?></span>
                        <span class="text-[9px] md:text-xs font-black uppercase tracking-wider text-slate-400 mt-2">Total Terapi</span>
                    </div>
                    <!-- Selesai -->
                    <div class="flex-1 flex flex-col items-center justify-center p-2 min-w-0">
                        <span class="text-3xl md:text-5xl font-black text-emerald-500"><?= esc($totalFinished) ?></span>
                        <span class="text-[9px] md:text-xs font-black uppercase tracking-wider text-slate-400 mt-2">Total Selesai</span>
                    </div>
                    <!-- Total Pasien -->
                    <div class="flex-1 flex flex-col items-center justify-center p-2 min-w-0">
                        <span class="text-3xl md:text-5xl font-black text-slate-800"><?= esc($totalPatients) ?></span>
                        <span class="text-[9px] md:text-xs font-black uppercase tracking-wider text-slate-400 mt-2">Total Pasien</span>
                    </div>
                </div>
            </div>

            <!-- CONTROLS & SEARCH -->
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-200/50">
                <!-- Search -->
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </span>
                    <input type="text" id="patientSearchInput" placeholder="Cari nama pasien..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-semibold placeholder:font-normal" />
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    <button type="button" id="btnToggleAll" onclick="toggleAllCards()"
                        class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-wider text-slate-700 shadow-sm transition hover:bg-slate-50 active:scale-95">
                        <i class="fas fa-expand-alt" id="toggleAllIcon"></i>
                        <span id="toggleAllText">Buka Semua</span>
                    </button>
                    <button type="button" onclick="window.location.reload()"
                        class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-lg shadow-teal-600/10 transition hover:bg-teal-700 active:scale-95">
                        <i class="fas fa-sync-alt mr-1.5"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- PANDUAN KODE QR CABANG (Untuk Super Admin & Owner) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/50 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 flex items-center justify-center bg-teal-50 text-teal-600 rounded-xl shrink-0">
                        <i class="fas fa-qrcode text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Tautan QR Code Antrean Cabang</h3>
                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Gunakan tautan di bawah ini untuk membuat kode QR yang ditempel di masing-masing lokasi cabang.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-2">
                    <?php foreach ($regions as $reg):
                        $qrUrl = base_url('antrean/monitor') . '?region=' . $reg->id;
                    ?>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex flex-col justify-between gap-3 hover:border-teal-200/60 transition-all duration-300">
                            <div>
                                <span class="text-[9px] font-black uppercase text-teal-600 tracking-wider">Cabang ID #<?= esc($reg->id) ?></span>
                                <h4 class="text-xs font-black text-slate-800 uppercase mt-1"><?= esc($reg->name) ?></h4>
                            </div>
                            <div class="space-y-2">
                                <input type="text" readonly value="<?= $qrUrl ?>"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-[9px] font-mono text-slate-500 focus:outline-none select-all" id="qr-input-<?= $reg->id ?>">
                                <button type="button" onclick="copyQrLink(<?= $reg->id ?>)"
                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-white shadow-sm hover:bg-teal-700 transition active:scale-95">
                                    <i class="fas fa-copy"></i>
                                    <span>Salin Tautan</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- GRID CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="regionGridContainer">
                <?php foreach ($queuesByRegion as $regionId => $data):
                    $reg = $data['region'];
                    $waitingCount = count($data['waiting']);
                    $processingCount = count($data['processing']);
                    $finishedCount = count($data['finished']);
                    $totalCount = count($data['all_patients']);
                ?>
                    <!-- CARD FOR BRANCH -->
                    <div class="region-card bg-white rounded-3xl border border-slate-200/50 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group"
                        data-region-id="<?= $regionId ?>"
                        data-region-name="<?= esc(strtolower($reg->name)) ?>">

                        <!-- Card Header -->
                        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/50 cursor-pointer select-none" onclick="toggleCard(<?= $regionId ?>)">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full bg-teal-500 animate-pulse"></div>
                                <h4 class="text-base md:text-lg font-black text-slate-800 uppercase tracking-wider">
                                    <?= esc($reg->name) ?>
                                </h4>
                            </div>
                            <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-chevron-down text-base transition-transform duration-300" id="chevron-<?= $regionId ?>"></i>
                            </button>
                        </div>

                        <!-- Stats Bar -->
                        <div class="flex flex-row border-b border-slate-100 text-center">
                            <!-- Menunggu -->
                            <div class="flex-1 min-w-0 flex flex-col items-center justify-center py-4 md:py-5 border-r border-slate-100 bg-amber-50/20">
                                <span class="text-2xl md:text-3xl font-black text-amber-500"><?= $waitingCount ?></span>
                                <span class="text-[10px] md:text-xs font-black uppercase text-slate-400 mt-1.5">Menunggu</span>
                            </div>
                            <!-- Terapi -->
                            <div class="flex-1 min-w-0 flex flex-col items-center justify-center py-4 md:py-5 border-r border-slate-100 bg-blue-50/20">
                                <span class="text-2xl md:text-3xl font-black text-blue-500"><?= $processingCount ?></span>
                                <span class="text-[10px] md:text-xs font-black uppercase text-slate-400 mt-1.5">Terapi</span>
                            </div>
                            <!-- Selesai -->
                            <div class="flex-1 min-w-0 flex flex-col items-center justify-center py-4 md:py-5 border-r border-slate-100 bg-emerald-50/20">
                                <span class="text-2xl md:text-3xl font-black text-emerald-500"><?= $finishedCount ?></span>
                                <span class="text-[10px] md:text-xs font-black uppercase text-slate-400 mt-1.5">Selesai</span>
                            </div>
                            <!-- Total -->
                            <div class="flex-1 min-w-0 flex flex-col items-center justify-center py-4 md:py-5 bg-slate-50/30">
                                <span class="text-2xl md:text-3xl font-black text-slate-700"><?= $totalCount ?></span>
                                <span class="text-[10px] md:text-xs font-black uppercase text-slate-400 mt-1.5">Total</span>
                            </div>
                        </div>

                        <!-- Card Content (Patient Queue Lists) - Hidden by default, toggled via JS -->
                        <div class="hidden overflow-hidden transition-all duration-300" id="content-<?= $regionId ?>">
                            <div class="p-4 space-y-4 max-h-[360px] overflow-y-auto bg-slate-50/30 no-scrollbar">
                                <!-- Menunggu Sub-list -->
                                <?php if (!empty($data['waiting'])): ?>
                                    <div class="space-y-2">
                                        <div class="text-[9px] font-black uppercase tracking-wider text-amber-500 px-1">Daftar Menunggu</div>
                                        <?php foreach ($data['waiting'] as $p): ?>
                                            <div class="patient-item flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-200/50 shadow-sm" data-patient-name="<?= esc(strtolower($p->patient_name)) ?>">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="h-9 w-9 shrink-0 flex items-center justify-center bg-amber-50 text-amber-600 rounded-xl font-black text-xs border border-amber-200/50">
                                                        No.<?= esc($p->queue_number) ?>
                                                    </div>
                                                    <div class="truncate">
                                                        <div class="text-xs font-black uppercase text-slate-800 truncate"><?= esc($p->patient_name) ?></div>
                                                        <div class="text-[9px] text-slate-400 mt-0.5">ID: <?= esc($p->patient_id) ?> &bull; Umur: <?= esc($p->patient_age) ?>th</div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Terapi Sub-list -->
                                <?php if (!empty($data['processing'])): ?>
                                    <div class="space-y-2">
                                        <div class="text-[9px] font-black uppercase tracking-wider text-blue-500 px-1">Sedang Terapi</div>
                                        <?php foreach ($data['processing'] as $p): ?>
                                            <div class="patient-item flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-200/50 shadow-sm" data-patient-name="<?= esc(strtolower($p->patient_name)) ?>">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="h-9 w-9 shrink-0 flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl font-black text-xs border border-blue-200/50">
                                                        No.<?= esc($p->queue_number) ?>
                                                    </div>
                                                    <div class="truncate">
                                                        <div class="text-xs font-black uppercase text-slate-800 truncate"><?= esc($p->patient_name) ?></div>
                                                        <div class="text-[9px] text-slate-400 mt-0.5">ID: <?= esc($p->patient_id) ?> &bull; Sedang diterapi</div>
                                                    </div>
                                                </div>
                                                <div class="flex h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500 animate-ping"></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Selesai Sub-list -->
                                <?php if (!empty($data['finished'])): ?>
                                    <div class="space-y-2">
                                        <div class="text-[9px] font-black uppercase tracking-wider text-emerald-500 px-1">Selesai</div>
                                        <?php foreach ($data['finished'] as $p): ?>
                                            <div class="patient-item flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-200/50 shadow-sm" data-patient-name="<?= esc(strtolower($p->patient_name)) ?>">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="h-9 w-9 shrink-0 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl font-black text-xs border border-emerald-200/50">
                                                        No.<?= esc($p->queue_number) ?>
                                                    </div>
                                                    <div class="truncate">
                                                        <div class="text-xs font-black uppercase text-slate-400 truncate line-through"><?= esc($p->patient_name) ?></div>
                                                        <div class="text-[9px] text-slate-400 mt-0.5">Selesai Terapi</div>
                                                    </div>
                                                </div>
                                                <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($data['all_patients'])): ?>
                                    <div class="flex flex-col items-center justify-center py-10 text-center">
                                        <i class="fas fa-clipboard-list text-slate-300 text-3xl mb-2"></i>
                                        <span class="text-xs font-semibold text-slate-400">Tidak ada antrean hari ini</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- ==================== ADMIN CABANG, TERAPIS & PATIENT STANDALONE VIEW (TABLE VIEW) ==================== -->
            <?php
            $singleRegion = !empty($regions) ? $regions[0] : null;
            $singleData = $singleRegion ? $queuesByRegion[$singleRegion->id] : null;
            $waitingCount = $singleData ? count($singleData['waiting']) : 0;
            $processingCount = $singleData ? count($singleData['processing']) : 0;
            $finishedCount = $singleData ? count($singleData['finished']) : 0;
            $regionName = $singleRegion ? $singleRegion->name : 'Cabang';
            ?>

            <div class="max-w-4xl mx-auto space-y-8 py-6">
                <!-- Title Header -->
                <div class="text-center space-y-2">
                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 tracking-tight">Antrian Pasien</h1>
                    <p class="text-sm md:text-base text-slate-500 font-bold uppercase tracking-wider">
                        Bone Hacker Live Status - Cabang <?= esc($regionName) ?>
                    </p>
                </div>

                <!-- Branch Selector Dropdown (Sangat Premium) -->
                <?php if (!$isLogin && count($regions) > 1): ?>
                    <div class="flex justify-center">
                        <div class="inline-flex items-center gap-3 bg-white border border-slate-100 px-4 py-2.5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Pilih Lokasi Cabang:</span>
                            <select onchange="switchPublicRegion(this.value)"
                                class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-black uppercase text-slate-700 focus:outline-none focus:border-teal-500 transition cursor-pointer">
                                <?php
                                foreach ($regions as $ar):
                                    $selected = ($singleRegion && $singleRegion->id == $ar->id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $ar->id ?>" <?= $selected ?>><?= esc($ar->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Card 1: Informasi Penting -->
                <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200/50 shadow-sm space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Informasi Penting</h3>
                    <ul class="list-disc pl-5 text-xs md:text-sm text-slate-500 space-y-3 font-semibold leading-relaxed">
                        <li>Periksa nomor urut dan status pendaftaran pasien terbaru.</li>
                        <li>Rata-rata durasi terapi untuk setiap pasien adalah 15-20 menit, tergantung pada jenis keluhan dan tingkat keparahan.</li>
                        <li>Pendaftaran dilakukan langsung di lokasi terapi. Urutan terapi mengikuti urutan kedatangan.</li>
                    </ul>
                </div>

                <!-- Card 2: Ringkasan Antrian -->
                <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200/50 shadow-sm space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Ringkasan Antrian</h3>
                    <div class="flex flex-row gap-6 text-center">
                        <!-- Menunggu -->
                        <div class="flex-1 border-t-4 border-orange-500 pt-4 min-w-0">
                            <span class="text-3xl md:text-4xl font-black text-orange-500"><?= $waitingCount ?></span>
                            <p class="text-xs md:text-sm font-bold text-slate-600 mt-1">Menunggu</p>
                        </div>
                        <!-- Terapi -->
                        <div class="flex-1 border-t-4 border-blue-500 pt-4 min-w-0">
                            <span class="text-3xl md:text-4xl font-black text-blue-500"><?= $processingCount ?></span>
                            <p class="text-xs md:text-sm font-bold text-slate-600 mt-1">Terapi</p>
                        </div>
                        <!-- Selesai -->
                        <div class="flex-1 border-t-4 border-emerald-500 pt-4 min-w-0">
                            <span class="text-3xl md:text-4xl font-black text-emerald-500"><?= $finishedCount ?></span>
                            <p class="text-xs md:text-sm font-bold text-slate-600 mt-1">Selesai</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Patient Table -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/40 border-b border-slate-100/80">
                                    <th class="px-4 py-4 md:px-6 md:py-5 text-[10px] md:text-xs font-black uppercase text-slate-400 tracking-widest whitespace-nowrap w-[15%]">No.</th>
                                    <th class="px-4 py-4 md:px-6 md:py-5 text-[10px] md:text-xs font-black uppercase text-slate-400 tracking-widest whitespace-nowrap w-[45%]">Nama Pasien</th>
                                    <th class="px-4 py-4 md:px-6 md:py-5 text-[10px] md:text-xs font-black uppercase text-slate-400 tracking-widest whitespace-nowrap w-[20%]">Status</th>
                                    <th class="px-4 py-4 md:px-6 md:py-5 text-[10px] md:text-xs font-black uppercase text-slate-400 tracking-widest whitespace-nowrap w-[20%] text-center">Posisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/70 font-bold text-slate-700">
                                <?php
                                $waitingPosition = 0;
                                if ($singleData && !empty($singleData['all_patients'])):
                                    foreach ($singleData['all_patients'] as $p):
                                        $statusText = '';
                                        $statusClass = '';
                                        $posisi = '-';

                                        if ($p->finish_at !== null) {
                                            $statusText = 'Selesai';
                                            $statusClass = 'text-emerald-500 bg-emerald-50 px-2.5 py-1 rounded-full text-[10px]';
                                        } else if ($p->process_at !== null) {
                                            $statusText = 'Terapi';
                                            $statusClass = 'text-blue-500 bg-blue-50 px-2.5 py-1 rounded-full text-[10px]';
                                        } else {
                                            $statusText = 'Menunggu';
                                            $statusClass = 'text-amber-500 bg-amber-50 px-2.5 py-1 rounded-full text-[10px]';
                                            $waitingPosition++;
                                            $posisi = $waitingPosition;
                                        }
                                ?>
                                        <tr class="hover:bg-slate-50/30 transition-colors">
                                            <td class="px-4 py-4.5 md:px-6 md:py-5 text-slate-800 font-black text-sm"><?= esc($p->queue_number) ?></td>
                                            <td class="px-4 py-4.5 md:px-6 md:py-5 text-slate-800 font-black text-sm uppercase truncate max-w-[120px] md:max-w-none"><?= esc($p->patient_name) ?></td>
                                            <td class="px-4 py-4.5 md:px-6 md:py-5 font-black"><span class="<?= $statusClass ?>"><?= $statusText ?></span></td>
                                            <td class="px-4 py-4.5 md:px-6 md:py-5 text-slate-500 font-black text-sm text-center"><?= $posisi ?></td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-16 text-slate-400/80 font-black uppercase tracking-widest text-xs md:text-sm">
                                            <div class="flex flex-col items-center justify-center gap-3">
                                                <i class="fas fa-clipboard-list text-slate-200 text-4xl"></i>
                                                <span>Tidak ada antrean hari ini</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Timestamp -->
                <div class="text-center text-xs text-slate-400 font-bold uppercase tracking-wider">
                    Terakhir diperbarui: <?= date('H.i') ?> WIB
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script>
        let isAllOpened = false;

        // Toggle single card open/close
        function toggleCard(regionId) {
            const content = document.getElementById(`content-${regionId}`);
            const chevron = document.getElementById(`chevron-${regionId}`);

            if (!content || !chevron) return;

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }

        // Toggle all cards open/close
        function toggleAllCards() {
            const toggleAllIcon = document.getElementById('toggleAllIcon');
            const toggleAllText = document.getElementById('toggleAllText');
            const regionCards = document.querySelectorAll('.region-card');

            isAllOpened = !isAllOpened;

            regionCards.forEach(card => {
                const regionId = card.getAttribute('data-region-id');
                const content = document.getElementById(`content-${regionId}`);
                const chevron = document.getElementById(`chevron-${regionId}`);

                if (content && chevron) {
                    if (isAllOpened) {
                        content.classList.remove('hidden');
                        chevron.classList.add('rotate-180');
                    } else {
                        content.classList.add('hidden');
                        chevron.classList.remove('rotate-180');
                    }
                }
            });

            if (isAllOpened) {
                toggleAllIcon.className = 'fas fa-compress-alt';
                toggleAllText.textContent = 'Tutup Semua';
            } else {
                toggleAllIcon.className = 'fas fa-expand-alt';
                toggleAllText.textContent = 'Buka Semua';
            }
        }

        // Live search functionality for branch regions and patient names
        const searchInput = document.getElementById('patientSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                        const query = e.target.value.toLowerCase().trim();
                        const cards = document.querySelectorAll('.region-card');

                        cards.forEach(card => {
                            const regionId = card.getAttribute('data-region-id');
                            const regionName = card.getAttribute('data-region-name');
                            const content = document.getElementById(`content-${regionId}`);
                            const chevron = document.getElementById(`chevron-${regionId}`);
                            const patientItems = card.querySelectorAll('.patient-item');

                            let hasMatchingPatient = false;

                            if (query === '') {
                                // Reset to default collapsed state
                                card.style.display = '';
                                patientItems.forEach(item => item.style.display = '');

                                // Only collapse all if they weren't explicitly opened before search
                                if (!isAllOpened) {
                                    content.classList.add('hidden');
                                    chevron.classList.remove('rotate-180');
                                }
                                return;
                            }

                            // Check patients inside card
                            patientItems.forEach(item => {
                                const patientName = item.getAttribute('data-patient-name');
                                if (patientName.includes(query)) {
                                    item.style.display = '';
                                    hasMatchingPatient = true;
                                } else {
                                    item.style.display = 'none';
                                }
                            });

                            // Card is visible if region name matches OR contains a matching patient name
                            if (regionName.includes(query) || hasMatchingPatient) {
                                card.style.display = '';
                                // Automatically open card to display matching patients
                                content.classList.remove('hidden');
                                chevron.classList.add('rotate-180');
                            } else {
                                card.style.display = 'none';
                                content.classList.add('hidden');
                                chevron.classList.remove('rotate-180');
                            }
                        });
                    }

                    // Function to copy QR Code Link beautifully
                    function copyQrLink(regionId) {
                        const input = document.getElementById(`qr-input-${regionId}`);
                        if (input) {
                            input.select();
                            input.setSelectionRange(0, 99999); // For mobile devices
                            navigator.clipboard.writeText(input.value).then(() => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Tautan Berhasil Disalin!',
                                    text: input.value,
                                    showConfirmButton: false,
                                    timer: 1800,
                                    customClass: {
                                        popup: 'rounded-3xl font-semibold'
                                    }
                                });
                            }).catch(err => {
                                console.error('Gagal menyalin tautan: ', err);
                            });
                        }

                        // Function to switch public region dropdown
                        function switchPublicRegion(regionId) {
                            const url = new URL(window.location.href);
                            url.searchParams.set('region', regionId);
                            window.location.href = url.toString();
                        }
    </script>

    <?php if ($isLogin): ?>
        <?= $this->endSection() ?>
    <?php else: ?>
    </body>

    </html>
<?php endif; ?>