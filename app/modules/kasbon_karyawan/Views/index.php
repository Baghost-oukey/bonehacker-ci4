<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kasbon Karyawan</h1>
        <p class="text-sm text-slate-500 mt-1.5 font-medium">Pilih karyawan untuk melihat riwayat atau mencairkan kasbon baru.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        <table id="table-karyawan-kasbon" class="table-auto w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-widest">
                    <th class="py-4 px-4 w-12 text-center">No</th>
                    <th class="py-4 px-4">Nama Karyawan</th>
                    <th class="py-4 px-4">Jabatan</th>
                    <th class="py-4 px-4">Status Keuangan</th>
                    <th class="py-4 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
            </tbody>
        </table>
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