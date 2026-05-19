<?php
$isDevEnvironment = ENVIRONMENT === 'development';
$viteBrowserUrl = 'http://localhost:5173';
$shouldUseViteDevServer = false;

$waitingList = array_values(array_filter($patient_queues ?? [], static fn($q) => empty($q->process_at) && empty($q->finish_at)));
$processingList = array_values(array_filter($patient_queues ?? [], static fn($q) => !empty($q->process_at) && empty($q->finish_at)));
$finishedList = array_values(array_filter($patient_queues ?? [], static fn($q) => !empty($q->finish_at)));

// Sort queues naturally by queue_number for the mobile view
$mobileQueues = $patient_queues ?? [];
usort($mobileQueues, function($a, $b) {
    return strnatcasecmp($a->queue_number ?? '', $b->queue_number ?? '');
});
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Antrean | Bone Hacker</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

    <?php if ($shouldUseViteDevServer): ?>
        <link rel="stylesheet" href="<?= $viteBrowserUrl ?>/resources/css/app.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= base_url('build/assets/app.css') . '?v=' . (is_file(FCPATH . 'build/assets/app.css') ? filemtime(FCPATH . 'build/assets/app.css') : time()) ?>">
    <?php endif; ?>

    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #fce7d2 0%, #e1e9f0 50%, #c5e1f5 100%);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white-card-bg: rgba(255, 255, 255, 0.9);
            --column-orange: #faede1;
            --column-blue: #e3f2f5;
            --column-green: #e3f5ef;
            --list-card-bg: #ffffff;
            --list-card-text: #1e293b;
            --header-text: #0f172a;
            --queue-num-bg: #f8fafc;
            --queue-num-text: #1e293b;
            --border-color: rgba(0, 0, 0, 0.05);
        }

        body.dark-mode {
            --bg-gradient: radial-gradient(circle at top right, #064e3b 0%, #020617 60%, #451a03 100%);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --white-card-bg: rgba(15, 23, 42, 0.8);
            --column-orange: rgba(251, 146, 60, 0.1);
            --column-blue: rgba(56, 189, 248, 0.1);
            --column-green: rgba(52, 211, 153, 0.1);
            --list-card-bg: rgba(30, 41, 59, 0.7);
            --list-card-text: #f1f5f9;
            --header-text: #ffffff;
            --queue-num-bg: #1e293b;
            --queue-num-text: #f1f5f9;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        body {
            margin: 0; padding: 0; min-height: 100vh;
            background: var(--bg-gradient);
            background-attachment: fixed;
            font-family: 'Outfit', sans-serif;
            color: var(--text-main);
            transition: background 0.5s ease;
        }

        .white-card {
            background: var(--white-card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .column-orange { background: var(--column-orange); border: 1px solid var(--border-color); }
        .column-blue { background: var(--column-blue); border: 1px solid var(--border-color); }
        .column-green { background: var(--column-green); border: 1px solid var(--border-color); }

        .header-orange { color: #f97316; }
        .header-blue { color: #38bdf8; }
        .header-green { color: #34d399; }

        .list-card-white {
            background: var(--list-card-bg);
            border: 1px solid var(--border-color);
            color: var(--list-card-text);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .active-waiting {
            background: #e11d48 !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 20px rgba(225, 29, 72, 0.4);
        }
        
        .active-waiting p, .active-waiting span { color: #ffffff !important; }
        .active-waiting .queue-num-box { background: #ffffff !important; color: #e11d48 !important; }

        .queue-num-box {
            background: var(--queue-num-bg);
            color: var(--queue-num-text);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }

        @keyframes translateX {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(300%); }
        }

        .theme-toggle-btn {
            width: 56px; height: 56px;
            background: var(--white-card-bg);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            cursor: pointer;
            display: flex; 
            align-items: center; 
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .theme-toggle-btn:hover { transform: scale(1.1); }
        .theme-toggle-btn i { font-size: 24px; color: var(--text-main); transition: transform 0.5s; display: block; line-height: 1; }
        .dark-mode .theme-toggle-btn i { transform: rotate(360deg); }
    </style>
</head>

<body class="flex flex-col lg:h-screen lg:overflow-hidden min-h-screen overflow-y-auto p-4 md:p-6 gap-6">

    <!-- DESKTOP MONITOR VIEW (KEEPS 3 COLUMNS INTACT) -->
    <div class="hidden lg:flex flex-col flex-1 min-h-0 gap-6 w-full">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight text-current" style="color: var(--header-text)">
                MONITOR ANTRIAN
            </h1>
            <p class="text-sm md:text-base font-semibold mt-1" style="color: var(--text-muted)">
                Bone Hacker - <?= esc($regionName ?? 'Semua Wilayah') ?>
            </p>
        </div>

        <div class="text-right flex items-center gap-8 mt-4 md:mt-0">
            <div class="text-right">
                <div id="liveTime" class="text-5xl md:text-6xl font-black text-blue-500 tracking-tighter font-mono"></div>
                <p id="liveDate" class="text-xs md:text-sm font-bold uppercase tracking-widest mt-1" style="color: var(--text-muted)"></p>
            </div>
            
            <button id="themeToggle" class="theme-toggle-btn">
                <i class="fas fa-sun" id="themeIcon"></i>
            </button>
        </div>
    </div>

    <!-- STATS PANEL -->
    <div class="white-card rounded-3xl p-6 flex flex-row items-center justify-around divide-x divide-slate-500/20">
        <!-- Menunggu -->
        <div class="flex flex-col items-center justify-center flex-1">
            <span class="text-6xl md:text-7xl font-black text-orange-500"><?= count($waitingList) ?></span>
            <span class="text-xs md:text-sm font-bold uppercase tracking-[0.2em] mt-2" style="color: var(--text-muted)">Menunggu</span>
        </div>

        <!-- Terapi -->
        <div class="flex flex-col items-center justify-center flex-1">
            <span class="text-6xl md:text-7xl font-black text-blue-500"><?= count($processingList) ?></span>
            <span class="text-xs md:text-sm font-bold uppercase tracking-[0.2em] mt-2" style="color: var(--text-muted)">Terapi</span>
        </div>

        <!-- Selesai -->
        <div class="flex flex-col items-center justify-center flex-1">
            <span class="text-6xl md:text-7xl font-black text-emerald-500"><?= count($finishedList) ?></span>
            <span class="text-xs md:text-sm font-bold uppercase tracking-[0.2em] mt-2" style="color: var(--text-muted)">Selesai</span>
        </div>
    </div>

    <!-- COLUMN CONTAINER WITH RELATIVE RED OVERLAY -->
    <div class="relative flex-1 min-h-0">
        <!-- 3 COLUMNS -->
        <div id="queue-columns" class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
            <!-- KOLOM MENUNGGU -->
            <div class="column-orange rounded-3xl overflow-hidden flex flex-col shadow-sm">
                <div class="py-5 text-center border-b border-white/10">
                    <h2 class="text-lg font-black header-orange uppercase tracking-[0.2em]">Menunggu</h2>
                </div>
                <div class="flex-1 overflow-y-hidden p-5 space-y-4 auto-scroll-list no-scrollbar" data-count="<?= count($waitingList) ?>">
                    <?php if (!empty($waitingList)): ?>
                        <?php foreach ($waitingList as $i => $q): ?>
                            <div class="list-card-white rounded-2xl p-4 flex items-center gap-5 <?= ($i === 0) ? 'active-waiting' : '' ?>">
                                <div class="h-14 w-14 shrink-0 flex items-center justify-center rounded-xl font-black text-2xl queue-num-box">
                                    <?= esc($q->queue_number ?? '-') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xl font-black uppercase tracking-tight truncate"><?= esc($q->patient_name ?? '-') ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black opacity-60">#<?= $i + 1 ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="h-full flex items-center justify-center">
                            <p class="text-orange-500/20 font-black text-4xl tracking-widest uppercase">KOSONG</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KOLOM SEDANG TERAPI -->
            <div class="column-blue rounded-3xl overflow-hidden flex flex-col shadow-sm">
                <div class="py-5 text-center border-b border-white/10">
                    <h2 class="text-lg font-black header-blue uppercase tracking-[0.2em]">Sedang Terapi</h2>
                </div>
                <div class="flex-1 overflow-y-hidden p-5 space-y-4 auto-scroll-list no-scrollbar" data-count="<?= count($processingList) ?>">
                    <?php if (!empty($processingList)): ?>
                        <?php foreach ($processingList as $i => $q): ?>
                            <div class="list-card-white rounded-2xl p-4 flex items-center gap-5 relative overflow-hidden">
                                <div class="absolute top-0 left-0 right-0 h-1 bg-blue-500/20">
                                    <div class="h-full bg-blue-500 w-1/4 animate-[translateX_3s_infinite]"></div>
                                </div>
                                <div class="h-14 w-14 shrink-0 flex items-center justify-center rounded-xl font-black text-2xl queue-num-box">
                                    <?= esc($q->queue_number ?? '-') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xl font-black uppercase tracking-tight truncate"><?= esc($q->patient_name ?? '-') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="h-full flex items-center justify-center">
                            <p class="text-blue-500/20 font-black text-4xl tracking-widest uppercase">KOSONG</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KOLOM SELESAI -->
            <div class="column-green rounded-3xl overflow-hidden flex flex-col shadow-sm">
                <div class="py-5 text-center border-b border-white/10">
                    <h2 class="text-lg font-black header-green uppercase tracking-[0.2em]">Selesai</h2>
                </div>
                <div class="flex-1 overflow-y-hidden p-5 space-y-4 auto-scroll-list no-scrollbar" data-count="<?= count($finishedList) ?>">
                    <?php if (!empty($finishedList)): ?>
                        <?php foreach ($finishedList as $i => $q): ?>
                            <div class="list-card-white rounded-2xl p-4 flex items-center gap-5 opacity-80">
                                <div class="h-14 w-14 shrink-0 flex items-center justify-center rounded-xl font-black text-2xl queue-num-box">
                                    <?= esc($q->queue_number ?? '-') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xl font-black uppercase tracking-tight truncate"><?= esc($q->patient_name ?? '-') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="h-full flex items-center justify-center">
                            <p class="text-emerald-500/20 font-black text-4xl tracking-widest uppercase">KOSONG</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RED OVERLAY ON BREAK (Pita Merah Mewah di Tengah Kolom) -->
        <?php if (isset($breakData) && !empty($breakData)): ?>
            <div id="breakOverlay" style="position: absolute; left: 0; right: 0; top: 50%; transform: translateY(-50%); z-index: 50; width: 100%; background: linear-gradient(135deg, rgba(220, 53, 69, 0.98) 0%, rgba(190, 24, 38, 0.98) 50%, rgba(220, 53, 69, 0.98) 100%); backdrop-filter: blur(16px); color: #ffffff; padding: 28px 0; box-shadow: 0 25px 60px rgba(220, 38, 38, 0.45), inset 0 2px 0 rgba(255,255,255,0.25), inset 0 -2px 0 rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; transition: all 0.5s ease-in-out;">
                
                <!-- Elegant Diagonal Warning Ribbon Accent (Top) -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: repeating-linear-gradient(-45deg, #991b1b, #991b1b 12px, #f43f5e 12px, #f43f5e 24px);"></div>
                
                <!-- Elegant Diagonal Warning Ribbon Accent (Bottom) -->
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 5px; background: repeating-linear-gradient(-45deg, #991b1b, #991b1b 12px, #f43f5e 12px, #f43f5e 24px);"></div>

                <!-- Glare Shine Effect overlay -->
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 40%, rgba(255,255,255,0) 100%); pointer-events: none;"></div>

                <div class="flex flex-col md:flex-row items-center justify-center gap-8 px-8 w-full max-w-7xl relative">
                    <!-- Live Pulse Badge -->
                    <div style="position: absolute; top: -50px; left: 50%; transform: translateX(-50%); background: #ef4444; border: 1.5px solid rgba(255,255,255,0.3); padding: 4px 16px; border-radius: 9999px; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(239,68,68,0.4);">
                        <span style="display: inline-block; width: 8px; height: 8px; background: #ffffff; border-radius: 50%; animation: pulseIndicator 1.5s infinite;"></span>
                        <span style="color: #ffffff; font-size: 10px; font-weight: 800; tracking-wider; text-transform: uppercase; letter-spacing: 0.15em; font-family: sans-serif;">ISTIRAHAT SEDANG BERLANGSUNG</span>
                    </div>
                    
                    <div class="flex items-center gap-5">
                        <!-- Neon Glowing Coffee Box -->
                        <div class="h-14 w-14 flex items-center justify-center rounded-2xl shrink-0 animate-bounce" style="background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.35); box-shadow: 0 8px 24px rgba(255,255,255,0.2), inset 0 2px 4px rgba(255,255,255,0.2);">
                            <i class="fas fa-coffee text-white text-2xl" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.4));"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl md:text-4xl font-extrabold uppercase tracking-tight text-white leading-none" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2); font-family: sans-serif;">SEDANG ISTIRAHAT</h2>
                            <p class="text-red-100 font-bold uppercase tracking-wider text-[10px] mt-1.5" style="color: rgba(255,255,255,0.85); letter-spacing: 0.05em; font-family: sans-serif;">Layanan antrean dijeda sejenak untuk istirahat terapis</p>
                        </div>
                    </div>
                    
                    <!-- Vertical Divider in desktop -->
                    <div class="hidden md:block h-12 w-px" style="background: rgba(255,255,255,0.25);"></div>
                    
                    <!-- Ultra Glass Timer Bubble -->
                    <div class="px-8 py-3 rounded-2xl shrink-0 flex items-center gap-4" style="background: rgba(0, 0, 0, 0.25); border: 1.5px solid rgba(255,255,255,0.15); box-shadow: inset 0 4px 12px rgba(0,0,0,0.15); backdrop-filter: blur(12px);">
                        <div class="flex flex-col text-right shrink-0">
                            <span style="font-size: 8px; font-weight: 800; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.1em; line-height: 1; font-family: sans-serif;">KEMBALI DALAM</span>
                            <span style="font-size: 9px; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; font-family: sans-serif;">COUNTDOWN</span>
                        </div>
                        <span id="breakTimer" class="text-3xl md:text-4xl font-black text-white font-mono tracking-tighter" style="letter-spacing: -0.02em; text-shadow: 0 0 10px rgba(255,255,255,0.3);">--:--:--</span>
                        <script>
                            (function() {
                                const endTimeStr = "<?= $breakData['end_time'] ?>";
                                const endTime = new Date(endTimeStr.replace(/-/g, '/')).getTime();
                                const timerEl = document.getElementById('breakTimer');

                                function updateTimer() {
                                    const now = new Date().getTime();
                                    const distance = endTime - now;

                                    if (distance <= 0) {
                                        timerEl.innerHTML = "00:00:00";
                                        return;
                                    }

                                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                    timerEl.innerHTML =
                                        (hours > 0 ? (hours < 10 ? "0" + hours + ":" : hours + ":") : "") +
                                        (minutes < 10 ? "0" + minutes : minutes) + ":" +
                                        (seconds < 10 ? "0" + seconds : seconds);
                                }

                                updateTimer();
                                setInterval(updateTimer, 1000);
                            })();
                        </script>
                    </div>
                </div>
            </div>
            
            <style>
                @keyframes pulseIndicator {
                    0% { transform: scale(0.85); opacity: 0.6; box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
                    50% { transform: scale(1.15); opacity: 1; box-shadow: 0 0 8px 4px rgba(255,255,255,0.6); }
                    100% { transform: scale(0.85); opacity: 0.6; box-shadow: 0 0 0 0 rgba(255,255,255,0); }
                }
            </style>
        <?php endif; ?>
    </div>

    <!-- MARQUEE BOTTOM BAR -->
    <div class="white-card py-4 px-8 rounded-2xl mt-auto flex items-center overflow-hidden">
        <marquee behavior="scroll" direction="left" scrollamount="5" class="text-base font-bold w-full whitespace-nowrap">
            <span class="text-blue-500 font-black">PURWOKERTO:</span> <span style="color: var(--text-muted)">Jl. Dr. Gumbreg, Kel. Mersi, Kec. Purwokerto Timur</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="text-blue-500 font-black">PURBALINGGA:</span> <span style="color: var(--text-muted)">RT 3/RW 2, Dusun Parung Bongas, Desa Kradenan</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="text-blue-500 font-black">PEMALANG:</span> <span style="color: var(--text-muted)">Jl. Banjarsari, Bojongnangka</span>
        </marquee>
    </div>

    </div> <!-- END DESKTOP MONITOR VIEW -->

    <!-- MOBILE VIEW (AS PER MOCKUP MENTIONS) -->
    <div class="block lg:hidden w-full max-w-2xl mx-auto space-y-6 pb-16 px-2">
        <!-- Header -->
        <div class="text-center py-4 space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--header-text)">Antrian Pasien</h1>
            <p class="text-xs font-medium tracking-wide" style="color: var(--text-muted)">
                Bone Hacker Live Status - Cabang <?= esc($regionName ?? 'Semua Wilayah') ?>
            </p>
        </div>

        <!-- Informasi Penting -->
        <div class="white-card rounded-2xl p-5 space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-widest" style="color: var(--text-muted)">Informasi Penting</h3>
            <ul class="text-xs space-y-2.5" style="color: var(--text-muted); font-weight: 300;">
                <li class="flex items-start">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 mr-2.5 shrink-0"></span>
                    <span>Periksa nomor urut dan status pendaftaran pasien terbaru.</span>
                </li>
                <li class="flex items-start">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 mr-2.5 shrink-0"></span>
                    <span>Rata-rata durasi terapi untuk setiap pasien adalah 15-20 menit, tergantung pada jenis keluhan dan tingkat keparahan.</span>
                </li>
                <li class="flex items-start">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 mr-2.5 shrink-0"></span>
                    <span>Pendaftaran dilakukan langsung di lokasi terapi. Urutan terapi mengikuti urutan kedatangan.</span>
                </li>
            </ul>
        </div>

        <!-- Ringkasan Antrian -->
        <div class="white-card rounded-2xl p-5 space-y-4">
            <h3 class="text-[10px] font-semibold uppercase tracking-widest text-center" style="color: var(--text-muted); opacity: 0.7;">Ringkasan Antrian</h3>
            <div class="grid grid-cols-3 gap-3 text-center">
                <!-- Menunggu -->
                <div class="pt-2.5 border-t-2 border-orange-500/80">
                    <span class="block text-2xl font-semibold text-orange-500"><?= count($waitingList) ?></span>
                    <span class="text-[9px] font-semibold uppercase mt-0.5 block tracking-wider" style="color: var(--text-muted)">Menunggu</span>
                </div>
                <!-- Terapi -->
                <div class="pt-2.5 border-t-2 border-blue-500/80">
                    <span class="block text-2xl font-semibold text-blue-500"><?= count($processingList) ?></span>
                    <span class="text-[9px] font-semibold uppercase mt-0.5 block tracking-wider" style="color: var(--text-muted)">Terapi</span>
                </div>
                <!-- Selesai -->
                <div class="pt-2.5 border-t-2 border-emerald-500/80">
                    <span class="block text-2xl font-semibold text-emerald-500"><?= count($finishedList) ?></span>
                    <span class="text-[9px] font-semibold uppercase mt-0.5 block tracking-wider" style="color: var(--text-muted)">Selesai</span>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="white-card rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/50 text-[10px] font-semibold uppercase tracking-wider" style="color: var(--text-muted)">
                            <th class="px-4 py-3 text-center w-12">No.</th>
                            <th class="px-4 py-3">Nama Pasien</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-center w-28">Posisi Antrian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/10 text-xs">
                        <?php if (!empty($mobileQueues)): ?>
                            <?php 
                            $waitingPos = 1;
                            foreach ($mobileQueues as $index => $q): 
                                $statusText = 'Menunggu';
                                $statusClass = 'bg-orange-500/10 text-orange-600 dark:text-orange-400';
                                $positionText = '-';

                                if (!empty($q->finish_at)) {
                                    $statusText = 'Selesai';
                                    $statusClass = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
                                } elseif (!empty($q->process_at)) {
                                    $statusText = 'Terapi';
                                    $statusClass = 'bg-blue-500/10 text-blue-600 dark:text-blue-400';
                                } else {
                                    $positionText = $waitingPos++;
                                }
                            ?>
                                <tr class="hover:bg-slate-100/5 transition-colors">
                                    <td class="px-4 py-3.5 text-center font-medium" style="color: var(--text-muted)"><?= $index + 1 ?></td>
                                    <td class="px-4 py-3.5 font-semibold uppercase tracking-tight" style="color: var(--text-main)">
                                        <?= esc($q->patient_name ?? '-') ?>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-medium" style="color: var(--text-muted)"><?= $positionText ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center italic font-medium" style="color: var(--text-muted)">
                                    Tidak ada antrean hari ini
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Live clock & footer updated -->
        <div class="text-center text-[10px]" style="color: var(--text-muted)">
            Terakhir diperbarui: <span id="mobileLastUpdatedTime" class="font-bold">--:-- WIB</span>
        </div>
    </div>

    <script>
        // --- THEME TOGGLE LOGIC ---
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        function setTheme(isDark) {
            if (isDark) {
                body.classList.add('dark-mode');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-mode');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'light');
            }
        }

        // Init theme from localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            setTheme(true);
        }

        themeToggle.addEventListener('click', () => {
            const isDark = body.classList.contains('dark-mode');
            setTheme(!isDark);
        });

        // --- LIVE CLOCK ---
        function updateLiveClock() {
            const now = new Date();
            const timeEl = document.getElementById('liveTime');
            const dateEl = document.getElementById('liveDate');

            if (timeEl) {
                timeEl.textContent = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).replace(/\./g, ' : ');
            }
            if (dateEl) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateEl.textContent = now.toLocaleDateString('id-ID', options).toUpperCase();
            }
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // --- MOBILE LAST UPDATED TIME ---
        const lastUpdatedEl = document.getElementById('mobileLastUpdatedTime');
        if (lastUpdatedEl) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            lastUpdatedEl.textContent = `${hours}.${minutes} WIB`;
        }

        setTimeout(() => { window.location.reload(); }, 60000);

        function initAutoScroll() {
            const containers = document.querySelectorAll('.auto-scroll-list');
            containers.forEach(container => {
                if (container.getAttribute('data-initialized')) return;
                const count = parseInt(container.getAttribute('data-count') || '0');
                if (count > 3) {
                    container.setAttribute('data-initialized', 'true');
                    const wrapper = document.createElement('div');
                    wrapper.className = 'scroll-wrapper space-y-4';
                    while (container.firstChild) { wrapper.appendChild(container.firstChild); }
                    const originalChildren = Array.from(wrapper.children);
                    originalChildren.forEach(child => { wrapper.appendChild(child.cloneNode(true)); });
                    container.appendChild(wrapper);
                    container.style.overflow = 'hidden';
                    
                    let pos = 0;
                    const speed = 0.8;
                    let isHovered = false;
                    
                    container.addEventListener('mouseenter', () => { isHovered = true; });
                    container.addEventListener('mouseleave', () => { isHovered = false; });
                    
                    function scroll() {
                        if (!isHovered) {
                            pos -= speed;
                            const halfHeight = wrapper.offsetHeight / 2;
                            if (Math.abs(pos) >= halfHeight) { pos = 0; }
                            wrapper.style.transform = `translateY(${pos}px)`;
                        }
                        requestAnimationFrame(scroll);
                    }
                    setTimeout(() => { requestAnimationFrame(scroll); }, 1000);
                }
            });
        }
        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initAutoScroll); } 
        else { initAutoScroll(); }
    </script>
</body>
</html>