<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<div class="p-4 sm:p-6 md:p-8 max-w-350 mx-auto">
    
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">System Logs</h1>
        <p class="text-sm text-slate-500 mt-1">Pantau dan analisis log aktivitas sistem pada tanggal <span class="font-semibold text-slate-700"><?= esc($date) ?></span>.</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col overflow-hidden transition-all hover:shadow-md">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 p-4 gap-4 bg-slate-50/80">
            
            <form id="logSearchForm" action="<?= base_url('logs'); ?>" method="get" class="w-full sm:w-auto m-0">
                <div class="flex items-center gap-3">
                    <label for="log_date" class="text-sm font-semibold text-slate-600 whitespace-nowrap">Pilih Tanggal:</label>
                    <input type="date" name="date" id="log_date" 
                           class="flex h-9 w-full sm:w-50 cursor-pointer rounded-md border border-slate-200 bg-white px-3 py-1 text-sm text-slate-900 shadow-sm transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" 
                           value="<?= $date; ?>">
                </div>
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <?php 
                    $prevDay = date('Y-m-d', strtotime('-1 day', strtotime($date)));
                    $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                ?>
                <a href="<?= base_url('logs?date=' . $prevDay); ?>" 
                   class="inline-flex h-9 flex-1 sm:flex-none items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                    <i class="fas fa-chevron-left mr-2 text-[10px]"></i> Sebelumnya
                </a>
                <a href="<?= base_url('logs?date=' . $nextDay); ?>" 
                   class="inline-flex h-9 flex-1 sm:flex-none items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                    Selanjutnya <i class="fas fa-chevron-right ml-2 text-[10px]"></i>
                </a>
            </div>

        </div>

        <div class="p-0">
            <?php if (empty(trim($log_content))): ?>
                <div class="flex flex-col items-center justify-center py-16 text-center bg-white">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 border border-slate-100 mb-4">
                        <i class="fas fa-check-circle text-emerald-500 text-3xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-900 tracking-tight">Sistem Bersih</p>
                    <p class="text-sm text-slate-500 mt-1">Tidak ada log atau *error* yang tercatat pada tanggal ini.</p>
                </div>
            <?php else: ?>
                <div class="bg-[#0f172a] p-4 sm:p-6 overflow-x-auto max-h-[70vh] custom-scrollbar">
                    <pre class="font-mono text-[13px] text-slate-300 leading-relaxed whitespace-pre-wrap break-words m-0"><?= htmlspecialchars($log_content); ?></pre>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.logConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>
<!-- <script src="<?= base_url('js/pages/system-logs.js') ?>"></script> -->
<?= $this->endSection() ?>