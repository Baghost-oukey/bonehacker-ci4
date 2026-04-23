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
            <a href="<?= site_url('antrean/daftar-antrean') . $regionQuery ?>"
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

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <!-- HEADER -->
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <!-- TITLE SECTION -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    Data Antrean
                </h3>
                <p class="text-sm text-slate-500">
                    Monitoring dan pengelolaan antrean pasien secara real-time
                </p>
            </div>

            <!-- FILTERS & ACTIONS ROW -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- LEFT: SEARCH & DATE RANGE -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <!-- SEARCH -->
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari pasien..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>

                    <!-- DATE RANGE -->
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

                <!-- RIGHT: ACTION BUTTONS -->
                <div class="flex items-center gap-2">
                    <?php if (session()->get('role') === 'superadmin'): ?>
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

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table id="table-1" class="w-full text-sm">

                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Usia</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">No. WA</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-right font-semibold">
                            <?= session()->get('role') === 'superadmin' ? 'Aksi' : 'Keterangan' ?>
                        </th>
                    </tr>
                </thead>

                <!-- BODY -->
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

        <!-- PAGINATION & INFO -->
        <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
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

                </div>

                <!-- RIGHT: PAGINATION BUTTONS -->
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

        <!-- FOOTER -->
        <div class="border-t border-slate-100 bg-slate-50/30 px-6 py-3 text-xs text-slate-400">
            Data ditampilkan berdasarkan filter tanggal dan wilayah pengguna
        </div>

    </div>
</section>

<div id="modalDelete" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Yakin ingin menghapus data ini?</p>

        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close class="px-4 py-2 text-sm border rounded-lg">Batal</button>
            <button id="confirmDelete" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                Hapus
            </button>
        </div>
    </div>
</div>
<!-- END -->


<!-- MODAL TAMBAH PASIEN -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-6xl overflow-hidden rounded-[2.5rem] bg-white shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)] border border-white/20">
        <div class="relative flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-10 py-7">
            <div class="flex items-center gap-5">
                <!-- <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white shadow-xl shadow-indigo-200">
                    <i class="fas fa-hospital-user text-2xl"></i>
                </div> -->
                <div>
                    <h5 class="text-2xl font-black text-slate-800 tracking-tight">Tambah Pasien Ke Antrian</h5>
                    <p class="text-[13px] text-slate-500 font-semibold leading-relaxed">Pasien Yang ada Pasien Sudah terdaftar dan jika belum terdaftar silahkan <span class="text-indigo-600 font-bold underline cursor-pointer">daftarkan pasien Disni</span> Untuk Melanjutkannya.</p>
                </div>
            </div>

            <button type="button" data-modal-close class="group h-10 w-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 hover:bg-red-500 hover:text-white transition-all duration-300">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="px-10 py-6">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="relative flex-1 group">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                    <input type="text" id="searchPatientList" placeholder="Ketik Nama, NIK, atau ID Pasien..."
                        class="w-full rounded-2xl border-2 border-slate-300 bg-slate-50/50 pl-14 pr-6 py-4 text-sm font-bold outline-none transition-all focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 placeholder:text-slate-400">
                </div>

                <button type="button" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-teal-600 px-8 py-4 text-sm font-black text-white hover:bg-black transition-all shadow-lg active:scale-95" data-modal-open="modalnewpatient">
                    <i class="fas fa-user-plus"></i>
                    Masukkan Ke Antrian
                </button>
            </div>
        </div>

        <div class="px-10 pb-4">
            <div class="max-h-[50vh] overflow-y-auto custom-scrollbar border border-slate-100 rounded-3xl bg-slate-50/30">
                <table id="table-2" class="w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-20">
                        <tr class="bg-slate-100/90 backdrop-blur-sm shadow-sm">
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 rounded-tl-2xl">No</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Nama</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Alamat</th>
                            <th class="px-6 py-4 text-center text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Status Pasien</th>
                            <th class="px-6 py-4 text-right text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 rounded-tr-2xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="patientListBody" class="bg-white divide-y divide-slate-50">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/80 px-10 py-5 flex items-center justify-between">
            <div id="paginationInfoModal" class="text-xs font-bold text-slate-500"></div>
            <div id="paginationControlsModal" class="flex gap-2"></div>
        </div>
    </div>
