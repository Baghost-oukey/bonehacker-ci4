<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-6 md:p-8 max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Kategori Keuangan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola kategori pemasukan dan pengeluaran (Global & Cabang)</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span class="text-sm font-bold"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <span class="text-sm font-bold"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- FORM TAMBAH -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sticky top-8">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-indigo-600"></i>
                    Tambah Kategori
                </h3>
                <form action="<?= base_url('kas/categories/store') ?>" method="POST" class="space-y-5">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Misal: Biaya Listrik"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jenis</label>
                        <select name="type" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value="expense">Pengeluaran</option>
                            <option value="income">Pemasukan</option>
                        </select>
                    </div>

                    <?php if (session()->get('role') === 'superadmin'): ?>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Cakupan Wilayah</label>
                        <select name="region_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value="global">Global (Semua Cabang)</option>
                            <?php foreach ($regions as $r): ?>
                                <option value="<?= $r->id ?>"><?= esc($r->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-2 italic">* Kategori Global akan muncul di seluruh cabang.</p>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-xl font-black text-sm uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                        Simpan Kategori
                    </button>
                </form>
            </div>
        </div>

        <!-- LIST KATEGORI -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Daftar Kategori Aktif</h3>
                    
                    <?php if (session()->get('role') === 'superadmin'): ?>
                    <form action="" method="GET" class="flex items-center gap-2">
                        <select name="region_id" onchange="this.form.submit()" class="text-xs font-bold border-slate-200 rounded-lg bg-white">
                            <option value="all">Semua Wilayah</option>
                            <?php foreach ($regions as $r): ?>
                                <option value="<?= $r->id ?>" <?= $filter_region == $r->id ? 'selected' : '' ?>><?= esc($r->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="p-6">Nama Kategori</th>
                                <th class="p-6">Jenis</th>
                                <th class="p-6">Cakupan</th>
                                <th class="p-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-6 font-bold text-slate-700"><?= esc($cat['name']) ?></td>
                                <td class="p-6">
                                    <?php if ($cat['type'] === 'income'): ?>
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-100">Pemasukan</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-rose-100">Pengeluaran</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-6">
                                    <?php if ($cat['is_default']): ?>
                                        <span class="text-xs font-bold text-indigo-600"><i class="fas fa-shield-alt mr-1"></i> System Default</span>
                                    <?php elseif (is_null($cat['region_id'])): ?>
                                        <span class="text-xs font-bold text-slate-500"><i class="fas fa-globe mr-1"></i> Global</span>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-amber-600"><i class="fas fa-map-marker-alt mr-1"></i> Cabang Spesifik</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-6 text-right">
                                    <?php if (!$cat['is_default']): ?>
                                        <a href="<?= base_url('kas/categories/delete/' . $cat['id']) ?>" 
                                           onclick="return confirm('Hapus kategori ini?')"
                                           class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-slate-300 italic">Locked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
