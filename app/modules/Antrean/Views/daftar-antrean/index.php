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

<body class="flex flex-col h-screen overflow-hidden p-4 md:p-6 gap-6">

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

    <!-- BREAK TIME OVERLAY -->
    <?php if (isset($breakData) && !empty($breakData)): ?>
        <div id="breakOverlay" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-slate-900/70 transition-all duration-500 px-8">
            <div class="relative w-full max-w-2xl bg-white p-12 rounded-[3rem] shadow-2xl flex flex-col items-center text-center">
                <div class="mb-8">
                    <div class="h-24 w-24 flex items-center justify-center bg-orange-50 rounded-full border-2 border-orange-100 shadow-inner">
                        <i class="fas fa-coffee text-orange-500 text-5xl animate-bounce"></i>
                    </div>
                </div>
                <div class="space-y-4 mb-10">
                    <h2 class="text-5xl font-black text-slate-900 uppercase tracking-tighter">SEDANG ISTIRAHAT</h2>
                    <p class="text-slate-400 font-bold tracking-[0.3em] uppercase text-xs">Mohon menunggu sebentar</p>
                </div>
                <div class="px-16 py-10 bg-slate-50 rounded-[2rem] border border-slate-100">
                    <span id="breakTimer" class="text-6xl font-black text-slate-800 font-mono tracking-tighter">--:--:--</span>
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
    <?php endif; ?>

    <!-- 3 COLUMNS -->
    <div id="queue-columns" class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">

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

    <!-- MARQUEE BOTTOM BAR -->
    <div class="white-card py-4 px-8 rounded-2xl mt-auto flex items-center overflow-hidden">
        <marquee behavior="scroll" direction="left" scrollamount="5" class="text-base font-bold w-full whitespace-nowrap">
            <span class="text-blue-500 font-black">PURWOKERTO:</span> <span style="color: var(--text-muted)">Jl. Dr. Gumbreg, Kel. Mersi, Kec. Purwokerto Timur</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="text-blue-500 font-black">PURBALINGGA:</span> <span style="color: var(--text-muted)">RT 3/RW 2, Dusun Parung Bongas, Desa Kradenan</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="text-blue-500 font-black">PEMALANG:</span> <span style="color: var(--text-muted)">Jl. Banjarsari, Bojongnangka</span>
        </marquee>
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

        setTimeout(() => { window.location.reload(); }, 60000);

        function initAutoScroll() {
            const containers = document.querySelectorAll('.auto-scroll-list');
            containers.forEach(container => {
                if (container.getAttribute('data-initialized')) return;
                const count = parseInt(container.getAttribute('data-count') || '0');
                if (count > 5) {
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
                    function scroll() {
                        pos -= speed;
                        const halfHeight = wrapper.offsetHeight / 2;
                        if (Math.abs(pos) >= halfHeight) { pos = 0; }
                        wrapper.style.transform = `translateY(${pos}px)`;
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