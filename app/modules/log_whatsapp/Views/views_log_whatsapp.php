<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<div class="px-2 py-4 md:px-6 md:py-8 space-y-6">
    <!-- HEADER -->
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight"><?= $title ?></h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Pantau riwayat pengiriman pesan WhatsApp secara real-time.</p>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        
        <!-- FILTER BAR -->
        <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
            <div class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
                <div class="w-full md:flex-1 max-w-xl space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rentang Waktu Laporan</label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative w-full">
                            <input type="date" id="startDate" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all bg-white outline-none shadow-inner">
                        </div>
                        <span class="text-slate-300 font-black text-[9px] uppercase hidden sm:block">SAMPAI</span>
                        <div class="relative w-full">
                            <input type="date" id="endDate" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all bg-white outline-none shadow-inner">
                        </div>
                    </div>
                </div>
                
                <div id="search-container" class="relative w-full md:w-80 group">
                    <!-- Search input from DataTables will be here -->
                </div>
            </div>
        </div>

        <!-- DESKTOP VIEW (TABLE) -->
        <div class="hidden md:block overflow-x-auto w-full">
            <table id="whatsappLogs" class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-white border-b border-slate-50 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-5">ID</th>
                        <th class="px-6 py-5">Penerima</th>
                        <th class="px-6 py-5">Pesan</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="hidden">Status Val</th> 
                        <th class="px-6 py-5">Waktu Terkirim</th>
                        <th class="px-6 py-5">Dibuat</th>
                        <th class="px-6 py-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach($logs as $log): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4 text-xs font-mono text-slate-400 italic">#<?= $log->id ?></td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800"><?= esc($log->name) ?></span>
                                <span class="text-[11px] font-mono font-medium text-slate-500">
                                    <?php 
                                        echo (isset($log->phone[1]) && $log->phone[0] === '6' && $log->phone[1] === '2') 
                                             ? '0' . substr($log->phone, 2) 
                                             : $log->phone; 
                                    ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-slate-500 max-w-xs truncate font-medium" title="<?= esc($log->message) ?>">
                                <?= esc($log->message) ?>
                            </p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($log->is_sent): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black tracking-widest bg-teal-100 text-teal-700 border border-teal-200">
                                    BERHASIL
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black tracking-widest bg-red-100 text-red-700 border border-red-200">
                                    GAGAL
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="hidden"><?= $log->is_sent ? 1 : 0 ?></td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-medium"><?= $log->time_sent ?: '<span class="opacity-30">-</span>' ?></td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400"><?= date('d/m/y H:i', strtotime($log->created_at)) ?></td>
                        <td class="px-6 py-4 text-right">
                            <?php if(!$log->is_sent): ?>
                                <form action="<?= base_url('whatsapp/log_whatsapp/resend') ?>" method="POST" class="m-0 inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="log_id" value="<?= $log->id ?>">
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white transition-all shadow-sm active:scale-90">
                                        <i class="fas fa-redo-alt text-[10px]"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="w-8 h-8 flex items-center justify-center mx-auto opacity-20">
                                    <i class="fas fa-check-circle text-teal-500"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBILE VIEW (CARDS) -->
        <div class="md:hidden divide-y divide-slate-100">
            <?php foreach($logs as $log): ?>
                <div class="p-5 space-y-4 hover:bg-slate-50/50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase italic">#<?= $log->id ?></span>
                            <h3 class="text-sm font-black text-slate-800 leading-tight"><?= esc($log->name) ?></h3>
                            <p class="text-[11px] font-mono font-bold text-slate-500">
                                <?php 
                                    echo (isset($log->phone[1]) && $log->phone[0] === '6' && $log->phone[1] === '2') 
                                         ? '0' . substr($log->phone, 2) 
                                         : $log->phone; 
                                ?>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <?php if($log->is_sent): ?>
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black bg-teal-100 text-teal-700 border border-teal-200">SENT</span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black bg-red-100 text-red-700 border border-red-200">FAILED</span>
                            <?php endif; ?>
                            <span class="text-[9px] font-mono text-slate-400"><?= date('d/m H:i', strtotime($log->created_at)) ?></span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <p class="text-xs font-medium text-slate-600 line-clamp-3 italic">"<?= esc($log->message) ?>"</p>
                    </div>

                    <?php if(!$log->is_sent): ?>
                        <form action="<?= base_url('whatsapp/log_whatsapp/resend') ?>" method="POST" class="m-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="log_id" value="<?= $log->id ?>">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl bg-orange-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-orange-500/25 active:scale-95 transition-all">
                                <i class="fas fa-paper-plane text-[10px]"></i>
                                Kirim Ulang
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if(empty($logs)): ?>
                <div class="py-20 text-center opacity-30">
                    <i class="fas fa-history text-4xl text-slate-300 mb-3"></i>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Riwayat Kosong</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.waLogConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        resendUrl: "<?= base_url('whatsapp/log_whatsapp/resend') ?>"
    };
</script>
<?= $this->endSection() ?>