</div>
<!-- END -->

<!-- MODAL NEW PASIEN -->
<div id="modalnewpatient" class="modal-wrapper hidden fixed inset-0 z-9999 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="w-full max-w-2xl overflow-hidden rounded-2rem bg-white shadow-2xl ring-1 ring-slate-200 border-white rounded-4xl">
        <div class="relative bg-linear-to-r from-slate-50 to-white px-8 py-6 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h5 class="text-xl font-black text-slate-800 tracking-tight uppercase">Registrasi Pasien</h5>
                    <p class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-1">Masukkan data rekam medis baru</p>
                </div>
                <button type="button" data-modal-close class="group flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 shadow-sm transition-all hover:bg-red-50 hover:border-red-100">
                    <span class="text-xl text-slate-400 group-hover:text-red-500 transition-colors">&times;</span>
                </button>
            </div>
        </div>

        <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data" class="flex flex-col bg-white needs-validation" novalidate id="formTambahPasien">
            <?= csrf_field() ?>
            <input type="hidden" name="desa_nama" id="desa_nama">
            <input type="hidden" name="kecamatan_id" id="kecamatan_id">
            <input type="hidden" name="kecamatan_nama" id="kecamatan_nama">
            <input type="hidden" name="kabupaten_id" id="kabupaten_id">
            <input type="hidden" name="kabupaten_nama" id="kabupaten_nama">
            <input type="hidden" name="provinsi_id" id="provinsi_id">
            <input type="hidden" name="provinsi_nama" id="provinsi_nama">

            <div class="max-h-[65vh] space-y-8 overflow-y-auto px-8 py-6 custom-scrollbar">

                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-l-4 border-teal-500 pl-3">
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Data Pasien</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Nama Lengkap</label>
                        <input type="text" name="name" required autofocus class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all">
                        <div class="invalid-feedback text-xs text-red-500 font-medium ml-1">Nama tidak boleh kosong</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Jenis Kelamin</label>
                            <select name="gender" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold cursor-pointer">
                                <option value="">-- Pilih --</option>
                                <option value="Man">Laki-laki</option>
                                <option value="Woman">Perempuan</option>
                            </select>
                            <div class="invalid-feedback text-xs text-red-500 font-medium ml-1">Jenis kelamin tidak boleh kosong</div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Pasien Rentan?</label>
                            <div class="flex items-center h-12.5">
                                <label class="relative inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" name="is_suspective" id="isSuspectiveCheckbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                                    <span class="ml-3 text-[10px] font-black text-slate-400 peer-checked:text-teal-600 transition-all uppercase">YA</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="keterangan_rentan" class="hidden animate-fade-down space-y-1.5 bg-red-50/30 p-4 rounded-2xl border border-red-100">
                    <label class="text-[11px] font-black text-red-500 uppercase ml-1">Detail Keterangan Rentan</label>
                    <textarea id="ket_rentan" name="ket_rentan" rows="2" placeholder="Sebutkan alasan atau kondisi rentan..." class="w-full rounded-xl border-red-200 bg-white px-4 py-3 text-sm font-semibold focus:border-red-400 transition-all"></textarea>
                </div>


                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-l-4 border-indigo-500 pl-3">
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Wilayah</span>
                    </div>

                    <div class="bg-slate-100 rounded-2xl p-1 flex gap-1 w-fit ring-1 ring-slate-200">
                        <label class="cursor-pointer">
                            <input type="radio" name="domestic" value="dalam_negeri" class="sr-only peer" checked>
                            <div class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-400 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-sm transition-all">Dalam Negeri</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="domestic" value="luar_negeri" class="sr-only peer">
                            <div class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-400 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-sm transition-all">Luar Negeri</div>
                        </label>
                    </div>

                    <div id="local-fields" class="space-y-4 animate-fade-down">
                        <div class="form-group" id="desa-group">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1" for="desa_id">Desa Asal Pasien</label>
                            <select name="desa_id" id="desa_id" class="w-full" style="width: 100%;">
                                <option value="">Temukan Desa</option>
                            </select>
                        </div>

                        <div class="space-y-1.5" id="region-group">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Pasien Cabang</label>
                            <?php $sess_region_id = session()->get('region_id'); ?>

                            <?php if ($role === 'user'): ?>
                                <input type="text" class="w-full rounded-xl border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 uppercase" value="<?= $sess_region_name ?>" readonly>
                                <input type="hidden" name="region_id" value="<?= is_array($sess_region_id) ? $sess_region_id[0] : $sess_region_id ?>">
                            <?php else: ?>
                                <select name="region_id" id="region_id_new" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold focus:border-teal-500 transition-all">
                                    <option value="">-- PILIH --</option>
                                    <?php foreach ($wilayah as $v): ?>
                                        <?php
                                        $active_id = session()->get('active_region');
                                        $selected = ($v->id == $active_id || (!empty($active_region) && $v->id == $active_region)) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $v->id ?>" <?= $selected ?>><?= $v->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="country-fields" class="hidden space-y-4 animate-fade-down bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Pilih Negara</label>
                            <select name="country_id" id="country_id" class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold">
                                <option value="">PILIH NEGARA</option>
                                <?php foreach ($negara as $value): ?>
                                    <option value="<?= $value->id ?>"><?= $value->country ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Umur</label>
                            <input type="number" name="age" minlength="1" maxlength="2" class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">No. WhatsApp</label>
                            <input type="number" id="phone_new" name="phone" minlength="10" maxlength="14" placeholder="0812xxxxx" class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Sumber Informasi</label>
                        <select class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold"
                            name="patient_information">
                            <option value="">Pilih Sumber</option>
                            <?php foreach ($resources as $value): ?>
                                <option value="<?= $value->id ?>" <?= isset($patient_information) && $patient_information == $value->id ? 'selected' : '' ?>>
                                    <?= $value->nama ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Alamat Jalan</label>
                        <textarea name="address" rows="2" placeholder="Nama jalan, No. Rumah, RT/RW..." class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold focus:border-teal-500 transition-all"></textarea>
                    </div>

                    <div class="space-y-1.5 border border-dashed border-slate-300 bg-slate-50 rounded-2xl p-4 transition-all hover:bg-slate-100 hover:border-teal-400">
                        <label class="text-[11px] font-black text-slate-500 uppercase ml-1 block mb-2">Upload Files & Pictures</label>
                        <input type="file" name="userfiles[]" id="userfiles" multiple onchange="previewFiles()"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer transition-all">
                        <div id="file-previews" class="mt-3 flex flex-wrap gap-2"></div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-teal-600 uppercase ml-1">Jadwal Kedatangan</label>
                        <input type="datetime-local" name="visit_date" required class="w-full rounded-xl border-teal-100 bg-teal-50/30 px-4 py-3 text-sm font-bold text-teal-700">
                        <div class="invalid-feedback text-xs text-red-500 font-medium ml-1">Tanggal kedatangan wajib diisi</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-8 py-6">
                <button type="button" data-modal-close class="px-6 py-2.5 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Batal</button>
                <button type="submit" id="submitBtnNew" class="bg-teal-600 px-10 py-3 rounded-xl text-[11px] font-black text-white uppercase tracking-widest hover:bg-teal-600 shadow-lg shadow-teal-900/20 transition-all active:scale-95">Simpan Pasien</button>
            </div>
        </form>
    </div>
</div>
<!-- END -->

<!-- TEMPLATE LOADING DATA -->
<div id="datatable-loader" class="hidden">
    <div class="fixed inset-0 z-10001 flex flex-col items-center justify-center p-4 pointer-events-none">
        <div class="flex flex-col items-center bg-white p-5 rounded-2xl shadow-xl border border-slate-100 animate-fade-up">
            <div class="relative flex h-10 w-10 items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
            </div>
            <span class="mt-3 text-[10px] font-black text-slate-500 tracking-widest uppercase">
                Memuat Data...
            </span>
        </div>

    </div>
</div>
<!-- END -->


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
        excelUrl: "<?= site_url('antrean/export_excell_antrean') ?>"
    };
</script>

<?= $this->endSection() ?>