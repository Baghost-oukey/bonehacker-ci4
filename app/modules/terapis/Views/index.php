<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="terapisPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Kelola data terapis dan tenaga medis yang bertugas
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" data-modal-open="modalTambah"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                <i class="fas fa-plus-circle text-white"></i>
                Tambah Terapis
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <!-- HEADER -->
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <!-- TITLE SECTION -->
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">
                    Data Terapis
                </h3>
                <p class="text-sm text-slate-500">
                    Daftar terapis yang terdaftar dalam sistem
                </p>
            </div>

            <!-- FILTERS & ACTIONS ROW -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <!-- LEFT: SEARCH -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <div class="flex-1 sm:flex-none sm:w-72">
                        <input type="text" id="searchInput" placeholder="Cari terapis..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                    </div>
                </div>

                <!-- RIGHT: FILTER WILAYAH -->
                <div class="flex items-center gap-2">
                    <select id="region_filter"
                        class="w-full sm:w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                        <option value="">Semua Wilayah</option>
                        <?php foreach ($regions as $value): ?>
                            <option value="<?= $value->id ?>"><?= $value->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table id="table-terapis" class="w-full text-sm">
                <!-- HEAD -->
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-semibold">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Wilayah</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Alamat</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Jumlah Tindakan</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50 transition">
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                            <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                            Memuat data terapis...
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
                    <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                        <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                    </div>
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
            Data terapis yang terdaftar dalam sistem
        </div>
    </div>
</section>

<!-- Modal Tambah Terapis -->
<div id="modalTambah" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Tambah Data Terapis</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="formTambahTerapis" action="<?= base_url('terapis/store') ?>" method="post" enctype="multipart/form-data" class="space-y-4 p-5 needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-1">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">ID Terapis (NIK/ID) <span class="text-red-500">*</span></label>
                        <input type="text" name="terapis_id" id="terapis_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            required>
                        <div class="id-feedback text-xs mt-1 hidden"></div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            required>
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama tidak boleh kosong</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tempat Lahir <span class="text-red-500">*</span></label>
                        <input type="text" name="tempat_lahir"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            required>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_lahir"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            required>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Wilayah <span class="text-red-500">*</span></label>
                        <select name="region_id" id="region_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            required>
                            <option value="">-- Pilih Wilayah --</option>
                            <?php foreach ($regions as $value): ?>
                                <option value="<?= $value->id ?>"><?= $value->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jabatan <span class="text-red-500">*</span></label>
                        <select name="jabatan_id" id="jabatan_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach ($jabatan as $j): ?>
                                <option value="<?= $j->id ?>"><?= $j->nama_jabatan ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Jabatan harus dipilih</div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Rank</label>
                    <select name="rank"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">-- Pilih Rank --</option>
                        <option value="SS">SS</option>
                        <option value="S">S</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" rows="2"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        required></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tanggal Mulai Kerja</label>
                        <input type="date" name="tgl_kerja"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Foto</label>
                        <input type="file" name="foto" id="foto" accept="image/*"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            onchange="previewImage(event)">
                        <p class="text-xs text-slate-400 mt-1">Maks. 2MB (JPG, JPEG, PNG)</p>
                    </div>
                </div>

                <div class="flex justify-center">
                    <img id="preview" src="#" alt="Preview"
                        class="hidden max-w-37.5 rounded-lg border border-slate-200" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="btnSimpan"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Status -->
<div id="modalStatus" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800" id="modalStatusTitle">Konfirmasi</h3>
        <p class="text-sm text-slate-500" id="modalStatusText"></p>

        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button id="btnConfirmStatus"
                class="rounded-lg px-4 py-2 text-sm font-medium text-white">
                Ya, Proses
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Script -->
<script>
    window.terapisConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('terapis/fetch') ?>",
        storeUrl: "<?= base_url('terapis/store') ?>",
        checkIdUrl: "<?= base_url('terapis/checkId') ?>",
        detailUrl: "<?= base_url('terapis/detail') ?>",
        generateUserUrl: "<?= base_url('terapis/generate_user') ?>"
    };
</script>

<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    // Preview image function (global)
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('preview');
            output.src = reader.result;
            output.classList.remove('hidden');
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function generateUser(terapis_id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('Apakah Anda yakin ingin membuat akun login untuk Terapis ini?')) return;
        } else {
            Swal.fire({
                title: 'Buat Akun Login?',
                text: "Sistem akan membuatkan username dan password default untuk terapis ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Buat Akun',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    processGenerateUser(terapis_id);
                }
            });
            return;
        }
        processGenerateUser(terapis_id);
    }

    function processGenerateUser(terapis_id) {
        $.ajax({
            url: window.terapisConfig.generateUserUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.terapisConfig.csrfHash
            },
            data: {
                terapis_id: terapis_id
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrfHash) {
                    window.terapisConfig.csrfHash = response.csrfHash;
                }
                
                if (response.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            confirmButtonColor: '#0d9488'
                        });
                    } else {
                        alert(response.message);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message,
                            confirmButtonColor: '#0d9488'
                        });
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Terjadi kesalahan pada server saat membuat akun.',
                        confirmButtonColor: '#0d9488'
                    });
                } else {
                    alert('Terjadi kesalahan pada server saat membuat akun.');
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>
