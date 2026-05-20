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

    <!-- DROPDOWN TAB MOBILE -->
    <div class="md:hidden mb-8">
        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Pilih Menu</label>
        <select id="mobile-tab-select" class="w-full bg-white border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-4 py-3.5 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 shadow-sm transition-all">
            <option value="tab-riwayat">🕒 Riwayat Kasbon</option>
            <option value="tab-ajukan">💸 Kasbon Karyawan</option>
            <option value="tab-cicilan">💰 Cicilan Kasbon</option>
            <option value="tab-potongan">✂️ Potongan Rutin</option>
        </select>
    </div>

    <!-- TAMPILAN TABS DESKTOP -->
    <div class="hidden md:flex border-b border-slate-200 mb-8 gap-8 px-2">
        <button class="tab-btn active pb-4 text-sm font-black text-teal-600 border-b-2 border-teal-600 transition-all uppercase tracking-widest shrink-0" data-target="tab-riwayat">
            <i class="fas fa-history mr-2"></i> Riwayat
        </button>
        <button class="tab-btn pb-4 text-sm font-black text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all uppercase tracking-widest shrink-0" data-target="tab-ajukan">
            <i class="fas fa-plus-circle mr-2"></i> Kasbon
        </button>
        <button class="tab-btn pb-4 text-sm font-black text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all uppercase tracking-widest shrink-0" data-target="tab-cicilan">
            <i class="fas fa-plus-circle mr-2"></i> Cicilan Kasbon
        </button>
        <button class="tab-btn pb-4 text-sm font-black text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all uppercase tracking-widest shrink-0" data-target="tab-potongan">
            <i class="fas fa-scissors mr-2"></i> Potongan Rutin
        </button>
    </div>

    <div id="tab-riwayat" class="tab-content block animate-in fade-in duration-300">
        <!-- TAMPILAN DESKTOP (TABLE) -->
        <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
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

        <!-- TAMPILAN MOBILE (CARDS) -->
        <div class="md:hidden space-y-4">
            <?php if (empty($riwayat)): ?>
                <div class="p-10 text-center text-slate-400 font-bold italic bg-white rounded-2xl border border-slate-200 shadow-sm">
                    Belum ada transaksi terekam.
                </div>
            <?php else: ?>
                <?php foreach ($riwayat as $rw): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col gap-3 animate-in fade-in duration-300">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500"><?= date('d M Y', strtotime($rw['tanggal'])) ?></span>
                            <div>
                                <?php if ($rw['status_potongan'] == 'belum_lunas'): ?>
                                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-teal-100 text-teal-700 px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                        <i class="fas fa-check-circle text-[8px]"></i>
                                        Lunas
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="border-t border-slate-50 pt-2.5">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Keterangan</p>
                            <p class="text-xs font-bold text-slate-800 break-words"><?= esc($rw['keterangan']) ?></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t border-slate-50 pt-3 mt-1">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Pinjaman Awal</span>
                                <span class="text-xs font-bold text-slate-500">Rp <?= number_format($rw['nominal'], 0, ',', '.') ?></span>
                            </div>
                            <div class="flex flex-col gap-0.5 text-right">
                                <span class="text-[9px] font-black text-teal-600 uppercase tracking-widest block mb-0.5">Sisa Hutang</span>
                                <span class="text-xs font-black text-slate-900">Rp <?= number_format($rw['sisa_hutang'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="tab-ajukan" class="tab-content hidden animate-in fade-in duration-300">
        <?= $this->include('App\modules\kasbon_karyawan\Views\form\form_ajukan') ?>
    </div>

    <div id="tab-cicilan" class="tab-content hidden animate-in fade-in duration-300">
        <?= $this->include('App\modules\kasbon_karyawan\Views\form\form_cicilan') ?>
    </div>

    <!-- Tab Potongan Rutin -->
    <div id="tab-potongan" class="tab-content hidden animate-in fade-in duration-300">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daftar Potongan Aktif -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700">Potongan Rutin Aktif</h3>
                    <span class="text-xs text-slate-400">Dipotong otomatis saat proses gaji</span>
                </div>
                <div class="divide-y divide-slate-50">
                    <?php if (empty($potongan_rutin)): ?>
                        <div class="p-6 text-center text-slate-400 text-sm italic">Belum ada potongan rutin</div>
                    <?php else: ?>
                        <?php foreach ($potongan_rutin as $p): ?>
                            <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 transition">
                                <div>
                                    <p class="text-sm font-bold text-slate-800"><?= esc($p['nama_potongan']) ?></p>
                                    <p class="text-xs text-rose-600 font-semibold">- Rp <?= number_format($p['nominal'], 0, ',', '.') ?>/bulan</p>
                                </div>
                                <button onclick="hapusPotongan(<?= $p['id'] ?>)"
                                    class="text-slate-300 hover:text-red-500 transition p-1.5 rounded-lg hover:bg-red-50">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Tambah Potongan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-3">
                    <h3 class="text-sm font-bold text-slate-700">Tambah Potongan Rutin</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Contoh: BPJS Kesehatan 1%, iuran koperasi</p>
                </div>
                <form id="formPotonganRutin" class="p-5 space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="terapis_id" value="<?= $karyawan['id'] ?>">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 block">Nama Potongan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_potongan" required placeholder="Contoh: BPJS Kesehatan 1%"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-rose-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 block">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input type="text" name="nominal" id="inputNominalPotongan" required placeholder="Contoh: 27.732"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-rose-600 focus:border-rose-400 focus:outline-none">
                    </div>
                    <button type="submit" id="btnSimpanPotongan"
                        class="w-full rounded-xl bg-rose-600 py-3 text-sm font-black uppercase tracking-widest text-white transition hover:bg-rose-700">
                        <i class="fas fa-plus mr-2"></i> Tambah Potongan
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.kasbonDetailConfig = {
        csrfName:    "<?= csrf_token() ?>",
        csrfHash:    "<?= csrf_hash() ?>",
        storeUrl:    "<?= base_url('kasbon/store') ?>",
        cicilanUrl:  "<?= base_url('kasbon/bayar') ?>",
        potonganUrl: "<?= base_url('kasbon/potongan/store') ?>",
        deletePotonganUrl: "<?= base_url('kasbon/potongan/delete') ?>",
        totalHutang: <?= (int)($karyawan['total_kasbon_aktif'] ?? 0) ?>
    };

    // Format rupiah potongan
    document.getElementById('inputNominalPotongan')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? parseInt(v).toLocaleString('id-ID') : '';
    });

    // Submit potongan rutin
    document.getElementById('formPotonganRutin')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanPotongan');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';

        const cfg = window.kasbonDetailConfig;
        $.ajax({
            url: cfg.potonganUrl,
            type: 'POST',
            data: $(this).serialize() + '&' + cfg.csrfName + '=' + cfg.csrfHash,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1200, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            complete: () => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus mr-2"></i> Tambah Potongan'; }
        });
    });

    window.hapusPotongan = function (id) {
        const cfg = window.kasbonDetailConfig;
        Swal.fire({
            title: 'Hapus potongan ini?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: cfg.deletePotonganUrl + '/' + id,
                type: 'POST',
                data: { [cfg.csrfName]: cfg.csrfHash },
                dataType: 'json',
                success: function (res) {
                    if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                    Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1000, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1000);
                }
            });
        });
    };

    // Sinkronisasi mobile dropdown dengan tab buttons (Dua Arah)
    document.getElementById('mobile-tab-select')?.addEventListener('change', function () {
        const target = this.value;
        const matchingBtn = document.querySelector(`.tab-btn[data-target="${target}"]`);
        if (matchingBtn) {
            matchingBtn.click();
        }
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-target');
            const selectEl = document.getElementById('mobile-tab-select');
            if (selectEl && selectEl.value !== target) {
                selectEl.value = target;
            }
        });
    });
</script>
<?= $this->endSection() ?>