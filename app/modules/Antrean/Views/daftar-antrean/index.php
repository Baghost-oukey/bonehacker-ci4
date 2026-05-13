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
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #1e293b 0%, #0f172a 50%, #020617 100%);
            background-attachment: fixed;
            font-family: 'Outfit', sans-serif;
            color: #f1f5f9;
        }

        .glow-orange {
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.4), inset 0 0 10px rgba(249, 115, 22, 0.2);
            border: 1px solid rgba(249, 115, 22, 0.6);
        }

        .glow-blue {
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.4), inset 0 0 10px rgba(6, 182, 212, 0.2);
            border: 1px solid rgba(6, 182, 212, 0.6);
        }

        .glow-green {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4), inset 0 0 10px rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.6);
        }

        .glow-text-orange {
            text-shadow: 0 0 10px rgba(249, 115, 22, 0.8);
        }

        .glow-text-blue {
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.8);
        }

        .glow-text-green {
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.8);
        }

        .dark-card {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
            backdrop-filter: blur(10px);
        }

        .dark-column {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(51, 65, 85, 0.5);
        }

        .list-card-orange {
            background: linear-gradient(90deg, rgba(249, 115, 22, 0.1) 0%, rgba(15, 23, 42, 0.8) 100%);
            border: 1px solid rgba(249, 115, 22, 0.4);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .list-card-blue {
            background: linear-gradient(90deg, rgba(6, 182, 212, 0.1) 0%, rgba(15, 23, 42, 0.8) 100%);
            border: 1px solid rgba(6, 182, 212, 0.4);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .list-card-green {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.1) 0%, rgba(15, 23, 42, 0.8) 100%);
            border: 1px solid rgba(16, 185, 129, 0.4);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="flex flex-col h-screen overflow-hidden p-4 md:p-6 gap-6 text-slate-100">

    <?php if (isset($isPublic) && $isPublic): ?>
        <!-- JS logic is handled by antrean_monitor.js -->
    <?php endif; ?>

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-2">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-4xl md:text-5xl font-black tracking-widest text-slate-100 drop-shadow-lg">
                    MONITOR ANTRIAN
                </h1>
                <p class="text-base md:text-lg font-semibold text-slate-300 mt-1 tracking-wider">
                    Bone Hacker - <?= esc($regionName ?? 'Semua Wilayah') ?>
                </p>
            </div>
        </div>

        <div class="text-right flex flex-col items-end mt-4 md:mt-0">
            <div class="glow-orange rounded-xl px-6 py-2 flex flex-col items-end justify-center dark-card">
                <div class="flex items-center gap-4">
                    <div id="liveTime" class="text-4xl md:text-5xl font-black text-orange-400 tracking-widest glow-text-orange font-mono"></div>
                    <i class="fas fa-cog text-slate-400 text-xl animate-[spin_4s_linear_infinite]"></i>
                </div>
                <p id="liveDate" class="text-xs md:text-sm font-bold text-slate-300 uppercase tracking-[0.2em] mt-1"></p>
            </div>
        </div>
    </div>

    <!-- STATS ROW (3 Separate Glowing Boxes) -->
    <div id="stats-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
        <!-- Menunggu -->
        <div class="dark-card glow-orange rounded-xl p-6 flex flex-col items-center justify-center relative overflow-hidden">
            <span class="text-6xl md:text-7xl font-black text-white drop-shadow-[0_4px_10px_rgba(0,0,0,0.5)] z-10"><?= count($waitingList) ?></span>
            <span class="text-xs md:text-sm font-bold text-slate-300 uppercase tracking-[0.2em] mt-2 z-10">Menunggu Pasien</span>
            <div class="text-orange-500/20 text-7xl absolute right-6 top-1/2 -translate-y-1/2">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>

        <!-- Terapi -->
        <div class="dark-card glow-blue rounded-xl p-6 flex flex-col items-center justify-center relative overflow-hidden">
            <span class="text-6xl md:text-7xl font-black text-white drop-shadow-[0_4px_10px_rgba(0,0,0,0.5)] z-10"><?= count($processingList) ?></span>
            <span class="text-xs md:text-sm font-bold text-slate-300 uppercase tracking-[0.2em] mt-2 z-10">Pasien Terapi</span>
            <div class="text-cyan-500/20 text-7xl absolute right-6 top-1/2 -translate-y-1/2">
                <i class="fas fa-stethoscope"></i>
            </div>
        </div>

        <!-- Selesai -->
        <div class="dark-card glow-green rounded-xl p-6 flex flex-col items-center justify-center relative overflow-hidden">
            <span class="text-6xl md:text-7xl font-black text-white drop-shadow-[0_4px_10px_rgba(0,0,0,0.5)] z-10"><?= count($finishedList) ?></span>
            <span class="text-xs md:text-sm font-bold text-slate-300 uppercase tracking-[0.2em] mt-2 z-10">Pasien Selesai</span>
            <div class="text-emerald-500/20 text-7xl absolute right-6 top-1/2 -translate-y-1/2">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- BREAK TIME OVERLAY (Full Screen) -->
    <?php if (isset($breakData) && !empty($breakData)): ?>
        <div id="breakOverlay" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-slate-900/75 transition-all duration-500 px-8">
            <!-- White Container -->
            <div class="relative w-full max-w-2xl bg-white p-10 rounded-2xl shadow-[0_20px_70px_rgba(0,0,0,0.5)] flex flex-col items-center text-center">

                <!-- Icon -->
                <div class="mb-6">
                    <div class="relative h-20 w-20 flex items-center justify-center bg-orange-50 rounded-full border-2 border-orange-100 shadow-inner">
                        <i class="fas fa-coffee text-slate-800 text-4xl relative animate-bounce"></i>
                    </div>
                </div>

                <!-- Text -->
                <div class="space-y-3 mb-8">
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 uppercase tracking-tight leading-none">
                        SEDANG<br>ISTIRAHAT
                    </h2>
                    <div class="h-1 w-20 bg-orange-500 mx-auto rounded-full"></div>
                    <p class="text-slate-500 font-bold tracking-[0.2em] uppercase text-xs mt-3">Mohon menunggu sebentar</p>
                </div>

                <!-- Timer Box -->
                <div class="relative px-20 py-8 bg-slate-50 rounded-3xl border border-slate-200 shadow-sm">

                    <span class="text-xs font-black text-slate-400 uppercase tracking-[0.5em] block mb-3">Sisa Waktu</span>
                    <span id="breakTimer" class="text-5xl font-black text-slate-800 font-mono tracking-tighter drop-shadow-sm">
                        --:--:--
                    </span>
                    <script>
                        (function() {
                            const endTimeStr = "<?= $breakData['end_time'] ?>";
                            // Parse standard SQL datetime to JS Date safely
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

                <!-- Progress Bar -->
                <div class="w-full max-w-xs bg-slate-200 h-2 rounded-full mt-12 overflow-hidden border border-slate-300">
                    <div class="h-full bg-orange-500 animate-[loading_60s_linear_infinite] shadow-[0_0_15px_rgba(249,115,22,0.6)]"></div>
                </div>
            </div>
        </div>
        <style>
            @keyframes loading {
                from {
                    width: 0%;
                }

                to {
                    width: 100%;
                }
            }
        </style>
    <?php endif; ?>

    <!-- 3 COLUMNS -->
    <div id="queue-columns" class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-[40vh]">

        <!-- KOLOM MENUNGGU -->
        <div class="dark-column rounded-xl overflow-hidden flex flex-col">
            <div class="py-4 text-center border-b border-orange-500/30">
                <h2 class="text-lg font-black text-orange-400 uppercase tracking-[0.2em] glow-text-orange">Menunggu</h2>
            </div>
            <div class="flex-1 overflow-y-hidden p-4 space-y-3 auto-scroll-list no-scrollbar" style="min-height: 0;" data-count="<?= count($waitingList) ?>">
                <?php if (!empty($waitingList)): ?>
                    <?php foreach ($waitingList as $i => $q): ?>
                        <div class="list-card-orange rounded-xl p-3 flex items-center gap-4">
                            <div class="h-12 w-12 shrink-0 flex items-center justify-center rounded-full border-2 border-orange-400/80 bg-orange-900/20">
                                <span class="text-2xl font-black text-orange-400"><?= esc($q->queue_number ?? '-') ?></span>
                            </div>
                            <div class="flex-1 min-w-0 flex items-center gap-2">
                                <p class="text-lg font-bold text-slate-100 uppercase tracking-wider truncate"><?= esc($q->patient_name ?? '-') ?></p>
                                <i class="fas fa-user text-orange-400/50 text-xs"></i>
                            </div>
                            <div class="text-right pl-2">
                                <span class="text-sm font-bold text-slate-300">#<?= $i + 1 ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center">
                        <p class="text-orange-400/50 font-black text-3xl tracking-[0.3em] uppercase glow-text-orange" style="font-family: serif;">KOSONG</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KOLOM SEDANG TERAPI -->
        <div class="dark-column rounded-xl overflow-hidden flex flex-col">
            <div class="py-4 text-center border-b border-cyan-500/30">
                <h2 class="text-lg font-black text-cyan-400 uppercase tracking-[0.2em] glow-text-blue">Sedang Terapi</h2>
            </div>
            <div class="flex-1 overflow-y-hidden p-4 space-y-3 auto-scroll-list no-scrollbar" style="min-height: 0;" data-count="<?= count($processingList) ?>">
                <?php if (!empty($processingList)): ?>
                    <?php foreach ($processingList as $i => $q): ?>
                        <div class="list-card-blue rounded-xl p-3 flex items-center gap-4 relative overflow-hidden">
                            <!-- Animated Loading Bar -->
                            <div class="absolute bottom-2 left-4 right-4 h-1 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-400 w-1/3 animate-[translateX_2s_infinite] glow-blue"></div>
                            </div>
                            <div class="h-12 w-12 shrink-0 flex items-center justify-center rounded-full border-2 border-cyan-400/80 bg-cyan-900/20 mb-2">
                                <span class="text-2xl font-black text-cyan-400"><?= esc($q->queue_number ?? '-') ?></span>
                            </div>
                            <div class="flex-1 min-w-0 flex items-center gap-2 mb-2">
                                <p class="text-lg font-bold text-slate-100 uppercase tracking-wider truncate"><?= esc($q->patient_name ?? '-') ?></p>
                                <i class="fas fa-user-md text-cyan-400/50 text-xs"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center">
                        <p class="text-cyan-400/50 font-black text-3xl tracking-[0.3em] uppercase glow-text-blue" style="font-family: serif;">KOSONG</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KOLOM SELESAI -->
        <div class="dark-column rounded-xl overflow-hidden flex flex-col">
            <div class="py-4 text-center border-b border-emerald-500/30">
                <h2 class="text-lg font-black text-emerald-400 uppercase tracking-[0.2em] glow-text-green">Selesai</h2>
            </div>
            <div class="flex-1 overflow-y-hidden p-4 space-y-3 auto-scroll-list no-scrollbar" style="min-height: 0;" data-count="<?= count($finishedList) ?>">
                <?php if (!empty($finishedList)): ?>
                    <?php foreach ($finishedList as $i => $q): ?>
                        <div class="list-card-green rounded-xl p-3 flex items-center gap-4 opacity-60">
                            <div class="h-12 w-12 shrink-0 flex items-center justify-center rounded-full border-2 border-emerald-400/80 bg-emerald-900/20">
                                <span class="text-2xl font-black text-emerald-400"><?= esc($q->queue_number ?? '-') ?></span>
                            </div>
                            <div class="flex-1 min-w-0 flex items-center gap-2">
                                <p class="text-lg font-bold text-slate-300 uppercase tracking-wider truncate line-through decoration-emerald-500/50"><?= esc($q->patient_name ?? '-') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center">
                        <p class="text-emerald-400/50 font-black text-3xl tracking-[0.3em] uppercase glow-text-green" style="font-family: serif;">KOSONG</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- MARQUEE BOTTOM BAR -->
    <?php if (isset($isPublic) && $isPublic): ?>
        <div class="dark-card border border-slate-700/50 py-3 px-6 rounded-xl mt-auto flex items-center overflow-hidden">
            <marquee behavior="scroll" direction="left" scrollamount="6" class="text-sm md:text-base font-semibold text-slate-300 w-full whitespace-nowrap">
                <span class="text-cyan-400 font-black tracking-widest">PURBALINGGA:</span> RT 3/RW 2, Dusun Parung Bongas, Desa Kradenan &nbsp;&nbsp;&nbsp;&nbsp; <span class="text-slate-600">|</span> &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="text-cyan-400 font-black tracking-widest">PEMALANG:</span> Jl. Banjarsari, Bojongnangka &nbsp;&nbsp;&nbsp;&nbsp; <span class="text-slate-600">|</span> &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="text-cyan-400 font-black tracking-widest">PURWOKERTO:</span> Jl. Dr. Gumbreg, Kel. Mersi, Kec. Purwokerto Timur
            </marquee>
        </div>
    <?php endif; ?>

    <script>
        // --- LIVE TIME & DATE ---
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
                });
            }
            if (dateEl) {
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                dateEl.textContent = now.toLocaleDateString('id-ID', options);
            }
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // --- AUTO RELOAD (60 SECONDS) ---
        setTimeout(() => {
            window.location.reload();
        }, 60000);

        // --- AUTO SCROLL ANIMATION ---
        function initAutoScroll() {
            const containers = document.querySelectorAll('.auto-scroll-list');
            containers.forEach(container => {
                if (container.getAttribute('data-initialized')) return;
                
                const count = parseInt(container.getAttribute('data-count') || '0');
                if (count > 5) {
                    container.setAttribute('data-initialized', 'true');
                    
                    // Buat wrapper untuk animasi transform (lebih pakem daripada scrollTop)
                    const wrapper = document.createElement('div');
                    wrapper.className = 'scroll-wrapper space-y-3'; // Pindahkan space-y-3 ke sini
                    
                    // Pindahkan semua isi asli ke dalam wrapper
                    while (container.firstChild) {
                        wrapper.appendChild(container.firstChild);
                    }
                    
                    // Clone isi asli untuk efek infinite
                    const originalChildren = Array.from(wrapper.children);
                    originalChildren.forEach(child => {
                        wrapper.appendChild(child.cloneNode(true));
                    });
                    
                    container.appendChild(wrapper);
                    container.style.overflow = 'hidden'; // Pastikan terkunci

                    let pos = 0;
                    const speed = 1.0; // Kecepatan ideal (1 pixel per frame)

                    function scroll() {
                        pos -= speed;
                        
                        // Jika sudah bergeser sejauh tinggi konten asli, reset ke 0
                        // Gunakan offsetHeight untuk akurasi layout
                        const halfHeight = wrapper.offsetHeight / 2;
                        if (Math.abs(pos) >= halfHeight) {
                            pos = 0;
                        }
                        
                        wrapper.style.transform = `translateY(${pos}px)`;
                        requestAnimationFrame(scroll);
                    }

                    // Jeda sebentar agar browser selesai kalkulasi layout
                    setTimeout(() => {
                        requestAnimationFrame(scroll);
                    }, 1000);
                }
            });
        }

        // Jalankan saat DOM siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAutoScroll);
        } else {
            initAutoScroll();
        }
    </script>
</body>

</html>