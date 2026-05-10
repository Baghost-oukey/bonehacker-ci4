<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="antreanPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data pasien dan antrean secara efisien
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <?php
            $regionQuery = '';
            if (isset($regions_patient) && !empty($regions_patient)) {
                $val = is_array($regions_patient) ? implode(',', $regions_patient) : $regions_patient;
                $regionQuery = '?region=' . $val;
            }
            ?>
            <a href="<?= site_url('antrean/daftar-antrean') . $regionQuery ?>" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-sync-alt text-slate-500"></i>
                Lihat Antrean
            </a>

            <button type="button" data-modal-open="exampleModal"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Pasien
            </button>
        </div>
    </div>

    <!-- TABLE 1 HALAMAN ANTREAN -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Data Antrean</h3>
                <p class="text-sm text-slate-500">Monitoring dan pengelolaan antrean pasien secara real-time</p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                        <div class="relative flex-1">
                            <i id="iconSearch1" class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm transition-all"></i>
                            <i id="iconSpinner1" class="fas fa-circle-notch fa-spin absolute left-3 top-1/2 -translate-y-1/2 text-teal-600 text-sm transition-all" style="display: none;"></i>
                            <input type="text" id="searchInput" placeholder="Cari pasien..."
                                class="w-full rounded-lg border border-slate-200 pl-9 pr-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                        </div>

                    <div class="flex items-center gap-2 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-slate-400 text-base"></i>
                            <input type="date" id="startDate"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                        <span class="text-slate-300">-</span>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-check text-slate-400 text-base"></i>
                            <input type="date" id="endDate"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if (in_array(session()->get('role'), ['superadmin', 'owner', 'admin'])): ?>
                        <button id="btnPdf"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 hover:border-red-300">
                            <i class="fas fa-file-pdf text-sm"></i>
                            <span class="hidden sm:inline">PDF</span>
                        </button>

                        <button id="btnExcel"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-300">
                            <i class="fas fa-file-excel text-sm"></i>
                            <span class="hidden sm:inline">Excel</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-1" class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-center font-semibold">Antrean</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Usia</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-center font-semibold">
                            <?= session()->get('role') === 'superadmin' ? 'Aksi' : 'Keterangan' ?>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data antrean...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            Data ditampilkan berdasarkan filter tanggal dan wilayah pengguna
        </div>
    </div>
</section>


<!-- DELETE MODAL -->
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

<!-- TABLE 2 MODAL -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Pasien Ke Antrian</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <div class="p-5">
            <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
                <div class="relative flex-1">
                    <i id="iconSearch2" class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm transition-all"></i>
                    <i id="iconSpinner2" class="fas fa-circle-notch fa-spin absolute left-3 top-1/2 -translate-y-1/2 text-teal-600 text-sm hidden transition-all"></i>
                    <input type="text" id="searchPatientList" placeholder="Ketik Nama atau Nomor WhatsApp..."
                        class="w-full rounded-lg border border-slate-300 pl-9 pr-4 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <p class="mt-1.5 text-[11px] text-slate-400">
                        <i class="fas fa-info-circle mr-1"></i> Pencarian otomatis mencakup seluruh wilayah/cabang.
                    </p>
                </div>

                <button type="button" data-modal-open="modalnewpatient"
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 whitespace-nowrap">
                    <i class="fas fa-user-plus"></i>
                    Pasien Baru
                </button>
            </div>

            <div class="max-h-[50vh] overflow-y-auto border border-slate-200 rounded-xl">
                <table id="table-2" class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">No</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama</th>
                            <th class="px-4 py-3 text-left font-semibold">Alamat</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="patientListBody" class="divide-y divide-slate-100 bg-white"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ADD PASIEN MODAL -->
<div id="modalnewpatient" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Registrasi Pasien Baru</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data"
            class="space-y-4 p-5 needs-validation" novalidate id="formTambahPasien">
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
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama tidak boleh kosong</div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jenis Kelamin <span
                                class="text-red-500">*</span></label>
                        <select name="gender" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">-- Pilih --</option>
                            <option value="Man">Laki-laki</option>
                            <option value="Woman">Perempuan</option>
                        </select>
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Jenis kelamin tidak boleh kosong</div>
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
                    <textarea id="ket_rentan" name="ket_rentan" rows="2"
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

                <div id="country-fields" class="hidden space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Pilih Negara</label>
                        <select name="country_id" id="country_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">PILIH NEGARA</option>
                            <?php foreach ($negara as $value): ?>
                                <option value="<?= $value->id ?>"><?= esc($value->country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="local-fields" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Desa Asal Pasien</label>
                        <select name="desa_id" id="desa_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">Temukan Desa</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Pasien Cabang</label>
                        <?php if (session()->get('role') === 'user'): ?>
                            <input type="text"
                                class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600"
                                value="<?= esc(session()->get('region_name')) ?>" readonly>
                            <input type="hidden" name="region_id"
                                value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                        <?php else: ?>
                            <select name="region_id" id="region_id_new" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <option value="">-- PILIH --</option>
                                <?php foreach ($wilayah as $v): ?>
                                    <option value="<?= $v->id ?>"><?= esc($v->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Umur</label>
                        <input type="number" name="age"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">No. WhatsApp</label>
                        <input type="number" id="phone_new" name="phone" placeholder="0812xxxxx"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                </div>

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

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Tanggal Kedatangan <span
                            class="text-red-500">*</span></label>
                    <input type="datetime-local" name="visit_date" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        value="<?= date('Y-m-d\TH:i') ?>">
                    <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Tanggal kedatangan wajib diisi</div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" id="submitBtnNew"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan
                    Pasien</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.antreanConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= site_url('antrean/fetchDataTable') ?>",
        fetchPatientUrl: "<?= site_url('antrean/fetchPatientDataTables') ?>",
        deleteBaseUrl: "<?= site_url('antrean/destroy') ?>",
        pdfUrl: "<?= site_url('antrean/print_pdf_antrean') ?>",
        excelUrl: "<?= site_url('antrean/export_excell_antrean') ?>",
        checkPhoneUrl: "<?= site_url('patient/check_phone') ?>",
        patientHistoryUrl: "<?= site_url('patient/show') ?>"
    };
</script>
<?= $this->endSection() ?>