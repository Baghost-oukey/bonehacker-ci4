<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="rekamMedisPage" class="w-full space-y-6 p-4 md:p-6">
    <?php
    $sess_role = session()->get('role');
    $sess_active_id = session()->get('active_region');
    $sess_active_name = session()->get('active_region_name');
    $sess_region_name = session()->get('region_name');
    $sess_patient_id = session()->get('region_patient');
    $val_id = 'all';

    if ($sess_role === 'user') {
        $val_id = is_array($sess_patient_id) ? $sess_patient_id[0] : $sess_patient_id;
    } else {
        $val_id = $sess_active_id ? $sess_active_id : 'all';
    }

    $region_label = $sess_role === 'user'
        ? $sess_region_name
        : (($sess_active_id === 'all' || !$sess_active_id) ? 'Semua Wilayah' : $sess_active_name);
    ?>

    <input type="hidden" id="region" value="<?= esc($val_id) ?>">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= is_array($title) ? 'Daftar Pasien' : esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data pasien dan rekam medis secara efisien
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <?php if (isset($role) && $role === 'superadmin'): ?>
                <button type="button" data-modal-open="modalExport"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-file-export text-slate-500"></i>
                    Export Data
                </button>
            <?php endif; ?>

            <button type="button" data-modal-open="exampleModal"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Pasien
            </button>
        </div>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Data Pasien</h3>
                <p class="text-sm text-slate-500">Wilayah aktif: <?= esc($region_label) ?></p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1 sm:flex-none sm:w-80">
                    <input type="text" id="searchInput" placeholder="Cari pasien..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-1" class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">ID Pasien</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Wilayah</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Kunjungan Terakhir</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Jumlah Kunjungan</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Aksi</th>
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
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-300">
                        <i class="fas fa-chevron-left text-xs mr-1"></i>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>
                    <div id="paginationNumbers" class="flex items-center gap-1"></div>
                    <button id="paginationNext"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-300">
                        <span class="hidden sm:inline">Berikutnya</span>
                        <i class="fas fa-chevron-right text-xs ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            Data ditampilkan berdasarkan filter wilayah pengguna
        </div>
    </div>
</section>

<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Yakin ingin menghapus data ini?</p>
        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
            <button id="confirmDelete"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Hapus
            </button>
        </div>
    </div>
</div>

<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Data Pasien</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data"
            class="space-y-4 p-5 needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-1">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="name" required autofocus>
                    <div class="invalid-feedback">Nama tidak boleh kosong</div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jenis Kelamin</label>
                        <select
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            name="gender" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Man">Laki-laki</option>
                            <option value="Woman">Perempuan</option>
                        </select>
                        <div class="invalid-feedback">Jenis kelamin tidak boleh kosong</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-slate-700">Pasien Rentan</div>
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_suspective" id="isSuspectiveCheckbox">
                            <span>YA</span>
                        </label>
                    </div>
                </div>

                <div id="keterangan_rentan" class="hidden space-y-1">
                    <label class="text-sm font-medium text-slate-700">Keterangan Rentan</label>
                    <textarea id="ket_rentan"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        name="ket_rentan" rows="4" placeholder="Masukkan keterangan rentan di sini..."></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Domestik</label>
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="radio" name="domestic" value="dalam_negeri" checked>
                            Dalam Negeri
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="radio" name="domestic" value="luar_negeri">
                            Luar Negeri
                        </label>
                    </div>
                </div>

                <div class="hidden space-y-1" id="country-group">
                    <label class="text-sm font-medium text-slate-700">Negara</label>
                    <select class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="country_id"
                        id="country_id">
                        <option value="">PILIH</option>
                        <?php foreach ($negara as $value): ?>
                            <option value="<?= $value->id ?>"><?= $value->country ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1" id="desa-group">
                    <label class="text-sm font-medium text-slate-700" for="desa_id">Pencarian Desa</label>
                    <select class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="desa_id"
                        id="desa_id" style="width: 100%;">
                        <option value="">Temukan Desa</option>
                    </select>
                </div>

                <div class="space-y-1" id="region-group">
                    <label class="text-sm font-medium text-slate-700">Wilayah</label>
                    <?php
                    $sess_region_id = session()->get('region_id');
                    ?>

                    <?php if ($sess_role === 'user'): ?>
                        <input type="text"
                            class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600"
                            value="<?= esc($sess_region_name) ?>" readonly>
                        <input type="hidden" name="region_id" value="<?= esc($sess_region_id) ?>">
                    <?php else: ?>
                        <select class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="region_id"
                            id="region_id" required>
                            <option value="">PILIH</option>
                            <?php foreach ($wilayah as $value): ?>
                                <?php
                                $active_id = session()->get('active_region');
                                $selected = $value->id == $active_id ? 'selected' : '';
                                ?>
                                <option value="<?= $value->id ?>" <?= $selected ?>><?= $value->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Umur</label>
                        <input type="number" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            name="age" minlength="1" maxlength="2">
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">No. Telepon/WhatsApp</label>
                        <input type="number" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            id="phone" name="phone" minlength="10" maxlength="14">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Alamat Jalan</label>
                    <textarea rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        name="address"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Upload Files & Pictures</label>
                    <input type="file" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        name="userfiles[]" id="userfiles" multiple onchange="previewFiles()">
                    <div id="file-previews" class="mt-3"></div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Sumber Informasi</label>
                    <select class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        name="patient_information">
                        <option value="">Pilih Sumber</option>
                        <?php foreach ($resources as $value): ?>
                            <option value="<?= $value->id ?>" <?= isset($patient_information) && $patient_information == $value->id ? 'selected' : '' ?>>
                                <?= $value->nama ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Tanggal Kedatangan</label>
                    <input type="datetime-local" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        name="visit_date" required>
                    <div class="invalid-feedback">Tanggal kedatangan tidak boleh kosong</div>
                </div>

                <input type="hidden" name="desa_nama" id="desa_nama">
                <input type="hidden" name="kecamatan_id" id="kecamatan_id">
                <input type="hidden" name="kecamatan_nama" id="kecamatan_nama">
                <input type="hidden" name="kabupaten_id" id="kabupaten_id">
                <input type="hidden" name="kabupaten_nama" id="kabupaten_nama">
                <input type="hidden" name="provinsi_id" id="provinsi_id">
                <input type="hidden" name="provinsi_nama" id="provinsi_nama">
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="submitBtn"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalExport" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Export Laporan Pasien</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('patient/export_data') ?>" method="GET" target="_blank" class="space-y-4 p-5">
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Pilih Rentang Tanggal</label>
                <input type="text" name="date_range" id="export_date"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    placeholder="Pilih Periode Laporan">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Pilih Wilayah</label>
                <select name="region_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm select2"
                    style="width: 100%;">
                    <option value="">Semua Wilayah</option>
                    <?php if (!empty($wilayah)): ?>
                        <?php foreach ($wilayah as $r): ?>
                            <option value="<?= $r->id ?>"><?= $r->name ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Format Laporan</label>
                <select name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="excel">Export ke Microsoft Excel (.xlsx)</option>
                    <option value="pdf">Export ke PDF (.pdf)</option>
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
        isSuperadmin: <?= isset($role) && $role === 'superadmin' ? 'true' : 'false' ?>
    };
</script>

<?= $this->endSection() ?>