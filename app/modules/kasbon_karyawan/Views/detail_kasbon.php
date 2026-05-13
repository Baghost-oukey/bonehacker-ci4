<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <a href="<?= base_url('kasbon') ?>" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-teal-600 transition-colors mb-6">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Karyawan
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 mb-6 flex flex-col lg:flex-row items-center gap-6">
        <div class="w-20 h-20 rounded-full bg-teal-400 flex items-center justify-center text-teal-600 text-3xl font-extrabold shadow-inner shrink-0">
            <?= strtoupper(substr($karyawan['nama'], 0, 1)) ?>
        </div>
        <div class="flex-1 text-center sm:text-left">
            <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($karyawan['nama']) ?></h1>
            <p class="text-sm text-slate-500 uppercase tracking-widest font-semibold mt-1"><?= esc($karyawan['nama_jabatan'] ?? 'Karyawan') ?></p>
        </div>

        <div class="flex flex-wrap justify-center lg:justify-end gap-4 w-full lg:w-auto">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-center min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Gaji Pokok</p>
                <p class="text-lg font-black text-slate-800">Rp <?= number_format($karyawan['gaji_pokok'], 0, ',', '.') ?></p>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 text-center min-w-[160px]">
                <p class="text-[10px] font-bold text-amber-500 uppercase tracking-wider mb-1">Total Hutang</p>
                <p class="text-lg font-black text-amber-700">Rp <?= number_format($karyawan['total_kasbon_aktif'], 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div class="flex border-b border-slate-200 mb-8 gap-8 px-2">
        <button class="tab-btn active pb-4 text-sm font-black text-teal-600 border-b-2 border-teal-600 transition-all uppercase tracking-widest" data-target="tab-riwayat">
            <i class="fas fa-history mr-2"></i> Riwayat
        </button>
        <button class="tab-btn pb-4 text-sm font-black text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all uppercase tracking-widest" data-target="tab-ajukan">
            <i class="fas fa-plus-circle mr-2"></i> Kasbon
        </button>
        <button class="tab-btn pb-4 text-sm font-black text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all uppercase tracking-widest" data-target="tab-cicilan">
            <i class="fas fa-plus-circle mr-2"></i> Cicilan Kasbon
        </button>
    </div>

    <div id="tab-riwayat" class="tab-content block animate-in fade-in duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="p-5 pl-8">Tanggal</th>
                            <th class="p-5">Keterangan</th>
                            <th class="p-5 text-right">Pinjaman Awal</th>
                            <th class="p-5 text-right text-teal-600">Sisa Hutang</th>
                            <th class="p-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium">
                        <?php if (empty($riwayat)): ?>
                            <tr>
                                <td colspan="5" class="p-16 text-center text-slate-400 font-bold italic">Belum ada transaksi terekam.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $rw): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-5 pl-8 text-slate-600 font-bold"><?= date('d M Y', strtotime($rw['tanggal'])) ?></td>
                                    <td class="p-5 text-slate-800">
                                        <div class="max-w-xs truncate" title="<?= esc($rw['keterangan']) ?>"><?= esc($rw['keterangan']) ?></div>
                                    </td>
                                    <td class="p-5 text-right text-slate-400">Rp <?= number_format($rw['nominal'], 0, ',', '.') ?></td>
                                    <td class="p-5 text-right font-black text-slate-900">Rp <?= number_format($rw['sisa_hutang'], 0, ',', '.') ?></td>
                                    <td class="p-5 text-center">
                                        <?php if ($rw['status_potongan'] == 'belum_lunas'): ?>
                                            <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 bg-teal-100 text-teal-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter">
                                                <i class="fas fa-check-circle"></i>
                                                Lunas
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-ajukan" class="tab-content hidden animate-in fade-in duration-300">
        <?= $this->include('App\modules\kasbon_karyawan\Views\form\form_ajukan') ?>
    </div>

    <div id="tab-cicilan" class="tab-content hidden animate-in fade-in duration-300">
        <?= $this->include('App\modules\kasbon_karyawan\Views\form\form_cicilan') ?>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.kasbonDetailConfig = {
        csrfName:   "<?= csrf_token() ?>",
        csrfHash:   "<?= csrf_hash() ?>",
        storeUrl:   "<?= base_url('kasbon/store') ?>",
        cicilanUrl: "<?= base_url('kasbon/bayar') ?>",
        totalHutang: <?= (int)($karyawan['total_kasbon_aktif'] ?? 0) ?>
    };
</script>
<?= $this->endSection() ?>