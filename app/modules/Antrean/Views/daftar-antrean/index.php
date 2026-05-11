<?php
$isDevEnvironment = ENVIRONMENT === 'development';
$viteBrowserUrl = 'http://localhost:5173';
$shouldUseViteDevServer = false;

$waitingList = array_values(array_filter($patient_queues ?? [], static fn($q) => empty($q->process_at) && empty($q->finish_at)));
$processingList = array_values(array_filter($patient_queues ?? [], static fn($q) => !empty($q->process_at) && empty($q->finish_at)));
$finishedList = array_values(array_filter($patient_queues ?? [], static fn($q) => !empty($q->finish_at)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Antrean | Bone Hacker</title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Load CSS from Vite/Build -->
    <?php if ($shouldUseViteDevServer): ?>
        <link rel="stylesheet" href="<?= $viteBrowserUrl ?>/resources/css/app.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= base_url('build/assets/app.css') . '?v=' . (is_file(FCPATH . 'build/assets/app.css') ? filemtime(FCPATH . 'build/assets/app.css') : time()) ?>">
    <?php endif; ?>

    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        /* Ensure body takes full screen with gradient */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5dec5 0%, #e2e8f0 50%, #b2d4eb 100%);
        }
    </style>
</head>
<body class="flex flex-col p-4 md:p-8 gap-6 md:gap-8 text-slate-800">

    <?php if (isset($isPublic) && $isPublic): ?>
        <script>
            setTimeout(function() { location.reload(); }, 30000); // 30s auto refresh

            document.addEventListener('DOMContentLoaded', function() {
                const scrollContainers = document.querySelectorAll('.auto-scroll-list');
                scrollContainers.forEach(container => {
                    let scrollSpeed = 1; 
                    let isPaused = false;
                    function autoScroll() {
                        if (isPaused) return;
                        const maxScroll = container.scrollHeight - container.clientHeight;
                        if (maxScroll <= 0) return;
                        container.scrollTop += scrollSpeed;
                        if (container.scrollTop >= maxScroll) {
                            isPaused = true;
                            setTimeout(() => { container.scrollTop = 0; isPaused = false; }, 2000);
                        }
                    }
                    setInterval(autoScroll, 50); 
                });
            });
        </script>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-2">
        <div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 uppercase drop-shadow-sm">
                MONITOR ANTRIAN
            </h1>
            <p class="text-lg md:text-xl font-bold text-slate-600 mt-2">
                Bone Hacker - <?= esc($regionName ?? 'Semua Wilayah') ?>
            </p>
        </div>
        
        <div class="text-right flex flex-col items-end mt-4 md:mt-0">
            <div class="flex items-center gap-3">
                <div id="liveTime" class="text-5xl md:text-7xl font-black text-blue-600 tracking-wider font-mono drop-shadow-md"></div>
                <div class="h-12 w-12 flex items-center justify-center rounded-full bg-white/60 backdrop-blur-sm text-slate-600 shadow-sm border border-white/60">
                    <i class="fas fa-sun text-2xl"></i>
                </div>
            </div>
            <p id="liveDate" class="text-base md:text-lg font-bold text-slate-500 uppercase tracking-widest mt-2"></p>
            
            <script>
                function updateTime() {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
                    document.getElementById('liveTime').textContent = timeString;
                    
                    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                    const dateString = now.toLocaleDateString('id-ID', options);
                    document.getElementById('liveDate').textContent = dateString;
                }
                setInterval(updateTime, 1000);
                updateTime();
            </script>
        </div>
    </div>

    <!-- STATS ROW (Single White Box) -->
    <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-lg border border-white p-6 flex justify-around items-center divide-x-2 divide-slate-200">
        <!-- Menunggu -->
        <div class="flex flex-col items-center justify-center w-1/3 text-center">
            <span class="text-6xl md:text-8xl font-black text-orange-500 drop-shadow-sm leading-none"><?= count($waitingList) ?></span>
            <span class="text-base md:text-lg font-extrabold text-slate-500 uppercase tracking-widest mt-3">Menunggu</span>
        </div>
        <!-- Sedang Terapi -->
        <div class="flex flex-col items-center justify-center w-1/3 text-center">
            <span class="text-6xl md:text-8xl font-black text-blue-500 drop-shadow-sm leading-none"><?= count($processingList) ?></span>
            <span class="text-base md:text-lg font-extrabold text-slate-500 uppercase tracking-widest mt-3">Terapi</span>
        </div>
        <!-- Selesai -->
        <div class="flex flex-col items-center justify-center w-1/3 text-center">
            <span class="text-6xl md:text-8xl font-black text-green-600 drop-shadow-sm leading-none"><?= count($finishedList) ?></span>
            <span class="text-base md:text-lg font-extrabold text-slate-500 uppercase tracking-widest mt-3">Selesai</span>
        </div>
    </div>

    <!-- 3 COLUMNS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 flex-1 min-h-[45vh]">
        
        <!-- KOLOM MENUNGGU -->
        <div class="bg-orange-50/60 backdrop-blur-sm rounded-3xl shadow-lg border border-white overflow-hidden flex flex-col">
            <div class="bg-white/95 border-t-8 border-t-orange-500 py-4 text-center shadow-sm z-10 border-b border-b-orange-100">
                <h2 class="text-xl font-black text-orange-600 uppercase tracking-[0.2em]">Menunggu</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-4 md:p-5 space-y-4 auto-scroll-list no-scrollbar bg-white/40">
                <?php if (!empty($waitingList)): ?>
                    <?php foreach ($waitingList as $i => $q): ?>
                        <div class="flex items-center gap-4 bg-white/95 rounded-2xl p-4 shadow-sm border border-slate-100 transform transition-transform hover:scale-[1.02]">
                            <div class="h-14 w-14 shrink-0 flex items-center justify-center rounded-xl bg-white shadow-inner border border-slate-100">
                                <span class="text-3xl font-black text-slate-900"><?= esc($q->queue_number ?? '-') ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xl font-bold text-slate-900 uppercase truncate"><?= esc($q->patient_name ?? '-') ?></p>
                            </div>
                            <div class="text-right pr-2">
                                <span class="text-xl font-black text-orange-500">#<?= $i + 1 ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="h-full flex items-center justify-center">
                        <p class="text-slate-400 font-bold text-xl">KOSONG</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KOLOM SEDANG TERAPI -->
        <div class="bg-blue-50/60 backdrop-blur-sm rounded-3xl shadow-lg border border-white overflow-hidden flex flex-col">
            <div class="bg-white/95 border-t-8 border-t-blue-500 py-4 text-center shadow-sm z-10 border-b border-b-blue-100">
                <h2 class="text-xl font-black text-blue-600 uppercase tracking-[0.2em]">Sedang Terapi</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-4 md:p-5 space-y-4 auto-scroll-list no-scrollbar bg-white/40">
                <?php if (!empty($processingList)): ?>
                    <?php foreach ($processingList as $i => $q): ?>
                        <div class="flex items-center gap-4 bg-white/95 rounded-2xl p-4 shadow-sm border border-slate-100 transform transition-transform hover:scale-[1.02]">
                            <div class="h-14 w-14 shrink-0 flex items-center justify-center rounded-xl bg-white shadow-inner border border-slate-100">
                                <span class="text-3xl font-black text-slate-900"><?= esc($q->queue_number ?? '-') ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xl font-bold text-slate-900 uppercase truncate"><?= esc($q->patient_name ?? '-') ?></p>
                            </div>
                            <div class="text-right pr-2">
                                <i class="fas fa-spinner animate-spin text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="h-full flex items-center justify-center">
                        <p class="text-slate-400 font-bold text-xl">KOSONG</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KOLOM SELESAI -->
        <div class="bg-green-50/60 backdrop-blur-sm rounded-3xl shadow-lg border border-white overflow-hidden flex flex-col">
            <div class="bg-white/95 border-t-8 border-t-green-500 py-4 text-center shadow-sm z-10 border-b border-b-green-100">
                <h2 class="text-xl font-black text-green-600 uppercase tracking-[0.2em]">Selesai</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-4 md:p-5 space-y-4 auto-scroll-list no-scrollbar bg-white/40">
                <?php if (!empty($finishedList)): ?>
                    <?php foreach ($finishedList as $i => $q): ?>
                        <div class="flex items-center gap-4 bg-white/95 rounded-2xl p-4 shadow-sm border border-slate-100 opacity-80">
                            <div class="h-14 w-14 shrink-0 flex items-center justify-center rounded-xl bg-white shadow-inner border border-slate-100">
                                <span class="text-3xl font-black text-slate-900"><?= esc($q->queue_number ?? '-') ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xl font-bold text-slate-900 uppercase truncate"><?= esc($q->patient_name ?? '-') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="h-full flex items-center justify-center">
                        <p class="text-slate-400 font-bold text-xl">KOSONG</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- MARQUEE BOTTOM BAR -->
    <?php if (isset($isPublic) && $isPublic): ?>
    <div class="bg-white/90 backdrop-blur-md py-4 px-6 rounded-2xl border border-white shadow-md mt-auto flex items-center overflow-hidden">
        <marquee behavior="scroll" direction="left" scrollamount="6" class="text-base md:text-lg font-semibold text-slate-700 w-full whitespace-nowrap">
            <span class="text-blue-600 font-black italic">PURBALINGGA:</span> RT 3/RW 2, Dusun Parung Bongas, Desa Kradenan &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="text-blue-600 font-black italic">PEMALANG:</span> Jl. Banjarsari, Bojongnangka &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="text-blue-600 font-black italic">PURWOKERTO:</span> Jl. Dr. Gumbreg, Kel. Mersi, Kec. Purwokerto Timur
        </marquee>
    </div>
    <?php endif; ?>

    <!-- Load Vite App JS if needed -->
    <?php if ($shouldUseViteDevServer): ?>
        <script type="module" src="<?= $viteBrowserUrl ?>/@vite/client"></script>
        <script type="module" src="<?= $viteBrowserUrl ?>/resources/js/app.js"></script>
    <?php else: ?>
        <script type="module" src="<?= base_url('build/assets/app.js') . '?v=' . (is_file(FCPATH . 'build/assets/app.js') ? filemtime(FCPATH . 'build/assets/app.js') : time()) ?>"></script>
    <?php endif; ?>
</body>
</html>