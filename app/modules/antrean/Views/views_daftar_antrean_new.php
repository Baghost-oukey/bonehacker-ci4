<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Antrian - Bone Hacker <?= esc($regionName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            transition: background-color 0.3s ease; 
            scroll-behavior: smooth;
        }

        /* ===== GRADIENTS ===== */
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Dark mode background */
        .dark.gradient-bg { 
            background: linear-gradient(135deg, #051515 0%, #0d2a2a 50%, #061818 100%) !important;
            background-attachment: fixed;
        }

        .dark.gradient-bg::before {
            background: radial-gradient(circle at 20% 50%, rgba(5, 21, 21, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(13, 42, 42, 0.3) 0%, transparent 50%);
        }

        /* ===== GLASS EFFECT ===== */
        .glass-card { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .dark .glass-card { 
            background: rgba(15, 45, 45, 0.85);
            border: 1px solid rgba(0, 200, 200, 0.15);
        }

        .dark .glass-card:hover {
            box-shadow: 0 20px 40px rgba(0, 200, 200, 0.1);
        }

        /* ===== QUEUE CARDS ===== */
        .queue-card { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(0);
        }

        .queue-card:hover {
            transform: translateX(4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .stat-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .stat-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        /* ===== HEADERS ===== */
        .column-header {
            background: linear-gradient(135deg, var(--header-color-1) 0%, var(--header-color-2) 100%);
            position: relative;
            overflow: hidden;
        }

        .column-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="white" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            pointer-events: none;
            opacity: 0.5;
        }

        /* ===== CUSTOM SCROLLBAR ===== */
        ::-webkit-scrollbar { 
            width: 8px; 
            height: 8px;
        }

        ::-webkit-scrollbar-track { 
            background: transparent; 
        }

        ::-webkit-scrollbar-thumb { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6b3a8a 100%);
        }

        .dark ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #00bfbf 0%, #00d4d4 100%);
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #00ffff 0%, #00e6e6 100%);
        }

        /* ===== DARK MODE STAT COLORS ===== */
        .dark .stat-value-waiting {
            color: #ffd700 !important;
        }

        .dark .stat-value-process {
            color: #00bfff !important;
        }

        .dark .stat-value-finished {
            color: #00ff00 !important;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes glow {
            0%, 100% {
                box-shadow: 0 0 5px rgba(239, 68, 68, 0.5), inset 0 0 5px rgba(239, 68, 68, 0.2);
            }
            50% {
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.8), inset 0 0 10px rgba(239, 68, 68, 0.3);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-in-left {
            animation: slideInLeft 0.6s ease-out;
        }

        .glow-red {
            animation: glow 1.5s ease-in-out infinite;
        }

        /* ===== BUTTONS ===== */
        .theme-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
        }

        .theme-btn:hover {
            transform: scale(1.1) rotate(20deg);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .dark .theme-btn {
            background: rgba(15, 45, 45, 0.9);
            border: 1px solid rgba(0, 200, 200, 0.3);
            color: #00d4d4;
        }

        .dark .theme-btn:hover {
            box-shadow: 0 8px 20px rgba(0, 200, 200, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .stat-card {
                padding: 1rem !important;
            }

            .stat-card .text-6xl {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen p-2 md:p-8 transition-colors duration-500" id="bodyNode">
    
    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-10 px-4 animate-in">
        <div class="text-center md:text-left mb-6 md:mb-0">
            <h1 class="text-4xl md:text-5xl font-black text-white dark:text-cyan-100 tracking-tight uppercase drop-shadow-lg">
                Monitor Antrian
            </h1>
            <p class="text-white/80 dark:text-cyan-200 font-semibold text-lg mt-2">Bone Hacker - <?= esc($regionName ?? 'Purwokerto') ?></p>
        </div>
        
        <div class="flex items-center space-x-8">
            <button onclick="toggleDarkMode()" class="theme-btn p-2 rounded-full bg-slate-700 dark:bg-white shadow-lg hover:shadow-xl">
                <span id="themeIcon" class="text-2xl">🌙</span>
            </button>
            <div class="text-center md:text-right">
                <div id="realtimeClock" class="text-5xl md:text-6xl font-black tabular-nums text-white dark:text-cyan-300 drop-shadow-lg">00:00:00</div>
                <div id="currentDate" class="text-white/70 dark:text-cyan-200 font-semibold uppercase text-sm mt-1"><?= esc($currentDate) ?></div>
            </div>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 animate-in" style="animation-delay: 0.1s;">
        <div class="stat-card stat-orange rounded-3xl p-6 shadow-2xl text-center transform transition">
            <div class="text-5xl md:text-7xl font-black drop-shadow-md stat-value-waiting"><?= esc($waiting_queues ?? 0) ?></div>
            <div class="text-xs md:text-sm font-bold text-white/90 dark:text-cyan-300 uppercase tracking-widest mt-3">Menunggu</div>
            <div class="mt-2 h-1 w-12 bg-white/40 mx-auto rounded-full"></div>
        </div>

        <div class="stat-card stat-blue rounded-3xl p-6 shadow-2xl text-center transform transition">
            <div class="text-5xl md:text-7xl font-black drop-shadow-md stat-value-process"><?= esc($processed_queues ?? 0) ?></div>
            <div class="text-xs md:text-sm font-bold text-white/90 dark:text-cyan-300 uppercase tracking-widest mt-3">Sedang Terapi</div>
            <div class="mt-2 h-1 w-12 bg-white/40 mx-auto rounded-full"></div>
        </div>

        <div class="stat-card stat-green rounded-3xl p-6 shadow-2xl text-center transform transition">
            <div class="text-5xl md:text-7xl font-black drop-shadow-md stat-value-finished"><?= esc($finished_queues ?? 0) ?></div>
            <div class="text-xs md:text-sm font-bold text-white/90 dark:text-cyan-300 uppercase tracking-widest mt-3">Selesai</div>
            <div class="mt-2 h-1 w-12 bg-white/40 mx-auto rounded-full"></div>
        </div>
    </div>
        </div>
   
    </div>

    <!-- QUEUE COLUMNS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 animate-in" style="animation-delay: 0.2s;">
        
        <!-- MENUNGGU COLUMN -->
        <div class="flex flex-col rounded-3xl overflow-hidden shadow-2xl">
            <div class="column-header text-white p-5 font-bold text-center uppercase tracking-widest text-lg" style="--header-color-1: #f97316; --header-color-2: #ea580c;">
                Menunggu
            </div>
            <div class="glass-card rounded-b-3xl shadow-none overflow-y-auto h-[550px] p-4 space-y-3 border-none">
                <?php 
                $waitingList = array_filter($patient_queues, fn($q) => !$q->process_at && !$q->finish_at);
                $i = 0;
                if (!empty($waitingList)):
                    foreach ($waitingList as $q): 
                        $i++;
                        // Highlight 3 teratas
                        $isHot = ($i <= 3) ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white glow-red shadow-lg scale-105' : 'bg-white dark:bg-slate-800 dark:border-cyan-500/30 text-slate-700 dark:text-cyan-100 hover:shadow-md';
                ?>
                    <div class="flex items-center justify-between p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 <?= $isHot ?> queue-card">
                        <div class="flex items-center flex-1 gap-3">
                            <div class="text-3xl font-black w-14 h-14 flex items-center justify-center bg-white dark:bg-slate-700 dark:border dark:border-cyan-400/30 rounded-xl text-orange-600 dark:text-cyan-300 shadow-sm"><?= esc($q->pq->id ?? '-') ?></div>
                            <div class="flex-1">
                                <div class="font-bold truncate uppercase text-sm"><?= esc($q->patient_name) ?></div>
                                <div class="text-xs opacity-70 dark:opacity-60 mt-1">Pasien <?= esc($q->patient_id) ?></div>
                            </div>
                        </div>
                        <?php if($i <= 3): ?>
                            <div class="text-lg font-black bg-yellow-400 dark:bg-yellow-300 text-black px-3 py-1 rounded-full">🔥 <?= $i ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; else: ?>
                    <div class="flex flex-col items-center justify-center py-16 text-slate-700 dark:text-slate-400">
                        <div class="text-lg font-semibold">Kosong</div>
                        <div class="text-sm">Tidak ada pasien menunggu</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SEDANG TERAPI COLUMN -->
        <div class="flex flex-col rounded-3xl overflow-hidden shadow-2xl">
            <div class="column-header text-white p-5 font-bold text-center uppercase tracking-widest text-lg" style="--header-color-1: #3b82f6; --header-color-2: #1d4ed8;">
                Sedang Terapi
            </div>
            <div class="glass-card rounded-b-3xl shadow-none overflow-y-auto h-[550px] p-4 space-y-3 border-none">
                <?php 
                $processingList = array_filter($patient_queues, fn($q) => $q->process_at && !$q->finish_at);
                if (!empty($processingList)):
                    foreach ($processingList as $q): 
                ?>
                    <div class="flex items-center justify-between p-5 rounded-2xl bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-800 dark:to-slate-700 dark:border-cyan-500/30 shadow-md border-l-4 border-blue-500 dark:border-l-cyan-400 queue-card">
                        <div class="flex items-center flex-1 gap-3">
                            <div class="text-3xl font-black w-14 h-14 flex items-center justify-center bg-blue-600 dark:bg-blue-700 dark:border dark:border-cyan-400/30 text-white rounded-xl shadow-sm"><?= esc($q->pq->id ?? '-') ?></div>
                            <div class="flex-1">
                                <div class="font-bold truncate uppercase text-sm dark:text-cyan-100"><?= esc($q->patient_name) ?></div>
                                <div class="text-xs text-slate-500 dark:text-cyan-300/70 mt-1">Sedang dalam proses</div>
                            </div>
                        </div>
                        <div class="animate-spin h-5 w-5 border-3 border-blue-500 dark:border-cyan-400 dark:border-t-transparent rounded-full"></div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-slate-400">
                        <div class="text-5xl mb-3"></div>
                        <div class="text-lg font-semibold">Tidak ada aktivitas</div>
                        <div class="text-sm">Tidak Ada pasein yang sedang di terapis</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SELESAI COLUMN -->
        <div class="flex flex-col rounded-3xl overflow-hidden shadow-2xl">
            <div class="column-header text-white p-5 font-bold text-center uppercase tracking-widest text-lg" style="--header-color-1: #10b981; --header-color-2: #059669;">
                Selesai
            </div>
            <div class="glass-card rounded-b-3xl shadow-none overflow-y-auto h-[550px] p-4 space-y-3 border-none">
                <?php 
                $finishedList = array_filter($patient_queues, fn($q) => $q->finish_at);
                if (!empty($finishedList)):
                    foreach ($finishedList as $q): 
                ?>
                    <div class="flex items-center justify-between p-5 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-800 dark:to-slate-700 dark:border-cyan-500/30 opacity-85 shadow-md border-l-4 border-green-500 dark:border-l-cyan-400 queue-card hover:opacity-100">
                        <div class="flex items-center flex-1 gap-3">
                            <div class="text-3xl font-black w-14 h-14 flex items-center justify-center bg-green-600 dark:bg-green-700 dark:border dark:border-cyan-400/30 text-white rounded-xl shadow-sm"><?= esc($q->pq->id ?? '-') ?></div>
                            <div class="flex-1">
                                <div class="font-bold truncate uppercase text-sm dark:text-cyan-100 line-through opacity-70"><?= esc($q->patient_name) ?></div>
                                <div class="text-xs text-slate-500 dark:text-cyan-300/70 mt-1">Selesai terapi</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 bg-green-500 dark:bg-green-600 text-white rounded-full shadow-md">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-cyan-400/50">
                        <div class="text-lg font-semibold">Belum ada yang selesai</div>
                        <div class="text-sm">Tunggu pasien menyelesaikan terapi</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="mt-12 mb-4 animate-in" style="animation-delay: 0.3s;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
            <div class="glass-card rounded-2xl p-6 shadow-lg border-l-4 border-orange-500">
                <div class="flex items-start gap-3">
                    <div class="text-3xl"></div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-cyan-100 uppercase tracking-widest text-sm">Cilacap</div>
                        <div class="text-sm text-slate-600 dark:text-cyan-200/70 mt-2">Jl. Munggur Barat, RT 4/RW 5, Kel. Mertasinga</div>
                    </div>
                </div>
            </div>
            <div class="glass-card rounded-2xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="flex items-start gap-3">
                    <div class="text-3xl"></div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-cyan-100 uppercase tracking-widest text-sm">Kedungreja</div>
                        <div class="text-sm text-slate-600 dark:text-cyan-200/70 mt-2">Jl. Raya Tambaksari, Pasar Blunder</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // REALTIME CLOCK FUNCTION
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('realtimeClock').textContent = `${hours}:${minutes}:${seconds}`;
            
            // AUTO DARK MODE AT 18:00 WIB
            applyAutoDarkMode(now);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // AUTO DARK MODE LOGIC
        function applyAutoDarkMode(dateTime) {
            const body = document.getElementById('bodyNode');
            const currentHour = dateTime.getHours();
            const isDarkModeManuallySet = localStorage.theme !== null && localStorage.theme !== 'auto';
            
            // Jika user belum pernah set theme manual, gunakan auto mode
            if (!isDarkModeManuallySet) {
                if (currentHour >= 18 || currentHour < 6) {
                    // Dark mode: 18:00 - 05:59
                    if (!body.classList.contains('dark')) {
                        body.classList.add('dark');
                        document.getElementById('themeIcon').textContent = '☀️';
                    }
                } else {
                    if (body.classList.contains('dark')) {
                        body.classList.remove('dark');
                        document.getElementById('themeIcon').textContent = '🌙';
                    }
                }
            }
        }

        // DARK MODE TOGGLE (MANUAL)
        function toggleDarkMode() {
            const body = document.getElementById('bodyNode');
            const icon = document.getElementById('themeIcon');
            body.classList.toggle('dark');
            
            if (body.classList.contains('dark')) {
                icon.textContent = '🌙';
                localStorage.theme = 'dark'; 
            } else {
                icon.textContent = '☀️';
                localStorage.theme = 'light'; 
            }
        }

        // Check local storage on load
        if (localStorage.theme === 'dark') {
            document.getElementById('bodyNode').classList.add('dark');
            document.getElementById('themeIcon').textContent = '🌙';
        } else if (localStorage.theme === 'light') {
            document.getElementById('bodyNode').classList.remove('dark');
            document.getElementById('themeIcon').textContent = '☀️';
        } else {
            const now = new Date();
            applyAutoDarkMode(now);
            localStorage.theme = 'auto'; 
        }
    </script>
</body>
</html>