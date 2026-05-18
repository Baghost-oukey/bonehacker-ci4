<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="antreanPage" class="w-full space-y-6 py-4 md:py-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data pasien dan antrean secara efisien
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 w-full md:w-auto md:flex md:items-center">
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

            <button type="button" id="btnBreakTime"
                class="inline-flex items-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-medium text-orange-700 transition hover:bg-orange-100">
                <i class="fas fa-coffee text-orange-500"></i>
                Istirahat
            </button>

            <button type="button" data-modal-open="exampleModal"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Pasien
            </button>
        </div>
    </div>

    <!-- TABLE & CARD VIEW ANTREAN -->
    <!-- TABLE 1 HALAMAN ANTREAN -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 md:px-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Data Antrean</h3>
                <p class="text-sm text-slate-500">Monitoring dan pengelolaan antrean pasien secara real-time</p>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <div class="relative flex-1">
                        <i id="iconSearch1" class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm transition-all"></i>
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

                <div class="grid grid-cols-2 gap-2 w-full md:w-auto md:flex md:items-center">
                    <?php if (session()->get('role') === 'superadmin'): ?>
                        <button id="btnPdf"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 hover:border-red-300">
                            <i class="fas fa-file-pdf text-sm"></i>
                            <span>PDF</span>
                        </button>

                        <button id="btnExcel"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-300">
                            <i class="fas fa-file-excel text-sm"></i>
                            <span>Excel</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="antrean-table-container">
            <!-- Mobile Cards (Shown on Mobile) -->
            <div id="mobile-card-container" class="md:hidden divide-y divide-slate-100 bg-white mb-6">
                <div class="p-12 text-center text-slate-400 italic text-sm">
                    <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                    Memuat data antrean...
                </div>
            </div>

            <!-- Table (Hidden on Mobile) -->
            <div class="overflow-x-auto">
                <table id="table-1" class="w-full text-sm hidden md:table">
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
        </div>

        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-[10px] text-slate-400 text-center md:text-left">
            Data disinkronkan secara real-time • Berdasarkan wilayah & filter
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
                class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Batal</button>
            <button id="confirmDelete"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Hapus</button>
        </div>
    </div>
</div>


<!-- TABLE 2 MODAL (PILIH PASIEN) -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-[60] flex flex-col items-center justify-end md:justify-center bg-black/40 p-0 md:p-4">
    <div class="w-full max-w-5xl overflow-hidden rounded-t-3xl md:rounded-3xl bg-white shadow-2xl flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white shrink-0">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Tambah Pasien Ke Antrian</h5>
            <button type="button" data-modal-close
                class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-6 overflow-hidden flex flex-col">
            <div class="flex flex-col gap-1.5 shrink-0">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1 w-full">
                        <i id="iconSearch2" class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="searchPatientList" placeholder="Ketik Nama atau Nomor WhatsApp..."
                            class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                    </div>
                    <button type="button" data-modal-open="modalnewpatient"
                        class="w-full md:w-auto px-6 py-3 rounded-xl bg-teal-600 text-white text-xs font-black tracking-widest uppercase hover:bg-teal-700 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fas fa-user-plus"></i> Pasien Baru
                    </button>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 mt-2">
                    <i class="fas fa-info-circle text-teal-500"></i> Pencarian otomatis mencakup seluruh wilayah/cabang.
                </p>
            </div>

            <div id="patient-selection-container" class="flex-1 overflow-hidden flex flex-col rounded-2xl border border-slate-100 shadow-inner bg-slate-50/30">
                <!-- Mobile Card List (Shown on Mobile) -->
                <div id="mobile-patient-list" class="md:hidden flex flex-col divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-y-auto max-h-[40vh] bg-white no-scrollbar mb-4">
                    <div class="p-8 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                        Memuat data pasien...
                    </div>
                </div>

                <!-- Desktop Table (Hidden on Mobile) -->
                <div class="overflow-y-auto overflow-x-auto w-full hidden md:block">
                    <table id="table-2" class="w-full text-left border-collapse bg-white relative">
                        <thead class="bg-white shadow-sm sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">NAMA</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ALAMAT</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">STATUS</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 text-sm">
                            <!-- DataTables akan mengisi ini -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
            <button type="button" data-modal-close class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Tutup</button>
        </div>
    </div>
</div>


