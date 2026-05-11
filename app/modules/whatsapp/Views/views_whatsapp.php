<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">WhatsApp API</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data API untuk integrasi pesan WhatsApp otomatis.</p>
        </div>
        <button id="btn-add-wa" type="button" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold hover:bg-teal-700 transition-all shadow-md shadow-teal-500/20 active:scale-95 outline-none">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Daftar Konfigurasi</h2>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Total: <?= count($records) ?> Data</p>
            </div>
            <div id="search-container" class="relative w-full md:w-80 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-20">
                    <i class="fas fa-search text-slate-400 group-focus-within:text-teal-600 transition-colors text-sm"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse" id="table-wa">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 whitespace-nowrap">ID</th>
                        <th class="px-6 py-4 whitespace-nowrap">URL API</th>
                        <th class="px-6 py-4 whitespace-nowrap">Instance ID</th>
                        <th class="px-6 py-4 whitespace-nowrap">Token</th>
                        <th class="px-6 py-4 whitespace-nowrap">Message Text</th>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
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
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate"><?= esc($record->message) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Data attribut ini sudah aman karena pakai htmlspecialchars -->
                                        <button type="button" class="btn-edit flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50 transition-all outline-none"
                                            data-id="<?= esc($record->id) ?>"
                                            data-url="<?= esc($record->url_api) ?>"
                                            data-instance="<?= esc($record->instance_id) ?>"
                                            data-token="<?= esc($record->token) ?>"
                                            data-message="<?= htmlspecialchars($record->message) ?>">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button type="button" class="btn-delete flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-all outline-none"
                                            data-id="<?= esc($record->id) ?>">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm">
                                Belum ada data WhatsApp API yang ditambahkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="addModal" class="hidden fixed inset-0 z-9999 bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800">Tambah Data API</h3>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors outline-none" onclick="closeModal(document.getElementById('addModal'))">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= base_url('whatsapp/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">URL API</label>
                    <input type="text" name="url_api" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Instance ID</label>
                    <input type="text" name="instance_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Token</label>
                    <input type="text" name="token" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Message Template</label>
                    <textarea name="message" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                <button type="button" onclick="closeModal(document.getElementById('addModal'))" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-100 transition-all outline-none">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold hover:bg-teal-700 transition-all shadow-md shadow-teal-500/20 outline-none">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 z-9999 bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800">Edit Data API</h3>
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors outline-none" onclick="closeModal(document.getElementById('editModal'))">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="editForm" method="POST">
            <?= csrf_field() ?>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">URL API</label>
                    <input type="text" id="editUrlApi" name="url_api" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Instance ID</label>
                    <input type="text" id="editInstanceId" name="instance_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Token</label>
                    <input type="text" id="editToken" name="token" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Message Template</label>
                    <textarea id="editMessageTemplate" name="message" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all bg-slate-50 focus:bg-white"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                <button type="button" onclick="closeModal(document.getElementById('editModal'))" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-100 transition-all outline-none">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold hover:bg-teal-700 transition-all shadow-md shadow-teal-500/20 outline-none">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="hidden fixed inset-0 z-9999 bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300 text-center">
        <div class="p-6 pt-8">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800 mb-2">Hapus Konfigurasi?</h3>
            <p class="text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin menghapus data API ini?</p>
        </div>
        <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-center gap-3">
            <button type="button" onclick="closeModal(document.getElementById('deleteModal'))" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-100 transition-all outline-none w-full">Batal</button>
            <form id="deleteForm" method="POST" class="w-full">
                <?= csrf_field() ?>
                <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition-all shadow-md shadow-red-500/20 outline-none">Ya, Hapus!</button>
            </form>
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