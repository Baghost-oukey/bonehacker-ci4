<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8 max-w-350 mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight"><?= $title ?></h1>
        <p class="text-sm text-slate-500 mt-1">Kelola template teks salam untuk dikirimkan ke pasien.</p>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full md:hidden mt-4">
            <select onchange="window.location.href=this.value" class="w-full bg-slate-50 border border-slate-100 text-slate-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-500/20">
                <option value="<?= site_url('logs') ?>">Logs</option>
                <option value="<?= site_url('whatsapp') ?>">WhatsApp</option>
                <option value="<?= site_url('log_whatsapp') ?>">Log WhatsApp</option>
                <option value="<?= site_url('jabatan') ?>">Jabatan</option>
                <option value="<?= site_url('greeting') ?>" selected>Greetings</option>
            </select>
        </div>
    </div>

    <div class="flex flex-col gap-8">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-white">
                <h4 class="text-lg font-bold text-slate-800">Daftar Salam Tersimpan</h4>
                <p class="text-[12px] text-slate-400 mt-1">Total salam aktif: <?= count($greetings ?? []) ?> data</p>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-wider bg-slate-50/30">
                            <th class="px-6 py-4 w-16">No</th>
                            <th class="px-6 py-4">Teks Salam</th>
                            <th class="px-6 py-4 text-right w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (isset($greetings) && count($greetings) > 0): ?>
                            <?php foreach ($greetings as $index => $greeting): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-500 align-middle">
                                        <?= $index + 1 ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-700 font-medium whitespace-pre-wrap leading-relaxed align-middle">
                                        <?= esc($greeting) ?>
                                    </td>

                                    <td class="px-6 py-4 text-right align-middle">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" class="btn-edit flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50 transition-all outline-none"
                                                data-index="<?= $index ?>"
                                                data-text="<?= esc($greeting) ?>">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>

                                            <a href="<?= base_url('greeting/delete/' . $index) ?>" class="flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-all outline-none">
                                                <i class="fas fa-trash text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400 text-sm font-medium">
                                    Belum ada salam tersimpan. Silakan tambah baru di formulir bawah!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($pager)): ?>
            <div class="flex justify-center w-full bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <?= $pager ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-10">
            <div class="p-6 border-b border-slate-100 bg-white">
                <h5 id="form-title" class="text-lg font-bold text-slate-800">Menambah Salam Baru</h5>
            </div>

            <form method="POST" action="<?= base_url('greeting/save') ?>" class="form p-6">
                <?= csrf_field() ?>
                <div class="form-group mb-6">
                    <label for="greetings" class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Teks Salam</label>
                    <p class="text-[11px] text-slate-400 mb-3 leading-relaxed">Tekan 'Enter' untuk membuat baris baru. Satu kotak ini merepresentasikan satu salam utuh.</p>

                    <textarea id="greetings_input" name="greetings" class="form-control w-full px-4 py-4 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none bg-slate-50 focus:bg-white transition-all resize-y min-h-30" placeholder="Ketik salam Anda di sini..." required></textarea>
                </div>

                <input type="hidden" id="greeting_index" name="greeting_index" value="">

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="btn-cancel" class="hidden px-5 py-2.5 rounded-xl text-slate-500 hover:bg-slate-100 text-sm font-bold border border-slate-200 shadow-sm transition-all outline-none">
                        Batal Edit
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-bold border-0 shadow-md shadow-teal-500/20 hover:bg-teal-700 w-full sm:w-auto flex justify-center items-center gap-2 outline-none transition-all">
                        <i class="fas fa-save"></i> Simpan Salam
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.greetingConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>
<?= $this->endSection() ?>