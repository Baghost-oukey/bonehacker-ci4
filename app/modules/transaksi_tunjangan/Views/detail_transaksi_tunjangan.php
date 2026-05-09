<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <a href="<?= base_url('transaksi-tunjangan') ?>" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors mb-6">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Terapis
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 mb-6 flex flex-col lg:flex-row items-center gap-6">
        <div class="w-20 h-20 rounded-full bg-indigo-500 flex items-center justify-center text-white text-3xl font-extrabold shadow-inner shrink-0">
            <?= strtoupper(substr($terapis['nama'], 0, 1)) ?>
        </div>
        <div class="flex-1 text-center sm:text-left">
            <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($terapis['nama']) ?></h1>
            <p class="text-sm text-slate-500 uppercase tracking-widest font-semibold mt-1"><?= esc($terapis['nama_jabatan'] ?? 'Terapis') ?></p>
        </div>

        <div class="flex flex-wrap justify-center lg:justify-end gap-4 w-full lg:w-auto">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-center min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Gaji Pokok</p>
                <p class="text-lg font-black text-slate-800">Rp <?= number_format($terapis['nominal_gaji'] ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 text-center min-w-[160px]">
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-1">Total Tunjangan Aktif</p>
                <p class="text-lg font-black text-indigo-700">Rp <?= number_format($total_tunjangan, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div class="flex border-b border-slate-200 mb-8 gap-8 px-2">
        <button class="tab-btn active pb-4 text-sm font-black text-indigo-600 border-b-2 border-indigo-600 transition-all uppercase tracking-widest" data-target="tab-riwayat">
            <i class="fas fa-history mr-2"></i> Riwayat
        </button>
        <button class="tab-btn pb-4 text-sm font-black text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all uppercase tracking-widest" data-target="tab-input-tunjangan">
            <i class="fas fa-plus-circle mr-2"></i> Input Tunjangan
        </button>
    </div>

    <div id="tab-riwayat" class="tab-content block animate-in fade-in duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="p-5 pl-8">Tanggal</th>
                            <th class="p-5">Jenis Tunjangan</th>
                            <th class="p-5">Keterangan</th>
                            <th class="p-5 text-right text-indigo-600">Nominal</th>
                            <th class="p-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium">
                        <?php if (empty($riwayat)): ?>
                            <tr>
                                <td colspan="5" class="p-16 text-center text-slate-400 font-bold italic">Belum ada riwayat tunjangan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $rw): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-5 pl-8 text-slate-600 font-bold"><?= date('d M Y', strtotime($rw['tanggal'])) ?></td>
                                    <td class="p-5">
                                        <span class="font-black text-slate-800"><?= esc($rw['nama_tunjangan']) ?></span>
                                        <p class="text-[10px] text-slate-400 uppercase"><?= esc($rw['kategori']) ?></p>
                                    </td>
                                    <td class="p-5 text-slate-500 text-xs"><?= esc($rw['keterangan'] ?: '-') ?></td>
                                    <td class="p-5 text-right font-black text-indigo-600">Rp <?= number_format($rw['nominal'], 0, ',', '.') ?></td>
                                    <td class="p-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 bg-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                                            <?= esc($rw['status_pembayaran']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-input-tunjangan" class="tab-content hidden animate-in fade-in duration-300">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm w-full">
            <form id="formInputTunjangan">
                <?= csrf_field(); ?>
                <input type="hidden" name="tipe_input" value="spesifik">
                <input type="hidden" name="terapis_id" value="<?= $terapis['id'] ?>">

                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Jenis Tunjangan</label>
                        <select name="tunjangan_karyawan_id" required class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Jenis --</option>
                            <?php foreach ($master_tunjangan as $mt): ?>
                                <option value="<?= $mt['id'] ?>"><?= esc($mt['nama_tunjangan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Nominal (Rp)</label>
                            <input type="text" name="nominal" id="inputNominal" required class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Tanggal</label>
                            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="w-full bg-slate-50 border-none rounded-xl p-4 text-sm font-bold text-slate-700" placeholder="Contoh: Bonus lembur pasien VIP"></textarea>
                    </div>

                    <button type="submit" id="btnSubmitTunjangan" class="w-full bg-indigo-600 py-4 rounded-2xl text-white font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 mt-4">
                        Simpan Tunjangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.tunjanganDetailConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        storeUrl: "<?= base_url('transaksi-tunjangan/store') ?>"
    };
</script>
<?= $this->endSection() ?>