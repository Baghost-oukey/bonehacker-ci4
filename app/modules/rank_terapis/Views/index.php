<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="w-full px-2 py-4 md:px-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-800">Pengaturan Rank Terapis</h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Kelola pilihan rank yang digunakan pada profil terapis.</p>
        </div>

        <div class="w-full md:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-slate-50 border border-slate-100 text-slate-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-slate-500/20">
                <option value="<?= site_url('logs') ?>">Logs</option>
                <option value="<?= site_url('whatsapp') ?>">WhatsApp</option>
                <option value="<?= site_url('log_whatsapp') ?>">Log WhatsApp</option>
                <option value="<?= site_url('jabatan') ?>">Jabatan</option>
                <option value="<?= site_url('rank-terapis') ?>" selected>Rank Terapis</option>
                <option value="<?= site_url('greeting') ?>">Greetings</option>
            </select>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
                <h2 class="text-lg font-black text-slate-800">Daftar Rank</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Data Master Rank Terapis</p>
            </div>

            <div class="hidden md:block overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-slate-50 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <th class="px-6 py-5 whitespace-nowrap">Urutan</th>
                            <th class="px-6 py-5 whitespace-nowrap">Rank</th>
                            <th class="px-6 py-5 whitespace-nowrap">Deskripsi</th>
                            <th class="px-6 py-5 whitespace-nowrap">Status</th>
                            <th class="px-6 py-5 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($ranks as $rank): ?>
                            <tr class="text-sm text-slate-700">
                                <td class="px-6 py-4 font-bold"><?= esc($rank->sort_order) ?></td>
                                <td class="px-6 py-4 font-black text-slate-800"><?= esc($rank->name) ?></td>
                                <td class="px-6 py-4"><?= esc($rank->description ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-widest <?= (int) $rank->is_active === 1 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                        <?= (int) $rank->is_active === 1 ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="btn-edit-rank w-9 h-9 rounded-xl bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white transition-all"
                                            data-id="<?= esc($rank->id) ?>"
                                            data-name="<?= esc($rank->name) ?>"
                                            data-description="<?= esc($rank->description ?? '') ?>"
                                            data-sort-order="<?= esc($rank->sort_order) ?>"
                                            data-is-active="<?= esc($rank->is_active) ?>">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <form action="<?= base_url('rank-terapis/destroy/' . $rank->id) ?>" method="post" onsubmit="return confirm('Hapus rank ini?')" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-slate-50">
                <?php foreach ($ranks as $rank): ?>
                    <div class="p-5 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-black text-slate-800"><?= esc($rank->name) ?></h3>
                                <p class="text-xs font-medium text-slate-500 mt-1"><?= esc($rank->description ?? '-') ?></p>
                            </div>
                            <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-500">#<?= esc($rank->sort_order) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-widest <?= (int) $rank->is_active === 1 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                <?= (int) $rank->is_active === 1 ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn-edit-rank w-9 h-9 rounded-xl bg-teal-50 text-teal-600"
                                    data-id="<?= esc($rank->id) ?>"
                                    data-name="<?= esc($rank->name) ?>"
                                    data-description="<?= esc($rank->description ?? '') ?>"
                                    data-sort-order="<?= esc($rank->sort_order) ?>"
                                    data-is-active="<?= esc($rank->is_active) ?>">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <form action="<?= base_url('rank-terapis/destroy/' . $rank->id) ?>" method="post" onsubmit="return confirm('Hapus rank ini?')" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-9 h-9 rounded-xl bg-red-50 text-red-600">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden h-fit">
            <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/30">
                <h2 id="rankFormTitle" class="text-lg font-black text-slate-800">Tambah Rank</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Master pilihan rank</p>
            </div>
            <form id="rankForm" action="<?= base_url('rank-terapis/store') ?>" method="post" class="p-5 md:p-6 space-y-5">
                <?= csrf_field() ?>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Rank</label>
                    <input type="text" id="rankName" name="name" required maxlength="20" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" placeholder="Contoh: A+">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Urutan</label>
                    <input type="number" id="rankSortOrder" name="sort_order" min="0" value="0" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Deskripsi</label>
                    <textarea id="rankDescription" name="description" rows="3" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner" placeholder="Catatan singkat untuk rank ini..."></textarea>
                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 cursor-pointer">
                    <input type="checkbox" id="rankIsActive" name="is_active" checked class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-sm font-bold text-slate-700">Rank aktif</span>
                </label>
                <div class="flex flex-col gap-3 pt-2">
                    <button type="submit" id="rankSubmitLabel" class="w-full px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all">
                        Simpan Rank
                    </button>
                    <button type="button" id="rankCancelEdit" class="hidden w-full px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Batal Edit
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('rankForm');
        const title = document.getElementById('rankFormTitle');
        const name = document.getElementById('rankName');
        const description = document.getElementById('rankDescription');
        const sortOrder = document.getElementById('rankSortOrder');
        const isActive = document.getElementById('rankIsActive');
        const submitLabel = document.getElementById('rankSubmitLabel');
        const cancel = document.getElementById('rankCancelEdit');
        const storeUrl = "<?= base_url('rank-terapis/store') ?>";
        const updateBaseUrl = "<?= base_url('rank-terapis/update') ?>";

        const resetForm = () => {
            form.action = storeUrl;
            title.textContent = 'Tambah Rank';
            submitLabel.textContent = 'Simpan Rank';
            cancel.classList.add('hidden');
            name.value = '';
            description.value = '';
            sortOrder.value = 0;
            isActive.checked = true;
        };

        document.querySelectorAll('.btn-edit-rank').forEach((button) => {
            button.addEventListener('click', () => {
                form.action = `${updateBaseUrl}/${button.dataset.id}`;
                title.textContent = 'Edit Rank';
                submitLabel.textContent = 'Simpan Perubahan';
                cancel.classList.remove('hidden');
                name.value = button.dataset.name || '';
                description.value = button.dataset.description || '';
                sortOrder.value = button.dataset.sortOrder || 0;
                isActive.checked = button.dataset.isActive === '1';
                name.focus();
            });
        });

        cancel.addEventListener('click', resetForm);
    });
</script>
<?= $this->endSection() ?>
