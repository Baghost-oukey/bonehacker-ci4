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
            <div class="flex flex-col items-center justify-center p-2 pt-6 md:pt-2 text-center">
                <span id="sumProcessing" class="text-4xl md:text-5xl font-black text-sky-500 transition-all duration-300"><?= $totalProcessing ?></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-2">Terapi</span>
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
                    <span class="font-extrabold text-slate-800 tracking-tight text-base uppercase">
                        <?= esc($branch->name) ?>
                    </span>
                    <a href="<?= site_url('antrean?region=' . $branch->id) ?>" 
                       class="text-slate-400 hover:text-teal-600 transition p-1"
                       title="Kelola antrean cabang ini">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
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

        // 3. AJAX Live Refresh
        function updateStats() {
            const selectedDate = dateInput.value;
            $.ajax({
                url: `<?= site_url('antrean/monitoring') ?>?date=${selectedDate}`,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    if (data) {
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