<!-- ADD PASIEN MODAL (PASIEN BARU) -->
<div id="modalnewpatient" class="modal-wrapper hidden fixed inset-0 z-[70] items-center justify-center bg-black/40 p-4 text-left">
    <div class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-6 bg-white z-10">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Registrasi Pasien Baru</h5>
            <button type="button" data-modal-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"><i class="fas fa-times text-xl"></i></button>
        </div>

        <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data"
            class="space-y-5 p-6 needs-validation" novalidate id="formTambahPasien">
            <?= csrf_field() ?>
            <input type="hidden" name="desa_nama" id="desa_nama">
            <input type="hidden" name="kecamatan_id" id="kecamatan_id">
            <input type="hidden" name="kecamatan_nama" id="kecamatan_nama">
            <input type="hidden" name="kabupaten_id" id="kabupaten_id">
            <input type="hidden" name="kabupaten_nama" id="kabupaten_nama">
            <input type="hidden" name="provinsi_id" id="provinsi_id">
            <input type="hidden" name="provinsi_nama" id="provinsi_nama">

            <div class="max-h-[60vh] space-y-5 overflow-y-auto pr-1">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required autofocus
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama tidak boleh kosong</div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Jenis Kelamin <span
                                class="text-red-500">*</span></label>
                        <select name="gender" required
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                            <option value="">-- Pilih --</option>
                            <option value="Man">Laki-laki</option>
                            <option value="Woman">Perempuan</option>
                        </select>
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Jenis kelamin tidak boleh kosong</div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pasien Rentan?</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_suspective" id="isSuspectiveCheckbox" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all relative">
                        </div>
                        <span class="text-sm text-slate-600">Ya</span>
                    </label>
                </div>

                <div id="keterangan_rentan" class="hidden space-y-1">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Keterangan Rentan</label>
                    <textarea id="ket_rentan" name="ket_rentan" rows="2"
                        placeholder="Sebutkan alasan atau kondisi rentan..."
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner"></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Domestik</label>
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
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Negara</label>
                        <select name="country_id" id="country_id"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                            <option value="">PILIH NEGARA</option>
                            <?php foreach ($negara as $value): ?>
                                <option value="<?= $value->id ?>"><?= esc($value->country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="local-fields" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Desa Asal Pasien</label>
                        <select name="desa_id" id="desa_id"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                            <option value="">Temukan Desa</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pasien Cabang</label>
                        <?php if (session()->get('role') === 'user' && !empty(session()->get('terapis_id'))): ?>
                            <input type="text"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 bg-slate-100 cursor-not-allowed opacity-80"
                                value="<?= esc(session()->get('region_name')) ?>" readonly>
                            <input type="hidden" name="region_id"
                                value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
                        <?php else: ?>
                            <select name="region_id" id="region_id_new" required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                                <option value="">-- PILIH --</option>
                                <?php foreach ($wilayah as $v): ?>
                                    <option value="<?= $v->id ?>"><?= esc($v->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Umur</label>
                        <input type="number" name="age"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">No. WhatsApp</label>
                        <input type="number" id="phone_new" name="phone" placeholder="0812xxxxx"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Sumber Informasi</label>
                    <select name="patient_information"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                        <option value="">Pilih Sumber</option>
                        <?php foreach ($resources as $value): ?>
                            <option value="<?= $value->id ?>"><?= esc($value->nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Alamat Jalan</label>
                    <textarea name="address" rows="2" placeholder="Nama jalan, No. Rumah, RT/RW..."
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner"></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Upload Files & Pictures</label>
                    <input type="file" name="userfiles[]" id="userfiles" multiple onchange="previewFiles()"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer">
                    <div id="file-previews" class="mt-2 flex flex-wrap gap-2"></div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Tanggal Kedatangan <span
                            class="text-red-500">*</span></label>
                    <input type="datetime-local" name="visit_date" required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner"
                        value="<?= date('Y-m-d\TH:i') ?>">
                    <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Tanggal kedatangan wajib diisi</div>
                </div>
            </div>

            <div class="bg-slate-50 border-t border-slate-100 p-6 flex flex-col md:flex-row gap-3 justify-end">
                <button type="button" data-modal-close
                    class="w-full md:w-auto px-6 py-3.5 rounded-2xl border border-slate-200 text-slate-500 text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Batal</button>
                <button type="submit" id="submitBtnNew"
                    class="w-full md:flex-1 px-6 py-3.5 rounded-2xl bg-teal-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all">Simpan
                    Pasien</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL REKAM MEDIS -->
<style>
    /* Sembunyikan section riwayat kunjungan pasien di halaman antrean */
    #antreanPage #patientHistoryContainer {
        display: none !important;
    }
    
    /* Paksa warna antrean menjadi slate-800 agar tidak hijau */
    #table-1 thead th:first-child,
    #table-1 tbody td:first-child {
        color: #1e293b !important; /* slate-800 */
    }
    
    /* Header table default agar tidak hijau */
    #table-1 thead th {
        color: #475569 !important; /* slate-600 */
    }
