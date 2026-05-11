<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="detailTerapisPage" class="w-full space-y-6 p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Detail informasi dan biodata terapis
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= site_url('terapis') ?>"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-arrow-left text-slate-500"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <form action="<?= site_url('terapis/update') ?>" id="detailterapis" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $terapis->id ?>">

        <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
            <!-- Card Header -->
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Biodata Terapis</h3>
                    <p class="text-sm text-slate-500">Lengkapi data diri terapis dengan benar</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnEdit" onclick="toggleEditMode(true)" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                        <i class="fas fa-edit"></i>
                        Ubah Data
                    </button>
                    <button type="button" id="btnBatal" onclick="toggleEditMode(false)" class="hidden items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" id="btnSimpan" class="hidden items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Photo Section -->
                        <div class="flex flex-col items-center space-y-3">
                            <div class="photo-container relative inline-block">
                                <img id="photo-preview"
                                    src="<?= base_url('foto_terapis/' . ($terapis->foto ? $terapis->foto : 'no_profile.png')) ?>"
                                    alt="Foto Profil"
                                    class="w-48 h-48 rounded-xl object-cover border-2 border-slate-200 cursor-pointer">

                                <!-- Overlay -->
                                <div class="overlay absolute inset-0 bg-black/50 rounded-xl opacity-0 invisible transition-all duration-200 flex items-center justify-center gap-2">
                                    <button type="button" onclick="triggerEdit()" id="btnEditPhoto"
                                        class="hidden h-9 w-9 rounded-lg bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center">
                                        <i class="fas fa-pen text-sm"></i>
                                    </button>
                                    <button type="button" onclick="previewImageModal()"
                                        class="h-9 w-9 rounded-lg bg-white text-slate-700 hover:bg-slate-100 flex items-center justify-center">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    <?php if ($terapis->foto): ?>
                                    <button type="button" onclick="confirmDeletePhoto()" id="btnDeletePhoto"
                                        class="hidden h-9 w-9 rounded-lg bg-white text-red-600 hover:bg-red-50 flex items-center justify-center">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="button" onclick="previewQr()"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                <i class="fas fa-qrcode text-slate-500"></i>
                                Info Publik
                            </button>

                            <input type="file" class="hidden" id="foto" name="foto" accept="image/*" onchange="previewImage(event)">
                        </div>

                        <!-- ID Terapis -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">ID Terapis <span class="text-red-500">*</span></label>
                            <input type="text" name="terapis_id" id="terapis_id"
                                value="<?= esc($terapis->terapis_id) ?>"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                required readonly>
                            <div class="id-feedback text-xs mt-1 hidden"></div>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" id="nama"
                                value="<?= esc($terapis->nama) ?>"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                required readonly>
                            <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama lengkap tidak boleh kosong</div>
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir"
                                value="<?= esc($terapis->tempat_lahir) ?>"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                readonly>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" id="tgl_lahir"
                                value="<?= $terapis->tanggal_lahir ?>"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                readonly>
                        </div>

                        <!-- Alamat -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                readonly><?= esc($terapis->alamat) ?></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- Wilayah Kerja -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Wilayah Kerja</label>
                            <select name="region_id" id="region_id"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                disabled>
                                <option value="">-- Pilih Wilayah --</option>
                                <?php foreach ($wilayah as $region): ?>
                                    <option value="<?= $region->id ?>" <?= $region->id == $terapis->region_id ? 'selected' : '' ?>>
                                        <?= esc($region->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Jabatan</label>
                            <select name="jabatan_id" id="jabatan_id"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                disabled>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php foreach ($jabatan as $jab): ?>
                                    <option value="<?= $jab->id ?>" <?= $jab->id == $terapis->jabatan_id ? 'selected' : '' ?>>
                                        <?= esc($jab->nama_jabatan) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Rank -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Rank</label>
                            <select name="rank" id="rank"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                disabled>
                                <option value="">-- Pilih Rank --</option>
                                <?php $ranks = ['SS', 'S', 'A', 'B', 'C']; ?>
                                <?php foreach ($ranks as $r): ?>
                                    <option value="<?= $r ?>" <?= $terapis->rank == $r ? 'selected' : '' ?>><?= $r ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tanggal Mulai Kerja -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Tanggal Mulai Kerja</label>
                            <input type="date" name="tgl_kerja" id="tgl_kerja"
                                value="<?= isset($terapis->tgl_mulai_kerja) ? date('Y-m-d', strtotime($terapis->tgl_mulai_kerja)) : '' ?>"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                readonly>
                        </div>

                        <!-- Keterangan -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3"
                                class="form-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-slate-50 cursor-not-allowed"
                                readonly><?= esc($terapis->keterangan) ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Status</label>
                            <label class="mt-2 inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="status" id="status_checkbox" class="form-input sr-only peer" <?= $terapis->is_active == 1 ? 'checked' : '' ?> disabled>
                                <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:inset-s-0.5 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                                <span class="text-sm font-medium text-slate-700" id="status_label"><?= $terapis->is_active == 1 ? 'Ya' : 'Tidak' ?></span>
                            </label>
                        </div>

                        <!-- Presensi -->
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-slate-700">Presensi</label>
                            <label class="mt-2 inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_presensi" id="presensi_checkbox" class="form-input sr-only peer" <?= ($terapis->is_presensi ?? 1) == 1 ? 'checked' : '' ?> disabled>
                                <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:inset-s-0.5 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                                <span class="text-sm font-medium text-slate-700" id="presensi_label"><?= ($terapis->is_presensi ?? 1) == 1 ? 'Ya' : 'Tidak' ?></span>
                            </label>
                            <p class="text-xs text-slate-500 mt-1">Jika dinonaktifkan, terapis tidak akan muncul di form presensi harian</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- User Account Section -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Akun User Terhubung</h3>
            <p class="text-sm text-slate-500">Informasi login terapis di sistem</p>
        </div>
        <div class="p-6">
            <?php if ($connected_user): ?>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500">Username</label>
                        <p class="text-sm font-semibold text-slate-900"><?= esc($connected_user->username) ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500">Role</label>
                        <p class="text-sm font-semibold text-slate-900">
                            <span class="inline-flex items-center rounded-md bg-teal-50 px-2 py-1 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-600/20">
                                <?= strtoupper(esc($connected_user->role)) ?>
                            </span>
                        </p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500">Status Akun</label>
                        <p class="text-sm font-semibold text-slate-900">
                            <span class="text-emerald-600"><i class="fas fa-check-circle mr-1"></i> Terhubung</span>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-6 space-y-4">
                    <div class="rounded-full bg-slate-100 p-4">
                        <i class="fas fa-user-shield text-3xl text-slate-400"></i>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-900">Belum Ada Akun User</p>
                        <p class="text-xs text-slate-500">Terapis ini belum memiliki akun untuk masuk ke sistem</p>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <button type="button" onclick="openCreateAccountModal()"
                            class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                            <i class="fas fa-user-plus"></i>
                            Buat Akun Baru
                        </button>
                        <button type="button" onclick="openLinkAccountModal()"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-link text-slate-500"></i>
                            Hubungkan Akun Ada
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal Error File -->
<div id="modalFileError" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Kesalahan Unggah File</h3>
        <div class="rounded-lg bg-red-50 p-3 text-sm text-red-600" id="fileErrorMessage"></div>
        <div class="flex justify-end">
            <button type="button" data-modal-close
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Modal Preview Foto -->
<div id="modalPhotoPreview" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">Preview Foto</h3>
            <button type="button" data-modal-close class="text-slate-500 hover:text-slate-800">&times;</button>
        </div>
        <div class="flex justify-center">
            <img id="full-photo-preview" class="max-w-full max-h-96 rounded-lg" src="" alt="Preview Foto">
        </div>
    </div>
</div>

<!-- Modal Hapus Foto -->
<div id="modalDeletePhoto" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus Foto</h3>
        <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus foto ini?</p>

        <form action="<?= site_url('terapis/deletefoto') ?>" method="POST" class="flex justify-end gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $terapis->id ?>">
            <input type="hidden" name="terapis_id" value="<?= $terapis->terapis_id ?>">

            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button type="submit"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Hapus
            </button>
        </form>
    </div>
</div>

<!-- Modal Create Account -->
<div id="modalCreateAccount" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-semibold text-slate-800">Buat Akun Login Baru</h3>
            <button type="button" onclick="closeCreateAccountModal()" class="text-slate-500 hover:text-slate-800 text-2xl">&times;</button>
        </div>
        
        <div class="space-y-4">
            <p class="text-sm text-slate-500">Buat akun akses sistem untuk terapis <strong><?= esc($terapis->nama) ?></strong>.</p>
            
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700">Username <span class="text-red-500">*</span></label>
                <input type="text" id="new-username" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" value="<?= esc($terapis->terapis_id) ?>" placeholder="Masukkan username">
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700">Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" id="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" value="password123" placeholder="Masukkan password">
                    <button type="button" onclick="togglePasswordVisibility('new-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Default: password123</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" onclick="closeCreateAccountModal()"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button type="button" onclick="submitCreateAccount()"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                Simpan & Buat Akun
            </button>
        </div>
    </div>
</div>

<!-- Modal Link Account -->
<div id="modalLinkAccount" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-semibold text-slate-800">Hubungkan ke Akun User</h3>
            <button type="button" onclick="closeLinkAccountModal()" class="text-slate-500 hover:text-slate-800 text-2xl">&times;</button>
        </div>
        
        <div class="space-y-4">
            <p class="text-sm text-slate-500">Pilih akun user yang ingin dihubungkan dengan terapis <strong><?= esc($terapis->nama) ?></strong>. Setelah dihubungkan, user tersebut akan memiliki akses ke profil terapis ini tanpa mengubah username mereka.</p>
            
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-700">Pilih User <span class="text-red-500">*</span></label>
                <select id="select-user-link" class="w-full" style="width: 100%;">
                    <option value="">-- Cari Nama atau Username --</option>
                    <?php foreach ($all_users as $u): ?>
                        <option value="<?= $u->id ?>"><?= esc($u->realname) ?> (<?= esc($u->username) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" onclick="closeLinkAccountModal()"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Batal
            </button>
            <button type="button" onclick="submitLinkAccount()"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                Hubungkan Sekarang
            </button>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div id="modalQrPreview" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">QR Code Info Publik Terapis</h3>
            <button type="button" data-modal-close class="text-slate-500 hover:text-slate-800">&times;</button>
        </div>
        <div class="flex justify-center">
            <img src="<?= $qr_code_base64 ?>" alt="QR Code Terapis" class="rounded-lg">
        </div>
        <div class="flex justify-center">
            <a href="<?= $qr_code_base64 ?>" download="qr_code_terapis.png"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                <i class="fas fa-download"></i>
                Download QR Code
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Script -->
<script>
    window.detailTerapisConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        checkIdUrl: "<?= base_url('terapis/checkId') ?>",
        generateUserUrl: "<?= base_url('terapis/generate_user') ?>",
        linkUserUrl: "<?= base_url('terapis/link_user') ?>",
        currentId: "<?= $terapis->terapis_id ?>",
        terapisId: "<?= $terapis->id ?>"
    };

    $(document).ready(function() {
        $('#select-user-link').select2({
            dropdownParent: $('#modalLinkAccount'),
            placeholder: '-- Cari Nama atau Username --',
            allowClear: true
        });

        // Close modals when clicking outside
        $('#modalLinkAccount, #modalCreateAccount').on('click', function(e) {
            if (e.target === this) {
                closeLinkAccountModal();
                closeCreateAccountModal();
            }
        });
        
        // Handle all data-modal-close
        $('[data-modal-close]').on('click', function() {
            $(this).closest('.modal-wrapper').addClass('hidden').removeClass('flex');
            document.body.style.overflow = "";
        });
    });

    function openCreateAccountModal() {
        $('#modalCreateAccount').removeClass('hidden').addClass('flex');
        document.body.style.overflow = "hidden";
    }

    function closeCreateAccountModal() {
        $('#modalCreateAccount').addClass('hidden').removeClass('flex');
        document.body.style.overflow = "";
    }

    function togglePasswordVisibility(id) {
        const input = document.getElementById(id);
        const icon = event.currentTarget.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function submitCreateAccount() {
        const username = $('#new-username').val().trim();
        const password = $('#new-password').val();

        if (!username || !password) {
            alert('Username dan Password wajib diisi');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin membuat akun ini?')) {
            return;
        }

        $.ajax({
            url: window.detailTerapisConfig.generateUserUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.detailTerapisConfig.csrfHash
            },
            data: {
                terapis_id: window.detailTerapisConfig.currentId,
                username: username,
                password: password
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrfHash) {
                    window.detailTerapisConfig.csrfHash = response.csrfHash;
                }
                
                if (response.status === 'success') {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan pada server saat membuat akun.');
            }
        });
    }

    function openLinkAccountModal() {
        $('#modalLinkAccount').removeClass('hidden').addClass('flex');
        document.body.style.overflow = "hidden";
    }

    function closeLinkAccountModal() {
        $('#modalLinkAccount').addClass('hidden').removeClass('flex');
        document.body.style.overflow = "";
    }

    function submitLinkAccount() {
        const userId = $('#select-user-link').val();
        if (!userId) {
            alert('Silakan pilih user terlebih dahulu');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menghubungkan akun ini dengan terapis ini?')) {
            return;
        }

        $.ajax({
            url: window.detailTerapisConfig.linkUserUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.detailTerapisConfig.csrfHash
            },
            data: {
                user_id: userId,
                terapis_id: window.detailTerapisConfig.currentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrfHash) {
                    window.detailTerapisConfig.csrfHash = response.csrfHash;
                }
                
                if (response.status === 'success') {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan pada server saat menghubungkan akun.');
            }
        });
    }

    function generateUser(terapis_id) {
        if (!confirm('Apakah Anda yakin ingin membuat akun login untuk Terapis ini?')) {
            return;
        }

        $.ajax({
            url: window.detailTerapisConfig.generateUserUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.detailTerapisConfig.csrfHash
            },
            data: {
                terapis_id: terapis_id
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrfHash) {
                    window.detailTerapisConfig.csrfHash = response.csrfHash;
                }
                
                if (response.status === 'success') {
                    alert(response.message);
                    location.reload(); // Reload to show the user info
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan pada server saat membuat akun.');
            }
        });
    }

    function toggleEditMode(isEdit) {
        const formInputs = document.querySelectorAll('.form-input');
        const btnEdit = document.getElementById('btnEdit');
        const btnBatal = document.getElementById('btnBatal');
        const btnSimpan = document.getElementById('btnSimpan');
        const btnEditPhoto = document.getElementById('btnEditPhoto');
        const btnDeletePhoto = document.getElementById('btnDeletePhoto');

        if (isEdit) {
            // Enable editing
            formInputs.forEach(input => {
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
                input.classList.remove('bg-slate-50', 'cursor-not-allowed');
                input.classList.add('bg-white');
            });
            
            // Show/Hide buttons
            btnEdit.classList.add('hidden');
            btnEdit.classList.remove('inline-flex');

            btnBatal.classList.remove('hidden');
            btnBatal.classList.add('inline-flex');

            btnSimpan.classList.remove('hidden');
            btnSimpan.classList.add('inline-flex');
            
            if (btnEditPhoto) btnEditPhoto.classList.remove('hidden');
            if (btnDeletePhoto) btnDeletePhoto.classList.remove('hidden');
        } else {
            // Cancel/Read-only
            if (confirm('Batalkan perubahan? Data yang sudah diubah tidak akan disimpan.')) {
                location.reload();
            }
        }
    }

    // Update label text when toggle changes
    document.getElementById('status_checkbox').addEventListener('change', function() {
        document.getElementById('status_label').textContent = this.checked ? 'Ya' : 'Tidak';
    });

    document.getElementById('presensi_checkbox').addEventListener('change', function() {
        document.getElementById('presensi_label').textContent = this.checked ? 'Ya' : 'Tidak';
    });
</script>

<?= $this->endSection() ?>
