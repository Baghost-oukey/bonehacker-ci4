<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="monitoringPage" class="w-full space-y-6 py-4 md:py-6">
    <!-- Header Page -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                Daftar Antrean
            </h1>
            <p class="text-sm text-slate-500">
                Pilih Wilayah / Cabang
            </p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="date" id="filterDate"
                    class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2.5 text-sm text-slate-700 font-bold focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/10 transition"
                    value="<?= esc($date) ?>">
            </div>
        </div>
    </div>

    <!-- Summary Stats Card -->
    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-6">
        <div class="text-center md:text-left">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">Ringkasan Total Antrean</h3>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <!-- Menunggu -->
            <div class="flex flex-col items-center justify-center p-2 text-center">
                <span id="sumWaiting" class="text-4xl md:text-5xl font-black text-amber-500 transition-all duration-300"><?= $totalWaiting ?></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-2">Menunggu</span>
            </div>

            <!-- Terapi -->
            <div class="flex flex-col items-center justify-center p-2 pt-6 md:pt-2 text-center cursor-pointer hover:bg-sky-50/50 hover:shadow-inner rounded-3xl transition-all duration-200"
                 onclick="showGlobalActivePatients()"
                 title="Klik untuk melihat semua pasien yang sedang diterapi">
                <span id="sumProcessing" class="text-4xl md:text-5xl font-black text-sky-500 transition-all duration-300"><?= $totalProcessing ?></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-2 flex items-center gap-1 justify-center">
                    Terapi <i class="fas fa-external-link-alt text-[8px] text-slate-350"></i>
                </span>
            </div>

            <!-- Selesai -->
            <div class="flex flex-col items-center justify-center p-2 pt-6 md:pt-2 text-center">
                <span id="sumFinished" class="text-4xl md:text-5xl font-black text-emerald-500 transition-all duration-300"><?= $totalFinished ?></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-2">Selesai</span>
            </div>

            <!-- Total -->
            <div class="flex flex-col items-center justify-center p-2 pt-6 md:pt-2 text-center">
                <span id="sumAll" class="text-4xl md:text-5xl font-black text-slate-800 transition-all duration-300"><?= $totalAll ?></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-2">Total Antrean</span>
            </div>
        </div>
    </div>

    <!-- Search Input for Branches -->
    <div class="relative w-full">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
        <input type="text" id="branchSearchInput" placeholder="Cari cabang / wilayah..."
            class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3.5 text-sm font-semibold text-slate-700 bg-white placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/10 transition shadow-sm">
    </div>

    <!-- Branches Grid -->
    <div id="branchesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($branches as $branch): ?>
            <div class="branch-card bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" 
                 data-name="<?= esc(strtolower($branch->name)) ?>">
                
                <!-- Card Header -->
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <a href="<?= site_url('antrean?region=' . $branch->id) ?>" 
                       class="font-extrabold text-slate-800 hover:text-teal-600 transition-colors tracking-tight text-base uppercase">
                        <?= esc($branch->name) ?>
                    </a>
                    <button type="button" 
                            class="text-slate-400 hover:text-teal-600 transition-colors p-1 focus:outline-none cursor-pointer"
                            onclick="toggleActivePatients('<?= $branch->id ?>')"
                            title="Tampilkan antrean cabang ini">
                        <i class="fas fa-chevron-right text-xs transition-transform duration-200" id="icon-active-<?= $branch->id ?>"></i>
                    </button>
                </div>

                <!-- Card Body (Stats row matching the image) -->
                <div class="p-4 grid grid-cols-4 gap-2 text-center items-center">
                    <!-- Menunggu -->
                    <div class="flex flex-col items-center justify-center py-1">
                        <span class="branch-waiting text-2xl font-black text-amber-500"><?= $branch->waiting ?></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1.5">Menunggu</span>
                    </div>

                    <!-- Terapi -->
                    <div class="flex flex-col items-center justify-center py-1">
                        <span class="branch-processing text-2xl font-black text-sky-500"><?= $branch->processing ?></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1.5">Terapi</span>
                    </div>

                    <!-- Selesai -->
                    <div class="flex flex-col items-center justify-center py-1">
                        <span class="branch-finished text-2xl font-black text-emerald-500"><?= $branch->finished ?></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1.5">Selesai</span>
                    </div>

                    <!-- Total (Styled with light grey background pill inside a column) -->
                    <div class="flex flex-col items-center justify-center bg-slate-50 border border-slate-100 rounded-2xl py-2 px-1">
                        <span class="branch-total text-2xl font-black text-slate-800"><?= $branch->total ?></span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-1">Total</span>
                    </div>
                </div>

                <!-- Active Patients Dropdown/Collapsible list -->
                <div class="active-patients-panel hidden border-t border-slate-100 bg-slate-50/50 px-5 py-4 transition-all duration-300" id="panel-active-<?= $branch->id ?>">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                        <span>Pasien di Antrean (<span class="panel-count"><?= count($branch->active_patients ?? []) ?></span>)</span>
                    </div>
                    <ul class="panel-list space-y-1.5">
                        <?php if (empty($branch->active_patients)): ?>
                            <li class="text-xs text-slate-400 italic py-1">Tidak ada pasien di antrean.</li>
                        <?php else: ?>
                            <?php foreach ($branch->active_patients as $p): ?>
                                <?php 
                                    $isProcessing = !empty($p['process_at']);
                                    $statusText = $isProcessing ? 'Terapi' : 'Menunggu';
                                    $statusClass = $isProcessing 
                                        ? 'bg-sky-50 text-sky-600 ring-sky-600/20' 
                                        : 'bg-amber-50 text-amber-600 ring-amber-600/20';
                                ?>
                                <li class="flex items-center justify-between bg-white px-3.5 py-2.5 rounded-xl border border-slate-100 shadow-sm text-xs font-semibold text-slate-700">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i class="fas fa-user-md text-slate-400 text-xs shrink-0"></i>
                                        <span class="truncate"><?= esc($p['patient_name']) ?></span>
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-medium <?= $statusClass ?> ring-1 ring-inset ml-1 shrink-0">
                                            <?= $statusText ?>
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 shrink-0">
                                        No. <?= esc($p['queue_number']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Not Found Area -->
    <div id="noResults" class="hidden text-center py-12 bg-white rounded-3xl border border-slate-200/50">
        <i class="fas fa-search text-slate-300 text-4xl mb-3"></i>
        <p class="text-slate-500 font-bold">Cabang tidak ditemukan</p>
    </div>

    <!-- Footer updated time -->
    <div class="flex items-center justify-between text-xs text-slate-400 px-2">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Auto update diaktifkan</span>
        </div>
        <div>
            Terakhir diperbarui: <span id="lastUpdatedTime" class="font-mono font-bold">--:--:--</span>
        </div>
    </div>
    <!-- Modal Active Patients Global -->
    <div id="modalActivePatientsGlobal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[75vh] transform scale-95 transition-all duration-300">
            <!-- Header -->
            <div class="px-6 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                    <h3 class="text-sm font-extrabold text-slate-800 tracking-tight">Pasien Sedang Diterapi</h3>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition" onclick="closeActivePatientsGlobalModal()">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5" id="modalActivePatientsGlobalBody">
                <!-- List populated dynamically via JS -->
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const searchInput = document.getElementById("branchSearchInput");
        const branchCards = document.querySelectorAll(".branch-card");
        const noResults = document.getElementById("noResults");
        const dateInput = document.getElementById("filterDate");
        const lastUpdatedEl = document.getElementById("lastUpdatedTime");

        // Stat elements
        const sumWaiting = document.getElementById("sumWaiting");
        const sumProcessing = document.getElementById("sumProcessing");
        const sumFinished = document.getElementById("sumFinished");
        const sumAll = document.getElementById("sumAll");

        let refreshInterval = null;

        // Set initial update time
        updateTimestamp();

        // 1. Client-side search filtering
        searchInput.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            branchCards.forEach(card => {
                const name = card.getAttribute("data-name");
                if (name.includes(query)) {
                    card.classList.remove("hidden");
                    visibleCount++;
                } else {
                    card.classList.add("hidden");
                }
            });

            if (visibleCount === 0) {
                noResults.classList.remove("hidden");
            } else {
                noResults.classList.add("hidden");
            }
        });

        // 2. Date Filter
        dateInput.addEventListener("change", () => {
            const selectedDate = dateInput.value;
            // Redirect to update page context
            window.location.href = `<?= site_url('antrean/monitoring') ?>?date=${selectedDate}`;
        });

        let currentBranchesData = <?= json_encode($branches) ?>;

        // Toggle collapsible active patients list
        window.toggleActivePatients = (branchId) => {
            const panel = document.getElementById(`panel-active-${branchId}`);
            const icon = document.getElementById(`icon-active-${branchId}`);
            if (panel) {
                panel.classList.toggle('hidden');
                if (icon) {
                    icon.classList.toggle('rotate-90');
                }
            }
        };

        // Show global active patients in modal
        window.showGlobalActivePatients = () => {
            const modal = document.getElementById('modalActivePatientsGlobal');
            const body = document.getElementById('modalActivePatientsGlobalBody');
            if (!modal || !body) return;

            body.innerHTML = '';
            let totalActive = 0;

            currentBranchesData.forEach(branch => {
                if (branch.active_patients && branch.active_patients.length > 0) {
                    totalActive += branch.active_patients.length;
                    
                    let branchHtml = `
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">${branch.name}</h4>
                            <ul class="space-y-1.5">
                    `;

                    branch.active_patients.forEach(p => {
                        const isProcessing = !!p.process_at;
                        const statusText = isProcessing ? 'Terapi' : 'Menunggu';
                        const statusClass = isProcessing 
                            ? 'bg-sky-50 text-sky-600 ring-sky-600/20' 
                            : 'bg-amber-50 text-amber-600 ring-amber-600/20';

                        branchHtml += `
                            <li class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-700">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i class="fas fa-user-md text-slate-400 shrink-0"></i>
                                    <span class="truncate">${p.patient_name}</span>
                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-medium ${statusClass} ring-1 ring-inset ml-1 shrink-0">
                                        ${statusText}
                                    </span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded bg-white border border-slate-150 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 shrink-0">
                                    No. ${p.queue_number}
                                </span>
                            </li>
                        `;
                    });

                    branchHtml += `
                            </ul>
                        </div>
                    `;

                    body.innerHTML += branchHtml;
                }
            });

            if (totalActive === 0) {
                body.innerHTML = `
                    <div class="text-center py-8 text-slate-400 italic text-sm">
                        Tidak ada pasien di antrean aktif di semua cabang.
                    </div>
                `;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.closeActivePatientsGlobalModal = () => {
            const modal = document.getElementById('modalActivePatientsGlobal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        // 3. AJAX Live Refresh
        function updateStats() {
            const selectedDate = dateInput.value;
            $.ajax({
                url: `<?= site_url('antrean/monitoring') ?>?date=${selectedDate}`,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    if (data) {
                        currentBranchesData = data.branches;

                        // Update total headers
                        animateNumber(sumWaiting, data.totalWaiting);
                        animateNumber(sumProcessing, data.totalProcessing);
                        animateNumber(sumFinished, data.totalFinished);
                        animateNumber(sumAll, data.totalAll);

                        // Update individual branch cards
                        if (data.branches && data.branches.length > 0) {
                            data.branches.forEach(branch => {
                                const card = document.querySelector(`.branch-card[data-name="${branch.name.toLowerCase()}"]`);
                                if (card) {
                                    card.querySelector('.branch-waiting').textContent = branch.waiting;
                                    card.querySelector('.branch-processing').textContent = branch.processing;
                                    card.querySelector('.branch-finished').textContent = branch.finished;
                                    card.querySelector('.branch-total').textContent = branch.total;

                                    // Update active patients list
                                    const panel = document.getElementById(`panel-active-${branch.id}`);
                                    if (panel) {
                                        panel.querySelector('.panel-count').textContent = branch.active_patients ? branch.active_patients.length : 0;
                                        const list = panel.querySelector('.panel-list');
                                        list.innerHTML = '';
                                        if (!branch.active_patients || branch.active_patients.length === 0) {
                                            list.innerHTML = '<li class="text-xs text-slate-400 italic py-1">Tidak ada pasien di antrean.</li>';
                                        } else {
                                            branch.active_patients.forEach(p => {
                                                const isProcessing = !!p.process_at;
                                                const statusText = isProcessing ? 'Terapi' : 'Menunggu';
                                                const statusClass = isProcessing 
                                                    ? 'bg-sky-50 text-sky-600 ring-sky-600/20' 
                                                    : 'bg-amber-50 text-amber-600 ring-amber-600/20';

                                                list.innerHTML += `
                                                    <li class="flex items-center justify-between bg-white px-3.5 py-2.5 rounded-xl border border-slate-100 shadow-sm text-xs font-semibold text-slate-700">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <i class="fas fa-user-md text-slate-400 text-xs shrink-0"></i>
                                                            <span class="truncate">${p.patient_name}</span>
                                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-medium ${statusClass} ring-1 ring-inset ml-1 shrink-0">
                                                                ${statusText}
                                                            </span>
                                                        </div>
                                                        <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 shrink-0">
                                                            No. ${p.queue_number}
                                                        </span>
                                                    </li>
                                                `;
                                            });
                                        }
                                    }
                                }
                            });
                        }
                        updateTimestamp();
                    }
                },
                error: function(err) {
                    console.error("Gagal memperbarui data monitoring antrean:", err);
                }
            });
        }

        function updateTimestamp() {
            const now = new Date();
            lastUpdatedEl.textContent = now.toTimeString().split(' ')[0];
        }

        function animateNumber(element, targetVal) {
            const currentVal = parseInt(element.textContent) || 0;
            if (currentVal === targetVal) return;
            
            // Simple direct animation
            element.textContent = targetVal;
            element.classList.add("scale-110", "text-teal-600");
            setTimeout(() => {
                element.classList.remove("scale-110", "text-teal-600");
            }, 300);
        }

        // Setup polling loop
        function startPolling() {
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(updateStats, 10000); // 10 seconds interval
        }

        // Initialize polling on page load automatically
        startPolling();
    });
</script>
<?= $this->endSection() ?>
