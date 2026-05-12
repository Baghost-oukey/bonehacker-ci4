<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<style>
    /* Paksa container agar tidak bisa melar keluar layar */
    #logsPage {
        width: 100% !important;
        max-width: 100vw !important;
        margin: 0 auto !important;
        padding: 0 !important;
        overflow-x: hidden !important;
    }
    
    .log-container {
        background-color: #0f172a !important;
        border-radius: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important; /* Mencegah kotak hitamnya melar */
        border: 1px solid #1e293b;
    }

    .log-scroll-area {
        width: 100% !important;
        overflow-x: auto !important; /* Hanya area ini yang bisa scroll */
        -webkit-overflow-scrolling: touch;
    }

    .log-text {
        display: inline-block;
        min-width: 100%;
        padding: 15px;
        font-family: monospace;
        font-size: 10px;
        line-height: 1.5;
        color: #cbd5e1;
        white-space: pre !important; /* Menjaga teks tetap memanjang agar bisa di-scroll */
        margin: 0;
    }

    /* Scrollbar minimalis */
    .log-scroll-area::-webkit-scrollbar {
        height: 4px;
    }
    .log-scroll-area::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 10px;
    }
</style>

<div id="logsPage" class="space-y-4">
    
    <!-- HEADER -->
    <div class="px-2">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">System Logs</h1>
        <p class="text-[10px] font-bold text-slate-400 uppercase">Periode: <?= esc($date) ?></p>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden mt-4">
            <select onchange="window.location.href=this.value" class="w-full bg-slate-50 border border-slate-100 text-slate-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-500/20">
                <option value="<?= site_url('logs') ?>" selected>Logs</option>
                <option value="<?= site_url('whatsapp') ?>">WhatsApp</option>
                <option value="<?= site_url('log_whatsapp') ?>">Log WhatsApp</option>
                <option value="<?= site_url('jabatan') ?>">Jabatan</option>
                <option value="<?= site_url('greeting') ?>">Greetings</option>
            </select>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-4 mx-2">
        
        <!-- FILTER -->
        <div class="space-y-1">
            <label class="text-[10px] font-bold uppercase text-slate-400 ml-1">Filter Tanggal</label>
            <form action="<?= base_url('logs'); ?>" method="get" class="m-0">
                <input type="date" name="date" 
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-teal-500 transition-all" 
                       value="<?= $date; ?>"
                       onchange="this.form.submit()">
            </form>
        </div>

        <!-- NAVIGATION -->
        <div class="flex flex-col gap-2">
            <?php 
                $prevDay = date('Y-m-d', strtotime('-1 day', strtotime($date)));
                $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($date)));
            ?>
            <a href="<?= base_url('logs?date=' . $prevDay); ?>" 
               class="w-full py-3.5 bg-slate-900 rounded-xl text-center shadow-sm active:scale-95 transition-all flex items-center justify-center">
                <span style="color: #ffffff !important; font-weight: 800 !important; font-size: 11px !important; text-transform: uppercase !important;">
                    &larr; Laporan Sebelumnya
                </span>
            </a>

            <a href="<?= base_url('logs?date=' . $nextDay); ?>" 
               class="w-full py-3.5 bg-slate-900 rounded-xl text-center shadow-sm active:scale-95 transition-all flex items-center justify-center">
                <span style="color: #ffffff !important; font-weight: 800 !important; font-size: 11px !important; text-transform: uppercase !important;">
                    Laporan Selanjutnya &rarr;
                </span>
            </a>
        </div>

        <!-- THE LOG BOX -->
        <div class="log-container">
            <?php if (empty(trim($log_content))): ?>
                <div class="p-10 text-center bg-white rounded-xl">
                    <p class="text-[11px] font-bold text-slate-300">TIDAK ADA LOG</p>
                </div>
            <?php else: ?>
                <div class="log-scroll-area">
                    <pre class="log-text"><?= htmlspecialchars($log_content); ?></pre>
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
<?= $this->endSection() ?>