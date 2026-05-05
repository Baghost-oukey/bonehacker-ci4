<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<div class="p-4 sm:p-6 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight"><?= $title ?></h1>
            <p class="text-sm text-slate-500 mt-1">Pantau riwayat pengiriman pesan WhatsApp dan kirim ulang jika ada yang gagal.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row gap-6 justify-between items-end">
            <div class="w-full md:w-auto flex-1 max-w-lg">
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Filter Rentang Waktu</label>
                <div class="flex items-center gap-2">
                    <input type="date" id="startDate" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white outline-none shadow-sm">
                    <span class="text-slate-400 font-bold text-[10px] uppercase">s/d</span>
                    <input type="date" id="endDate" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white outline-none shadow-sm">
                </div>
            </div>
            
            <div id="search-container" class="relative w-full md:w-80 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-20">
                    <i class="fas fa-search text-slate-400 group-focus-within:text-teal-600 transition-colors text-sm"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table id="whatsappLogs" class="w-full text-left border-collapse whitespace-nowrap min-w-300">
                <thead>
                    <tr class="bg-white border-b border-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">History ID</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Message</th>
                        <th class="px-6 py-4 text-center">Status Pesan</th>
                        <th class="hidden">Status Value</th> 
                        <th class="px-6 py-4">Time Sent</th>
                        <th class="px-6 py-4">Created At</th>
                        <th class="px-6 py-4">Updated At</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($logs as $log): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm font-mono text-slate-500"><?= $log->id ?></td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400"><?= $log->history_id ?></td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-700"><?= $log->name ?></td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-600">
                            <?php 
                                // Logika format nomor telepon asli dipertahankan
                                echo (isset($log->phone[1]) && $log->phone[0] === '6' && $log->phone[1] === '2') 
                                     ? '0' . substr($log->phone, 2) 
                                     : $log->phone; 
                            ?>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate" title="<?= htmlspecialchars($log->message) ?>">
                            <?= $log->message ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($log->is_sent): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black tracking-wider bg-teal-100 text-teal-700 border border-teal-200">
                                    BERHASIL
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black tracking-wider bg-red-100 text-red-700 border border-red-200">
                                    GAGAL
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="hidden"><?= $log->is_sent ? 1 : 0 ?></td>
                        <td class="px-6 py-4 text-xs text-slate-500"><?= $log->time_sent ?: '-' ?></td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-500"><?= date('Y-m-d H:i', strtotime($log->created_at)) ?></td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-500"><?= date('Y-m-d H:i', strtotime($log->updated_at)) ?></td>
                        <td class="px-6 py-4 text-right">
                            <?php if(!$log->is_sent): ?>
                                <form action="<?= base_url('whatsapp/log_whatsapp/resend') ?>" method="POST" class="m-0 inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="log_id" value="<?= $log->id ?>">
                                    <button type="submit" class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-100 text-orange-700 hover:bg-orange-200 border border-orange-200 text-xs font-bold transition-all outline-none shadow-sm active:scale-95">
                                        <i class="fas fa-paper-plane"></i> Resend
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-slate-300 text-xl font-bold">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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