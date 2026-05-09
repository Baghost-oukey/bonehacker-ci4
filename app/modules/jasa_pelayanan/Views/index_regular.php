<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-7xl" id="pelayananPage">

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?= esc($title) ?></h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data riwayat tindakan dan transaksi <?= esc($kategori) ?>.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <a href="<?= base_url('jasa-pelayanan/tambah?kategori=' . strtolower($kategori)) ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm uppercase tracking-widest py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-lg font-bold text-slate-700">Daftar Transaksi</h2>
        </div>
        
        <div class="p-6 overflow-x-auto">
            <table id="tablePelayanan" class="w-full text-left border-collapse" style="width:100%">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                        <th class="p-4 pl-0">No</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Nama Pasien</th>
                        <th class="p-4">Nama Terapis</th>
                        <th class="p-4 text-center pr-0">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-medium text-slate-700">
                    </tbody>
            </table>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<script>
    window.pelayananConfig = {
        kategori: "<?= esc($kategori) ?>", 
        fetchUrl: "<?= base_url('jasa-pelayanan/fetch') ?>",
        deleteUrl: "<?= base_url('jasa-pelayanan/destroy/') ?>",
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>"
    };
</script>

<?= $this->endSection(); ?>