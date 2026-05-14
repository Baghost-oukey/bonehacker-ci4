<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="rekamMedisPage" class="w-full space-y-6 py-4 md:py-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data pasien dan rekam medis secara efisien
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 w-full md:w-auto md:flex md:items-center">
            <?php if (session()->get('role') === 'superadmin'): ?>
                <button type="button" data-modal-open="modalExport"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-file-export text-slate-500"></i>
                    <span>Export</span>
                </button>
            <?php endif; ?>

            <button type="button" data-modal-open="exampleModal"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                <span>Pasien</span>
            </button>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 md:px-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Data Pasien</h3>
                <p class="text-sm text-slate-500">Kelola data pasien dan rekam medis secara efisien</p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="w-full sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari pasien..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>


                </div>
            </div>
        </div>

        <!-- Mobile Card Container (KODE KITA) -->
        <div id="mobile-patient-container" class="md:hidden divide-y divide-slate-100 bg-white">
            <div class="p-12 text-center text-slate-400 italic text-sm">
                <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                Memuat data pasien...
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-RekamMedis" class="w-full text-sm hidden md:table">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">ID Pasien</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Wilayah</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Kunjungan Terakhir</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Jumlah Kunjungan</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data pasien...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
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
                    <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                        <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                    </div>
                </div>

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
            <i class="fas fa-info-circle mr-1"></i> Menampilkan data wilayah aktif. Gunakan kolom pencarian untuk menemukan pasien di <strong>seluruh cabang</strong>.
        </div>
    </div>
</section>