</style>
<?= $this->include('App\modules\patients\Views\component\card_riwayat') ?>

<!-- Modal Istirahat -->
<div id="modalBreakTime" class="modal-wrapper hidden fixed inset-0 z-[60] items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-6 bg-white z-10">
            <h5 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-coffee text-orange-500"></i>
                Set Waktu Istirahat
            </h5>
            <button type="button" onclick="closeModal(document.getElementById('modalBreakTime'))" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            <?php if (session()->get('role') === 'superadmin'): ?>
            <div class="space-y-1.5">
                <label class="text-sm font-bold text-slate-700 uppercase tracking-wider">Pilih Wilayah</label>
                <select id="breakRegionId" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all bg-slate-50 font-bold text-slate-700">
                    <option value="">-- Pilih Wilayah --</option>
                    <?php foreach ($wilayah as $w): ?>
                        <option value="<?= $w->id ?>" <?= session()->get('active_region') == $w->id ? 'selected' : '' ?>><?= esc($w->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" id="breakRegionId" value="<?= is_array(session()->get('region_patient')) ? session()->get('region_patient')[0] : session()->get('region_patient') ?>">
            <?php endif; ?>

            <div class="space-y-4">
                <label class="text-sm font-bold text-slate-700 uppercase tracking-wider">Durasi Istirahat</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" data-duration="15" class="btn-duration border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:border-teal-500 hover:text-teal-600 transition-all flex flex-col items-center gap-1">
                        <span class="text-xl">15</span>
                        <span class="text-[10px] uppercase">Menit</span>
                    </button>
                    <button type="button" data-duration="30" class="btn-duration border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:border-teal-500 hover:text-teal-600 transition-all flex flex-col items-center gap-1">
                        <span class="text-xl">30</span>
                        <span class="text-[10px] uppercase">Menit</span>
                    </button>
                    <button type="button" data-duration="45" class="btn-duration border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:border-teal-500 hover:text-teal-600 transition-all flex flex-col items-center gap-1">
                        <span class="text-xl">45</span>
                        <span class="text-[10px] uppercase">Menit</span>
                    </button>
                    <button type="button" data-duration="60" class="btn-duration border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:border-teal-500 hover:text-teal-600 transition-all flex flex-col items-center gap-1">
                        <span class="text-xl">60</span>
                        <span class="text-[10px] uppercase">Menit</span>
                    </button>
                </div>
                
                <div class="relative pt-2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-clock text-slate-400"></i>
                    </div>
                    <input type="number" id="customDuration" placeholder="Atur menit lainnya..." 
                        class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all">
                </div>
            </div>

            <div class="pt-2">
                <button type="button" id="btnStartBreak" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-teal-600/30 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-play"></i>
                    Mulai Istirahat Sekarang
                </button>
                <button type="button" id="btnStopBreak" class="hidden w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl shadow-lg shadow-slate-800/30 transition-all flex items-center justify-center gap-2 mt-2">
                    <i class="fas fa-stop"></i>
                    Hentikan Istirahat
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalBreak = document.getElementById('modalBreakTime');
        const btnOpenBreak = document.getElementById('btnBreakTime');
        const btnStartBreak = document.getElementById('btnStartBreak');
        const btnStopBreak = document.getElementById('btnStopBreak');
        const durationButtons = document.querySelectorAll('.btn-duration');
        const customInput = document.getElementById('customDuration');
        let selectedDuration = null;

        if (btnOpenBreak) {
            btnOpenBreak.addEventListener('click', () => openModal(modalBreak));
        }

        durationButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                durationButtons.forEach(b => b.classList.remove('border-teal-500', 'text-teal-600', 'bg-teal-50'));
                this.classList.add('border-teal-500', 'text-teal-600', 'bg-teal-50');
                selectedDuration = this.dataset.duration;
                customInput.value = '';
            });
        });

        customInput.addEventListener('input', function() {
            if (this.value) {
                durationButtons.forEach(b => b.classList.remove('border-teal-500', 'text-teal-600', 'bg-teal-50'));
                selectedDuration = this.value;
            }
        });

        btnStartBreak.addEventListener('click', function() {
            const duration = selectedDuration || customInput.value;
            const regionId = document.getElementById('breakRegionId').value;

            if (!regionId) {
                Swal.fire('Peringatan', 'Silakan pilih wilayah terlebih dahulu.', 'warning');
                return;
            }
            if (!duration) {
                Swal.fire('Peringatan', 'Silakan pilih atau masukkan durasi istirahat.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= site_url("antrean/set-break") ?>',
                type: 'POST',
                data: {
                    region_id: regionId,
                    duration: duration,
                    status: 'start',
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil', res.message, 'success');
                        closeModal(modalBreak);
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            });
        });

        btnStopBreak.addEventListener('click', function() {
            const regionId = document.getElementById('breakRegionId').value;
            if (!regionId) return;

            $.ajax({
                url: '<?= site_url("antrean/set-break") ?>',
                type: 'POST',
                data: {
                    region_id: regionId,
                    status: 'stop',
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil', res.message, 'success');
                        closeModal(modalBreak);
                    }
                }
            });
        });
    });
