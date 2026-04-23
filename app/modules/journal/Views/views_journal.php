<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-8 md:px-8 w-full max-w-350 mx-auto animate-fade-in">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?= esc($title) ?></h1>
            <p class="text-slate-500 mt-1 text-sm font-medium">Pantau dan kelola data jurnal pemeriksaan secara efisien</p>
        </div>
        <div class="flex items-center gap-3">

            <button type="button" id="btnOpenExport" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-500/20 hover:bg-teal-700 transition-all active:scale-95">
                <i class="fas fa-file-export"></i> Export Data
            </button>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="p-6 border-b border-slate-100 bg-white">
            <div class="flex flex-col gap-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Filter Pencarian</h2>
                    <p class="text-slate-500 text-sm mt-0.5">Sesuaikan rentang waktu dan wilayah untuk memfilter data</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                    <div class="md:col-span-5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Rentang Waktu</label>
                        <div class="flex items-center gap-2">
                            <input type="date" id="start_date" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white">
                            <span class="text-slate-400 font-bold text-[10px] uppercase">s/d</span>
                            <input type="date" id="end_date" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white">
                        </div>
                    </div>



                    <div class="md:col-span-3">
                        <button type="button" id="btn-reset" class="w-full flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-600 text-[10px] font-black hover:bg-red-100 transition-all uppercase tracking-widest">
                            <i class="fas fa-undo"></i> Reset Filter
                        </button>
                    </div>
                    <div class="md:col-span-3">
                        <?php if (session()->get('role') === 'user'): ?>
                            <div class="relative">
                                <input type="text" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 font-bold" value="<?= session()->get('region_name') ?>" readonly>
                                <input type="hidden" id="region" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                            </div>
                        <?php else: ?>
                            <div class="relative w-full group">
                                <button type="button" id="btn-dropdown-region" class="flex items-center justify-between px-4 py-2.5 rounded-xl border border-teal-600 bg-teal-600 text-white text-sm font-bold hover:border-teal-500 hover:bg-teal-900 transition-all outline-none shadow-sm group-hover:shadow-md">
                                    <span id="selected-region-text" class="truncate uppercase mx-7 tracking-tight">Semua Wilayah</span>
                                </button>

                                <div class="absolute inset-x-0 bottom-0 opacity-0 pointer-events-none w-auto">
                                    <select id="region" class="form-control select2">
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php $selected = (session()->get('active_region') == $value->id) ? 'selected' : ''; ?>
                                            <option value="<?= $value->id ?>" <?= $selected ?>><?= esc($value->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="md:col-span-5">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Pencarian Data</label>
                        <div class="flex items-center gap-2">
                            <div class="relative group w-full">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-20">
                                    <i class="fas fa-search text-slate-400 group-focus-within:text-teal-600 transition-colors text-sm"></i>
                                </div>
                                <input type="text" id="customSearch" placeholder="Ketik nama pasien..." class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 placeholder:text-slate-400 placeholder:font-medium focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 shadow-sm transition-all outline-none block">
                            </div>

                            <button type="button" id="btn-search" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 text-white text-xs font-black hover:bg-teal-700 transition-all shadow-md shadow-teal-500/20 active:scale-95 whitespace-nowrap outline-none">
                                Cari
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-journal" class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100">No</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100">Tanggal</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100">Nama Pasien</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100">Alamat</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100">Hasil Pemeriksaan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100">Tindakan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-teal-500"></i>
                            Memuat data jurnal pemeriksaan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalExportJournal" class="hidden fixed inset-0 z-9999 bg-slate-900/60 backdrop-blur-sm flex-col items-center justify-center p-4 sm:p-0">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden w-6xl">
            <div class="bg-teal-600 px-8 py-6 text-white relative">
                <h5 class="text-xl font-black tracking-tight uppercase">Export Data Jurnal</h5>
                <p class="text-white text-xs mt-1 font-medium">Unduh laporan dalam format Excel atau PDF</p>
                <button type="button" class="absolute top-6 right-6 text-slate-500 hover:text-white transition-colors" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="<?= site_url('journal/export_file_journal') ?>" method="GET" target="_blank">
                <div class="p-8 bg-white space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Periode Laporan</label>
                        <select id="period_picker" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                            <option value="all">Seluruh Data</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="last_year">Tahun Lalu</option>
                            <option value="custom">Pilih Tanggal Sendiri</option>
                        </select>
                    </div>

                    <div id="custom_date_container" style="display: none;" class="grid grid-cols-2 gap-4 animate-fade-in">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Mulai</label>
                            <input type="date" name="start_date" id="exp_start_date" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Selesai</label>
                            <input type="date" name="end_date" id="exp_end_date" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Target Wilayah</label>
                        <?php if (session()->get('role') === 'user'): ?>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-500" value="<?= session()->get('region_name') ?>" readonly>
                            <input type="hidden" name="region_id" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                        <?php else: ?>
                            <select name="region_id" id="export_region" class="form-control select2">
                                <option value="">Semua Wilayah</option>
                                <?php foreach ($wilayah as $r): ?>
                                    <option value="<?= $r->id ?>"><?= esc($r->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Format Dokumen</label>
                        <select name="format_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-700">
                            <option value="excel">Microsoft Excel (.xlsx)</option>
                            <option value="pdf">PDF Document (.pdf)</option>
                        </select>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" class="px-6 py-2.5 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors" data-dismiss="modal">Batal</button>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-teal-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition-all active:scale-95">
                        Unduh Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.journalConfig = {
        fetchUrl: "<?= site_url('journal/fetch') ?>",
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>

<?= $this->endSection() ?>