<!-- Modal Hapus -->
<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Yakin ingin menghapus data ini?</p>
        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
            <button id="confirmDelete"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Hapus</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Pasien -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Data Pasien</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data"
            class="space-y-4 p-5" novalidate id="formTambahPasien">
            <?= csrf_field() ?>
            <input type="hidden" name="desa_nama" id="desa_nama">
            <input type="hidden" name="kecamatan_id" id="kecamatan_id">
            <input type="hidden" name="kecamatan_nama" id="kecamatan_nama">
            <input type="hidden" name="kabupaten_id" id="kabupaten_id">
            <input type="hidden" name="kabupaten_nama" id="kabupaten_nama">
            <input type="hidden" name="provinsi_id" id="provinsi_id">
            <input type="hidden" name="provinsi_nama" id="provinsi_nama">

            <div class="max-h-[60vh] space-y-4 overflow-y-auto pr-1">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required autofocus
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                        <div class="invalid-feedback mt-1 hidden items-center gap-1.5 text-xs text-red-500">
                            <i class="fas fa-exclamation-circle"></i> Nama tidak boleh kosong
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jenis Kelamin <span
                                class="text-red-500">*</span></label>
                        <select name="gender" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                            <option value="">-- Pilih --</option>
                            <option value="Man">Laki-laki</option>
                            <option value="Woman">Perempuan</option>
                        </select>
                        <div class="invalid-feedback mt-1 hidden items-center gap-1.5 text-xs text-red-500">
                            <i class="fas fa-exclamation-circle"></i> Jenis kelamin tidak boleh kosong
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Pasien Rentan?</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_suspective" id="isSuspectiveCheckbox" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all relative">
                        </div>
                        <span class="text-sm text-slate-600">Ya</span>
                    </label>
                </div>

                <div id="keterangan_rentan" class="hidden space-y-1">
                    <label class="text-sm font-medium text-slate-700">Keterangan Rentan</label>
                    <textarea name="ket_rentan" id="ket_rentan" rows="2"
                        placeholder="Sebutkan alasan atau kondisi rentan..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Domestik</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="domestic" value="dalam_negeri"
                                class="text-teal-600 focus:ring-teal-500" checked>
                            <span class="text-sm text-slate-600">Dalam Negeri</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="domestic" value="luar_negeri"
                                class="text-teal-600 focus:ring-teal-500">
                            <span class="text-sm text-slate-600">Luar Negeri</span>
                        </label>
                    </div>
                </div>

                <div id="desa-group" class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Pencarian Desa</label>
                    <select name="desa_id" id="desa_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">Temukan Desa</option>
                    </select>
                </div>

                <div id="region-group" class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Wilayah / Cabang</label>
                    <?php $sess_region_id = session()->get('region_id'); ?>
                    <?php if ($sess_role === 'terapis'): ?>
                        <input type="text"
                            class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600"
                            value="<?= esc($sess_region_name) ?>" readonly>
                        <input type="hidden" name="region_id" value="<?= esc($sess_region_id) ?>">
                    <?php else: ?>
                        <select name="region_id" id="region_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                            <option value="">-- PILIH --</option>
                            <?php foreach ($wilayah as $value): ?>
                                <?php $active_id = session()->get('active_region'); ?>
                                <option value="<?= $value->id ?>" <?= $value->id == $active_id ? 'selected' : '' ?>>
                                    <?= esc($value->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback mt-1 hidden items-center gap-1.5 text-xs text-red-500">
                            <i class="fas fa-exclamation-circle"></i> Wilayah cabang wajib dipilih
                        </div>
                    <?php endif; ?>
                </div>

                <div id="country-group" class="hidden space-y-1">
                    <label class="text-sm font-medium text-slate-700">Pilih Negara</label>
                    <select name="country_id" id="country_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">PILIH NEGARA</option>
                        <?php foreach ($negara as $value): ?>
                            <option value="<?= $value->id ?>"><?= esc($value->country) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Umur</label>
                        <input type="number" name="age"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">No. WhatsApp</label>
                        <input type="number" id="phone" name="phone" placeholder="0812xxxxx"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Alamat Jalan</label>
                    <textarea name="address" rows="2" placeholder="Nama jalan, No. Rumah, RT/RW..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Upload Files & Pictures</label>
                    <input type="file" name="userfiles[]" id="userfiles" multiple onchange="previewFiles()"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer">
                    <div id="file-previews" class="mt-2 flex flex-wrap gap-2"></div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Sumber Informasi</label>
                        <select name="patient_information"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">Pilih Sumber</option>
                            <?php foreach ($resources as $value): ?>
                                <option value="<?= $value->id ?>"><?= esc($value->nama) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tanggal Kedatangan <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="visit_date" required
                            value="<?= date('Y-m-d\TH:i') ?>"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <div class="invalid-feedback mt-1 hidden items-center gap-1.5 text-xs text-red-500">
                            <i class="fas fa-exclamation-circle"></i> Tanggal kedatangan wajib diisi
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="submitBtn"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan
                    Pasien</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Export -->
<div id="modalExport" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Export Laporan Pasien</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('patient/export_data') ?>" method="GET" target="_blank" class="space-y-4 p-5">
            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Periode Laporan</label>
                    <select id="periodeSelect" name="periode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        <option value="today">Hari Ini</option>
                        <option value="last_7_days">7 Hari Terakhir</option>
                        <option value="this_month" selected>Bulan Ini</option>
                        <option value="custom">Rentang Tanggal...</option>
                    </select>
                </div>

                <div id="customDateRange" class="hidden grid grid-cols-2 gap-4 rounded-lg bg-slate-50 p-3 border border-slate-200">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500">Dari Tanggal</label>
                        <input type="date" name="start_date" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Pilih Wilayah</label>
                <select name="region_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua Wilayah</option>
                    <?php if (!empty($wilayah)): ?>
                        <?php foreach ($wilayah as $r): ?>
                            <option value="<?= $r->id ?>"><?= esc($r->name) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Format Laporan</label>
                <select name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="excel">Microsoft Excel (.xlsx)</option>
                    <option value="pdf">PDF Document (.pdf)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Unduh
                    Sekarang</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.rekamMedisConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('patients/fetch2') ?>",
        checkPhoneUrl: "<?= site_url('patient/check_phone') ?>",
        destroyBaseUrl: "<?= site_url('patient/destroy') ?>",
        dashboardUrl: "<?= site_url('dashboard') ?>",
        isSuperadmin: <?= isset($role) && $role === 'superadmin' ? 'true' : 'false' ?>,
        filterRegion: <?= json_encode($filter_region) ?>
    };
</script>

<?= $this->endSection() ?>