</script>

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

    // Konfigurasi untuk modal rekam medis (digunakan oleh card_riwayat.php)
    window.patientConfig = {
        patientId: null, // Akan diisi saat buka modal
        queueId: null,
        patientRegionId: null,
        csrfTokenName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        urls: {
            historyFetchBase: '<?= site_url("history/fetch") ?>',
            historyFetch: '<?= site_url("history/fetch") ?>/',
            historyStore: '<?= site_url("history/store") ?>',
            historyDestroy: '<?= site_url("history/destroy") ?>',
            complaintTags: '<?= site_url("tag-keluhan/get_tags") ?>',
            medisTags: '<?= site_url("tag-rekam-medis/tags") ?>',
            resultTags: '<?= site_url("tag-pemeriksaan/get_tags") ?>',
            terapisByRegion: '<?= site_url("history/terapis-by-region") ?>'
        }
    };

    /**
     * Membuka langsung modal Tambah Riwayat Pasien dari halaman antrean
     * @param {HTMLElement} btn Tombol yang diklik (berisi data-medis)
     */
    function openMedicalRecordModal(btn) {
        const raw = btn.getAttribute('data-medis');
        if (!raw) return;

        let info;
        try {
            info = JSON.parse(raw);
        } catch (e) {
            console.error("Gagal memproses data medis:", e, raw);
            return;
        }

        const {
            patientId,
            historyId,
            queueId,
            regionId,
            patientName,
            patientAge,
            patientPhone,
            patientAddress
        } = info;

        // Update patientConfig supaya PatientHistoryPage menggunakan data yang benar
        window.patientConfig.patientId = patientId;
        window.patientConfig.queueId = queueId;
        window.patientConfig.patientRegionId = regionId;

        // Populate header info di modal
        const nameEl = document.getElementById('modal-patient-name');
        const ageEl = document.getElementById('modal-patient-age');
        const addressEl = document.getElementById('modal-patient-address');
        const phoneEl = document.getElementById('modal-patient-phone');
        if (nameEl) nameEl.textContent = patientName || '-';
        if (ageEl) ageEl.textContent = patientAge || '-';
        if (addressEl) addressEl.textContent = patientAddress || '-';
        if (phoneEl) phoneEl.textContent = patientPhone || '-';

        // Jika sudah ada history → buka detail, jika belum → buka tambah baru
        if (window.PatientHistoryPage) {
            // Set config terlebih dahulu
            if (window.PatientHistoryPage.config) {
                window.PatientHistoryPage.config.patientId = patientId;
                window.PatientHistoryPage.config.queueId = queueId;
                if (regionId) window.PatientHistoryPage.config.patientRegionId = regionId;
            }

            if (historyId) {
                // Ada history → edit/lihat detail
                window.PatientHistoryPage.show(historyId);
            } else {
                // Belum ada history → langsung tambah baru
                window.PatientHistoryPage.add();
            }
        }
    }
</script>
<?= $this->endSection() ?>