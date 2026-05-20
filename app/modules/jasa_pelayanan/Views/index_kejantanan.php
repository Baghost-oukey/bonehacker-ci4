<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="jasaPelayananPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Laporan komisi jasa pelayanan kejantanan per hari untuk terapis
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

    <!-- DROPDOWN NAVIGASI MOBILE -->
    <div class="w-full lg:hidden">
        <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
            <option value="<?= base_url('jasa-pelayanan/reguler') ?>">🌸 Pasien Reguler</option>
            <option value="<?= base_url('jasa-pelayanan/kejantanan') ?>" selected>🔥 Pasien Kejantanan</option>
        </select>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Data Jaspel Kejantanan Per Hari</h3>
                <p class="text-sm text-slate-500">Komisi dibagikan kepada terapis yang hadir — hanya pasien dengan terapi kejantanan aktif</p>
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

        <!-- Mobile Card Container -->
        <div id="mobile-jaspel-container" class="md:hidden divide-y divide-slate-100 bg-white">
            <div class="p-12 text-center text-slate-400 italic text-sm">
                <i class="fas fa-info-circle mr-2 text-slate-300"></i>
                Pilih cabang dan bulan, lalu klik "Tampilkan"
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-JaspelKejantanan" class="w-full text-sm hidden md:table">
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

        <!-- PAGINATION & INFO -->
        <div id="paginationContainer" style="display: none;" class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- LEFT: SHOW ENTRIES & INFO -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <!-- SHOW ENTRIES -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-slate-600">Tampilkan</label>
                        <select id="paginationLength"
                            class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs font-medium text-slate-600">data per halaman</span>
                    </div>

                    <!-- INFO TEXT -->
                    <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                        <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                    </div>
                </div>

                <!-- RIGHT: PAGINATION BUTTONS -->
                <div class="flex items-center justify-center gap-1.5 sm:justify-end">
                    <button id="paginationPrev"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left text-xs mr-1"></i>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>
                    <div id="paginationNumbers" class="flex items-center gap-1"></div>
                    <button id="paginationNext"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="hidden sm:inline">Berikutnya</span>
                        <i class="fas fa-chevron-right text-xs ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            <i class="fas fa-info-circle mr-1"></i>
            Hanya menghitung pasien yang rekam medisnya memiliki terapi kejantanan aktif dan sudah selesai
        </div>
    </div>

    <!-- Daftar Pengaturan Kejantanan -->
    <?php if (!empty($settings_kejantanan)): ?>
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center gap-3">
            <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-700">Kejantanan</span>
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Pengaturan Jaspel Kejantanan</h3>
                <p class="text-sm text-slate-500">Nominal untuk terapi kejantanan per cabang</p>
            </div>
        </div>
        <!-- Desktop view -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">Cabang</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Nominal/Pasien</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Jumlah Terapis</th>
                        <?php if (in_array($sess_role, ['superadmin', 'owner'])): ?>
                            <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($settings_kejantanan as $setting): ?>
                        <?php $jumlahTerapis = count(json_decode($setting->terapis_ids, true) ?? []); ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium"><?= esc($setting->region_name) ?></td>
                            <td class="px-6 py-4 text-right font-semibold text-purple-600">
                                Rp <?= number_format($setting->nominal_per_pasien, 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-center"><?= $jumlahTerapis ?> orang</td>
                            <?php if (in_array($sess_role, ['superadmin', 'owner'])): ?>
                                <td class="px-6 py-4 text-center">
                                    <a href="<?= site_url('jasa-pelayanan/settings') ?>?region_id=<?= $setting->region_id ?>&tipe=kejantanan"
                                        class="inline-flex items-center gap-1 rounded-lg bg-purple-50 px-3 py-1.5 text-xs font-medium text-purple-600 hover:bg-purple-100">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Mobile view -->
        <div class="block md:hidden divide-y divide-slate-100">
            <?php foreach ($settings_kejantanan as $setting): ?>
                <?php $jumlahTerapis = count(json_decode($setting->terapis_ids, true) ?? []); ?>
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-800"><?= esc($setting->region_name) ?></span>
                        <?php if (in_array($sess_role, ['superadmin', 'owner'])): ?>
                            <a href="<?= site_url('jasa-pelayanan/settings') ?>?region_id=<?= $setting->region_id ?>&tipe=kejantanan"
                                class="inline-flex items-center gap-1 rounded-lg bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-600 hover:bg-purple-100">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-slate-50 pt-2.5">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-slate-400 font-medium">Nominal/Pasien</span>
                            <span class="text-sm font-bold text-purple-600">Rp <?= number_format($setting->nominal_per_pasien, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-slate-400 font-medium">Terapis Berhak</span>
                            <span class="text-sm font-semibold text-slate-700"><?= $jumlahTerapis ?> orang</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        let table;
        let csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        $('#btnFilter').on('click', function() {
            const regionId = $('#regionFilter').val();
            const bulan = $('#monthFilter').val();

            if (!regionId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Informasi',
                    text: 'Pilih cabang terlebih dahulu',
                    confirmButtonColor: '#0d9488'
                });
                return;
            }

            if (!bulan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Informasi',
                    text: 'Pilih bulan terlebih dahulu',
                    confirmButtonColor: '#0d9488'
                });
                return;
            }

            if ($.fn.DataTable.isDataTable('#table-JaspelKejantanan')) {
                $('#table-JaspelKejantanan').DataTable().destroy();
            }

            table = $('#table-JaspelKejantanan').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                dom: 't',
                preXhr: function(e, settings, data) {
                    $('#mobile-jaspel-container').html('<div class="p-12 text-center text-slate-400 italic text-sm"><i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>Memuat data...</div>');
                },
                ajax: {
                    url: '<?= site_url('jasa-pelayanan/getJaspelKejantananPerHari') ?>',
                    type: 'POST',
                    data: function(d) {
                        d.region_id = regionId;
                        d.bulan = bulan;
                        d[csrfName] = csrfHash;
                    },
                    dataSrc: function(json) {
                        if (json.csrfHash) {
                            csrfHash = json.csrfHash;
                        }
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Terjadi kesalahan saat memuat data',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                },
                columns: [
                    { 
                        data: 'no', 
                        className: 'px-6 py-3.5 text-center' 
                    },
                    { 
                        data: 'tanggal',
                        className: 'px-6 py-3.5 text-left font-medium text-slate-800'
                    },
                    { 
                        data: 'total_pasien', 
                        className: 'px-6 py-3.5 text-center' 
                    },
                    { 
                        data: 'terapis_hadir', 
                        className: 'px-6 py-3.5 text-center' 
                    },
                    { 
                        data: 'nama_terapis',
                        className: 'px-6 py-3.5 text-left text-slate-600'
                    },
                    { 
                        data: 'total_jaspel', 
                        className: 'px-6 py-3.5 text-right font-semibold text-teal-600' 
                    },
                    { 
                        data: 'jaspel_per_terapis', 
                        className: 'px-6 py-3.5 text-right font-semibold text-emerald-600' 
                    }
                ],
                pageLength: parseInt($('#paginationLength').val(), 10) || 25,
                drawCallback: function(settings) {
                    var api = this.api();
                    var data = api.rows({ page: 'current' }).data();
                    var mContainer = $('#mobile-jaspel-container');
                    mContainer.empty();
                    
                    var info = api.page.info();
                    
                    if (data.length === 0) {
                        mContainer.append('<div class="p-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>Tidak ada data untuk ditampilkan</div>');
                        $('#paginationContainer').hide();
                        return;
                    }
                    
                    $('#paginationContainer').show();
                    
                    data.each(function(row) {
                        var cardHtml = `
                            <div class="p-4 space-y-4 bg-white border-b border-slate-100">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 font-mono text-sm border border-slate-200 shadow-sm mt-0.5">
                                            #${row.no}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-[15px] font-black text-slate-900 leading-tight">${row.tanggal}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Terapis: ${row.nama_terapis || '-'}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end shrink-0">
                                        <div class="flex items-center gap-1.5 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-100">
                                             <span class="text-[11px] font-black text-teal-600">${row.total_pasien}</span>
                                             <span class="text-[9px] font-bold text-teal-400 uppercase tracking-tighter">Pasien</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 bg-slate-50/50 rounded-xl p-3 border border-slate-100">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Total Jaspel</span>
                                        <span class="text-[11px] text-teal-600 font-black">${row.total_jaspel}</span>
                                    </div>
                                    <div class="flex flex-col gap-1 border-l border-slate-200 pl-3">
                                        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Jaspel / Terapis</span>
                                        <span class="text-[11px] text-emerald-600 font-black">${row.jaspel_per_terapis}</span>
                                    </div>
                                </div>
                                
                                <div class="text-[10px] text-slate-400 font-semibold flex items-center gap-1.5">
                                    <i class="fas fa-users text-slate-300"></i>
                                    <span>Terapis Hadir: <span class="text-slate-600 font-bold">${row.terapis_hadir} Orang</span></span>
                                </div>
                            </div>
                        `;
                        mContainer.append(cardHtml);
                    });

                    // Update Pagination Info
                    var start = info.start + 1;
                    var end = Math.min(info.start + info.length, info.recordsDisplay);
                    if (info.recordsDisplay <= 0) {
                        $('#paginationInfo').text("Menampilkan 0 sampai 0 dari 0 data");
                    } else {
                        $('#paginationInfo').text(`Menampilkan ${start} sampai ${end} dari ${info.recordsDisplay} data`);
                    }

                    // Update Previous and Next buttons state
                    $('#paginationPrev').prop('disabled', info.page <= 0);
                    $('#paginationNext').prop('disabled', info.page >= info.pages - 1);

                    // Update Page Numbers UI
                    var totalPages = info.pages;
                    var currentPage = info.page + 1;
                    var numbersContainer = $('#paginationNumbers');
                    numbersContainer.empty();

                    var startPage = Math.max(1, currentPage - 2);
                    var endPage = Math.min(totalPages, currentPage + 2);

                    if (startPage > 1) {
                        numbersContainer.append(`<button class="pagination-page-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>`);
                        if (startPage > 2) {
                            numbersContainer.append('<span class="px-1 text-slate-300">...</span>');
                        }
                    }

                    for (var pageNum = startPage; pageNum <= endPage; pageNum++) {
                        var activeClass = pageNum === currentPage
                            ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
                            : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
                        numbersContainer.append(`<button class="pagination-page-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${activeClass} text-xs" data-page="${pageNum}">${pageNum}</button>`);
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            numbersContainer.append('<span class="px-1 text-slate-300">...</span>');
                        }
                        numbersContainer.append(`<button class="pagination-page-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="${totalPages}">${totalPages}</button>`);
                    }
                }
            });
        });

        // Pagination Events Setup
        $('#paginationLength').on('change', function() {
            if (table) {
                table.page.len(parseInt($(this).val(), 10)).draw();
            }
        });

        $(document).on('click', '.pagination-page-btn', function() {
            var pageNum = parseInt($(this).data('page'), 10) - 1;
            if (table && !isNaN(pageNum)) {
                table.page(pageNum).draw('page');
            }
        });

        $('#paginationPrev').on('click', function() {
            if (table) {
                table.page('previous').draw('page');
            }
        });

        $('#paginationNext').on('click', function() {
            if (table) {
                table.page('next').draw('page');
            }
        });

        // Auto load untuk non-superadmin
        <?php if ($sess_role !== 'superadmin' && $sess_region_id !== 'all'): ?>
            $('#btnFilter').trigger('click');
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>


