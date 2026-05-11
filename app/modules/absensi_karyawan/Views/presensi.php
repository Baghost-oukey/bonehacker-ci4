<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-6xl" id="presensiPage">

    <div class="flex flex-col gap-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="<?= base_url('kehadiran') ?>" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition-all">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Presensi Harian</h1>
                    <p class="text-slate-500 text-sm mt-1">Sistem rekam kehadiran cepat (otomatis pilih Hadir).</p>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal Presensi</p>
                    <input type="date" id="tanggal_presensi" value="<?= esc($tanggal) ?>" class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white">
                </div>
            </div>
        </div>

        <form id="formPresensi" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="tanggal" id="tanggal_presensi_hidden" value="<?= esc($tanggal) ?>">

            <?php if (empty($terapis)): ?>
                <div class="bg-white rounded-4xl border border-slate-200 p-8 text-center text-slate-400 font-bold shadow-sm">
                    Belum ada data terapis aktif.
                </div>
            <?php else: ?>
                <?php foreach ($terapis as $index => $t): ?>
                    <?php $existing = $rekap_by_tanggal[$t->id] ?? null; ?>
                    <div class="rounded-4xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-black text-xl">
                                    <?= strtoupper(substr($t->nama, 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-base font-black text-slate-900 uppercase tracking-tight"><?= esc($t->nama) ?></p>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Terapis</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold uppercase tracking-[0.3em] text-slate-400">Keterangan</span>
                                <input type="text" name="absen[<?= $index ?>][keterangan]" value="<?= esc($existing['keterangan'] ?? '') ?>" placeholder="Sakit / Izin..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-500 focus:bg-white transition-all">
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 lg:grid-cols-[1.3fr_1fr] lg:items-center">
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="absen[<?= $index ?>][terapis_id]" value="<?= $t->id ?>">
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="absen[<?= $index ?>][status]" value="Hadir" class="peer sr-only" <?= !$existing || $existing['status'] === 'Hadir' ? 'checked' : '' ?> >
                                    <div class="flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-500 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                        <i class="fas fa-check-circle"></i> Hadir
                                    </div>
                                </label>
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="absen[<?= $index ?>][status]" value="Tidak Hadir" class="peer sr-only" <?= $existing && $existing['status'] === 'Tidak Hadir' ? 'checked' : '' ?> >
                                    <div class="flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-500 transition-all peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700">
                                        <i class="fas fa-times-circle"></i> Tidak Hadir
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="sticky bottom-0 z-40 bg-slate-50/80 pt-4 backdrop-blur-sm">
                <div class="mx-auto flex w-full max-w-6xl justify-center px-4 py-3">
                    <button type="submit" id="btnSimpanPresensi" class="inline-flex items-center justify-center rounded-3xl bg-indigo-600 px-8 py-4 text-sm font-black uppercase tracking-[0.2em] text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700">
                        <i class="fas fa-save mr-3"></i> Simpan Presensi Hari Ini
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<script>
    window.presensiConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        storeUrl: "<?= base_url('kehadiran/simpan_massal') ?>",
        redirectUrl: "<?= base_url('kehadiran') ?>",
    };
</script>

<?= $this->endSection(); ?>
