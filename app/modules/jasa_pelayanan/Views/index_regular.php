<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="jasaPelayananPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Laporan komisi jasa pelayanan per hari untuk terapis
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <?php if (in_array($sess_role, ['superadmin', 'owner'])): ?>
                <a href="<?= site_url('jasa-pelayanan/settings') ?>"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-cog text-slate-500"></i>
                    Pengaturan Jaspel
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Data Jaspel Per Hari</h3>
                <p class="text-sm text-slate-500">Komisi dibagikan kepada terapis yang hadir berdasarkan presensi</p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <?php if ($sess_role === 'superadmin'): ?>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-slate-600">Cabang:</label>
                            <select id="regionFilter" class="rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                                <option value="">Pilih Cabang</option>
                                <?php foreach ($wilayah as $region): ?>
                                    <option value="<?= $region->id ?>" <?= ($region->id == $sess_region_id && $sess_region_id !== 'all') ? 'selected' : '' ?>>
                                        <?= esc($region->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="regionFilter" value="<?= $sess_region_id ?>">
                        <div class="text-sm font-medium text-slate-700">
                            <i class="fas fa-map-marker-alt text-teal-600 mr-2"></i>
                            Cabang: <span class="font-semibold"><?= esc($sess_region_name) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-slate-600">Bulan:</label>
                        <input type="month" id="monthFilter" value="<?= $current_month ?>"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>

                    <button type="button" id="btnFilter"
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        <i class="fas fa-filter"></i>
                        Tampilkan
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-JaspelPerHari" class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-center font-semibold w-12">No</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Total Pasien</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Terapis Hadir</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Terapis</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Total Jaspel</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Jaspel/Terapis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-info-circle mr-2 text-slate-300"></i>
                            Pilih cabang dan bulan, lalu klik "Tampilkan"
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            <i class="fas fa-info-circle mr-1"></i>
            Komisi dibagikan hanya kepada terapis yang hadir sesuai presensi kehadiran
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        let table;

        $('#btnFilter').on('click', function() {
            const regionId = $('#regionFilter').val();
            const bulan = $('#monthFilter').val();

            if (!regionId) {
                alert('Pilih cabang terlebih dahulu');
                return;
            }

            if (!bulan) {
                alert('Pilih bulan terlebih dahulu');
                return;
            }

            // Destroy existing table if exists
            if ($.fn.DataTable.isDataTable('#table-JaspelPerHari')) {
                $('#table-JaspelPerHari').DataTable().destroy();
            }

            // Initialize DataTable
            table = $('#table-JaspelPerHari').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= site_url('jasa-pelayanan/getJaspelPerHari') ?>',
                    type: 'POST',
                    data: function(d) {
                        d.region_id = regionId;
                        d.bulan = bulan;
                        d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                    },
                    dataSrc: function(json) {
                        // Update CSRF token
                        if (json.csrfHash) {
                            $('input[name="<?= csrf_token() ?>"]').val(json.csrfHash);
                        }
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data');
                    }
                },
                columns: [{
                        data: 'no',
                        className: 'text-center'
                    },
                    {
                        data: 'tanggal'
                    },
                    {
                        data: 'total_pasien',
                        className: 'text-center'
                    },
                    {
                        data: 'terapis_hadir',
                        className: 'text-center'
                    },
                    {
                        data: 'nama_terapis'
                    },
                    {
                        data: 'total_jaspel',
                        className: 'text-right font-semibold text-teal-600'
                    },
                    {
                        data: 'jaspel_per_terapis',
                        className: 'text-right font-semibold text-emerald-600'
                    }
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Memuat data...',
                    lengthMenu: 'Tampilkan _MENU_ data per halaman',
                    zeroRecords: 'Tidak ada data untuk ditampilkan',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    search: 'Cari:',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Selanjutnya',
                        previous: 'Sebelumnya'
                    }
                },
                order: [
                    [1, 'desc']
                ] // Sort by tanggal descending
            });
        });

        // Auto load if region is already selected (for non-superadmin)
        <?php if ($sess_role !== 'superadmin' && $sess_region_id !== 'all'): ?>
            $('#btnFilter').trigger('click');
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>