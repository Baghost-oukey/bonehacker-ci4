<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="w-full md:w-auto">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kasbon Karyawan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">Lihat riwayat atau cairkan kasbon baru.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full lg:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('gaji') ?>">💵 Gaji Karyawan</option>
                <option value="<?= site_url('transaksi-tunjangan') ?>">💰 Tunjangan Terapis</option>
                <option value="<?= site_url('master-gaji') ?>">⚙️ Master Gaji</option>
                <option value="<?= site_url('kasbon') ?>" selected>💸 Kasbon Karyawan</option>
            </select>
        </div>
    </div>

    <!-- TAMPILAN DESKTOP (TABLE) -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        <table id="table-karyawan-kasbon" class="table-auto w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-widest">
                    <th class="py-4 px-4 w-12 text-center">No</th>
                    <th class="py-4 px-4">Nama Karyawan</th>
                    <th class="py-4 px-4">Jabatan</th>
                    <th class="py-4 px-4 text-center">Status Keuangan</th>
                    <th class="py-4 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
            </tbody>
        </table>
    </div>

    <!-- TAMPILAN MOBILE (CARDS) -->
    <div id="mobile-card-container" class="md:hidden space-y-4 pb-20">
        <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
            Memuat data karyawan...
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.kasbonIndexConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('kasbon/fetch') ?>"
    };
</script>
<?= $this->endSection() ?>