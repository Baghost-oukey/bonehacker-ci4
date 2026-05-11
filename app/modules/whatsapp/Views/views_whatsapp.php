<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="whatsappPage" class="w-full px-2 py-4 md:px-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-900">WhatsApp API</h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Konfigurasi integrasi pesan WhatsApp otomatis.</p>
        </div>
        <button id="btn-add-wa" type="button" class="w-full md:w-auto flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black hover:bg-teal-700 transition-all shadow-lg shadow-teal-500/25 active:scale-[0.98] outline-none">
            <i class="fas fa-plus text-xs"></i> 
            <span class="uppercase tracking-widest">Tambah Data</span>
        </button>
    </div>

    <!-- TABLE / CARD CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-800">Daftar Konfigurasi</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total: <?= count($records) ?> Terdaftar</p>
                </div>
            </div>
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden md:block overflow-x-auto w-full">
            <table class="w-full text-left border-collapse" id="table-wa">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-5 whitespace-nowrap">ID</th>
                        <th class="px-6 py-5 whitespace-nowrap">URL API</th>
                        <th class="px-6 py-5 whitespace-nowrap">Instance ID</th>
                        <th class="px-6 py-5 whitespace-nowrap">Token</th>
                        <th class="px-6 py-5 whitespace-nowrap">Message</th>
                        <th class="px-6 py-5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $record): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 text-sm text-slate-500 font-mono"><?= esc($record->id) ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-700"><?= esc($record->url_api) ?></td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-mono text-slate-500 bg-slate-50 rounded-lg inline-block px-2 py-1">
                                        <?= esc(substr($record->instance_id, 0, 4)) . '****' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-mono text-slate-500 bg-slate-50 rounded-lg inline-block px-2 py-1">
                                        <?= esc(substr($record->token, 0, 5)) . '****************' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate font-medium"><?= esc($record->message) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="btn-edit flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50 transition-all outline-none"
                                            data-id="<?= esc($record->id) ?>"
                                            data-url="<?= esc($record->url_api) ?>"
                                            data-instance="<?= esc($record->instance_id) ?>"
                                            data-token="<?= esc($record->token) ?>"
                                            data-message="<?= htmlspecialchars($record->message) ?>">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button type="button" class="btn-delete flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-all outline-none"
                                            data-id="<?= esc($record->id) ?>">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-2 opacity-30">
                                    <i class="fas fa-comment-slash text-4xl text-slate-300"></i>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Data Kosong</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile View (Cards) -->
        <div class="md:hidden divide-y divide-slate-100">
            <?php if (count($records) > 0): ?>
                <?php foreach ($records as $record): ?>
                    <div class="p-5 space-y-4 hover:bg-slate-50/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">#<?= esc($record->id) ?></p>
                                <h3 class="text-sm font-black text-slate-800"><?= esc($record->url_api) ?></h3>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="btn-edit w-10 h-10 flex items-center justify-center rounded-2xl bg-teal-50 text-teal-600 shadow-sm shadow-teal-500/10 active:scale-90 transition-all"
                                    data-id="<?= esc($record->id) ?>"
                                    data-url="<?= esc($record->url_api) ?>"
                                    data-instance="<?= esc($record->instance_id) ?>"
                                    data-token="<?= esc($record->token) ?>"
                                    data-message="<?= htmlspecialchars($record->message) ?>">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button type="button" class="btn-delete w-10 h-10 flex items-center justify-center rounded-2xl bg-red-50 text-red-500 shadow-sm shadow-red-500/10 active:scale-90 transition-all"
                                    data-id="<?= esc($record->id) ?>">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Instance ID</p>
                                <p class="text-[11px] font-mono font-bold text-slate-600 truncate"><?= esc(substr($record->instance_id, 0, 8)) ?>***</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Token</p>
                                <p class="text-[11px] font-mono font-bold text-slate-600 truncate"><?= esc(substr($record->token, 0, 8)) ?>***</p>
                            </div>
                        </div>
                        <div class="bg-teal-50/30 p-3 rounded-2xl border border-teal-100/30">
                            <p class="text-[9px] font-bold text-teal-600/60 uppercase tracking-widest mb-1">Message Template</p>
                            <p class="text-xs font-medium text-slate-600 italic line-clamp-2">"<?= esc($record->message) ?>"</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="py-20 text-center opacity-30">
                    <i class="fas fa-comment-slash text-4xl text-slate-300 mb-3"></i>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Belum ada data</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODALS (Slightly improved for mobile) -->
<div id="addModal" class="hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800">Tambah Data API</h3>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors outline-none" onclick="closeModal(document.getElementById('addModal'))">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= base_url('whatsapp/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">URL API</label>
                    <input type="text" name="url_api" required placeholder="Contoh: https://api.whatsapp.com" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Instance ID</label>
                    <input type="text" name="instance_id" required placeholder="Masukkan Instance ID" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Token</label>
                    <input type="text" name="token" required placeholder="Masukkan Token Akses" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Message Template</label>
                    <textarea name="message" rows="3" required placeholder="Tulis template pesan..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex flex-col md:flex-row gap-3">
                <button type="button" onclick="closeModal(document.getElementById('addModal'))" class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-600 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all outline-none">Batal</button>
                <button type="submit" class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest hover:bg-teal-700 transition-all shadow-lg shadow-teal-500/25 outline-none">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL (Symmetric with Add Modal) -->
<div id="editModal" class="hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800">Edit Konfigurasi</h3>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors outline-none" onclick="closeModal(document.getElementById('editModal'))">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="editForm" method="POST">
            <?= csrf_field() ?>
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">URL API</label>
                    <input type="text" id="editUrlApi" name="url_api" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Instance ID</label>
                    <input type="text" id="editInstanceId" name="instance_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Token</label>
                    <input type="text" id="editToken" name="token" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Message Template</label>
                    <textarea id="editMessageTemplate" name="message" rows="3" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none transition-all bg-slate-50 focus:bg-white shadow-inner"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex flex-col md:flex-row gap-3">
                <button type="button" onclick="closeModal(document.getElementById('editModal'))" class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-600 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all outline-none">Batal</button>
                <button type="submit" class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest hover:bg-teal-700 transition-all shadow-lg shadow-teal-500/25 outline-none">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-all duration-300 text-center">
        <div class="p-8">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-trash-alt text-2xl"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Hapus Data?</h3>
            <p class="text-xs font-medium text-slate-500 leading-relaxed uppercase tracking-wider">Konfigurasi ini akan dihapus permanen dari sistem.</p>
        </div>
        <div class="p-6 bg-slate-50 flex flex-col gap-2">
            <form id="deleteForm" method="POST" class="w-full">
                <?= csrf_field() ?>
                <button type="submit" class="w-full px-6 py-3.5 rounded-2xl bg-red-600 text-white text-sm font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-500/25 outline-none">Ya, Hapus Data</button>
            </form>
            <button type="button" onclick="closeModal(document.getElementById('deleteModal'))" class="w-full px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all outline-none">Batal</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.waConfig = {
        baseUrl: "<?= base_url('whatsapp') ?>",
        csrfTokenName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>
<?= $this->endSection() ?>