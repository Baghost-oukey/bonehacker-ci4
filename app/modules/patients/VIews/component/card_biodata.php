<div class="rounded-xl border border-slate-200 bg-white shadow-sm font-sans mb-8">
    
<!-- HEADER -->
    <div class="flex flex-col space-y-1.5 border-b border-slate-200 p-6 bg-white rounded-t-xl">
        <h3 class="text-lg font-semibold leading-none tracking-tight text-slate-900">Biodata Pasien</h3>
        <p class="text-sm text-slate-500">Ubah dan perbarui informasi dasar pasien.</p>
    </div>
    
    <!-- MODAL DATA PASIEN -->
    <div class="p-6">
        <?php if (isset($is_suspective) && $is_suspective) : ?>
            <div class="mb-8 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex h-5 w-5 shrink-0 items-center justify-center mt-0.5">
                    <i class="far fa-lightbulb text-red-600 text-lg"></i>
                </div>
                <div>
                    <h5 class="text-sm font-medium leading-none tracking-tight text-red-900 mb-1.5">Peringatan Kategori</h5>
                    <p class="text-sm text-red-600">Termasuk kategori Pasien Rentan</p>
                </div>
            </div>
        <?php endif ?>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium leading-none text-slate-900">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors" 
                        name="name" id="name" value="<?= esc($patient->name) ?>" required placeholder="Masukkan nama lengkap">
                    <div class="invalid-feedback hidden mt-1 text-[13px] font-medium text-red-500">Nama lengkap tidak boleh kosong</div>
                </div>

                <div class="space-y-2">
                    <label for="gender" class="text-sm font-medium leading-none text-slate-900">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select class="flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-colors" 
                        name="gender" id="gender" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Man" <?= ($patient->gender == 'Man') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Woman" <?= ($patient->gender == 'Woman') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                    <div class="invalid-feedback hidden mt-1 text-[13px] font-medium text-red-500">Jenis kelamin tidak boleh kosong</div>
                </div>

                <div class="space-y-2">
                    <label for="age" class="text-sm font-medium leading-none text-slate-900">Usia</label>
                    <input type="number" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors" 
                        name="age" id="age" value="<?= esc($patient->age) ?>" min="1" max="99" placeholder="Misal: 25">
                </div>

                <div class="space-y-2">
                    <label for="visit_date" class="text-sm font-medium leading-none text-slate-900">
                        Tanggal Kedatangan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors" 
                        name="visit_date" id="visit_date" value="<?= isset($created_at) ? date('Y-m-d', strtotime($created_at)) : '' ?>" required>
                    <div class="invalid-feedback hidden mt-1 text-[13px] font-medium text-red-500">Tanggal Kedatangan tidak boleh kosong</div>
                </div>

                <div class="space-y-2">
                    <label for="phone" class="text-sm font-medium leading-none text-slate-900">No. Telepon/WhatsApp</label>
                    <input type="tel" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors" 
                        name="phone" id="phone" value="<?= esc($patient->phone) ?>" minlength="10" maxlength="14" placeholder="Contoh: 081234567890">
                </div>

                <div class="space-y-2">
                    <label for="address" class="text-sm font-medium leading-none text-slate-900">Alamat Jalan</label>
                    <textarea rows="3" class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors" 
                        name="address" id="address" placeholder="Masukkan alamat lengkap"><?= esc($patient->address ?? '') ?></textarea>
                </div>

                <div class="flex flex-row items-center justify-between rounded-lg border border-slate-200 p-4">
                    <div class="space-y-0.5">
                        <label class="text-sm font-medium text-slate-900">Termasuk Kelompok Rentan</label>
                        <p class="text-[13px] text-slate-500">Tandai jika pasien membutuhkan penanganan khusus.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="is_suspective" id="isSuspectiveCheckbox" class="peer sr-only" <?= ($patient->is_suspective ?? false) ? 'checked' : '' ?>>
                        <div class="h-6 w-11 shrink-0 rounded-full border-2 border-transparent bg-slate-200 transition-colors peer-checked:bg-slate-900 peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-white after:absolute after:left-2px after:top-2px after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div class="space-y-2" id="keterangan_rentan" style="display: <?= $patient->is_suspective ? 'block' : 'none' ?>;">
                    <label for="ket_rentan" class="text-sm font-medium leading-none text-slate-900">Keterangan Rentan</label>
                    <textarea id="ket_rentan" class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors" 
                        name="ket_rentan" rows="4" placeholder="Masukkan keterangan rentan di sini..."><?= esc($patient->ket_suspect) ?></textarea>
                </div>
            </div>

            <div class="space-y-6">
                
                <div class="space-y-2">
                    <label for="patient_information" class="text-sm font-medium leading-none text-slate-900">Sumber Informasi</label>
                    <select class="flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-colors" 
                        name="patient_information" id="patient_information">
                        <option value="">Pilih Sumber Informasi</option>
                        <?php foreach ($resources as $value) : ?>
                            <option value="<?= $value->id ?>" <?= ($patient->patient_information == $value->id) ? 'selected' : '' ?>>
                                <?= esc($value->nama) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-row items-center justify-between rounded-lg border border-slate-200 p-4" id="domestic-group">
                    <div class="space-y-0.5">
                        <label class="text-sm font-medium text-slate-900">Dalam Negeri / Luar Negeri</label>
                        <p class="text-[13px] text-slate-500">Status domisili pasien.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="domestic" class="peer sr-only" <?= $patient->domestic ? 'checked' : '' ?>>
                        <div class="h-6 w-11 shrink-0 rounded-full border-2 border-transparent bg-slate-200 transition-colors peer-checked:bg-slate-900 peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-white after:absolute after:left-2px after:top-2px after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div class="space-y-2" id="country-group">
                    <label for="country_id" class="text-sm font-medium leading-none text-slate-900">Negara</label>
                    <select class="flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-colors" 
                        name="country_id" id="country_id">
                        <option value="">PILIH</option>
                        <?php foreach ($negara as $value): ?>
                            <option value="<?= esc($value->id) ?>" <?= (isset($patient->country_id) && $patient->country_id == $value->id) ? 'selected' : '' ?>>
                                <?= esc($value->country) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback hidden mt-1 text-[13px] font-medium text-red-500">Negara tidak boleh kosong</div>
                </div>

                <div class="space-y-2" id="region-group">
                    <label for="region_id" class="text-sm font-medium leading-none text-slate-900">Wilayah</label>
                    <select class="w-full text-sm" name="region_id" id="region_id">
                        <option value="">PILIH</option>
                        <?php foreach ($wilayah as $value): ?>
                            <option value="<?= $value->id ?>" <?= ($patient->region_id == $value->id) ? 'selected' : '' ?>>
                                <?= esc($value->name ?? $value->nama ?? 'Tanpa Nama') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback hidden mt-1 text-[13px] font-medium text-red-500">Wilayah tidak boleh kosong</div>
                </div>

                <div class="space-y-2" id="desa-group">
                    <label for="desa_id" class="text-sm font-medium leading-none text-slate-900">Pencarian Desa</label>
                    <select class="w-full text-sm" name="desa_id" id="desa_id" style="width: 100%;">
                        <?php if (!empty($desa_id)): ?>
                            <option value="<?= esc($desa_id) ?>" selected><?= esc($desa_nama) ?></option>
                        <?php else: ?>
                            <option value="">Temukan Desa</option>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback hidden mt-1 text-[13px] font-medium text-red-500">Desa tidak boleh kosong</div>
                </div>

                <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="<?= $address->kecamatan_id ?? '' ?>">
                <input type="hidden" name="kabupaten_id" id="kabupaten_id" value="<?= $address->kabupaten_id ?? '' ?>">
                <input type="hidden" name="provinsi_id" id="provinsi_id" value="<?= $address->provinsi_id ?? '' ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <div class="space-y-2" id="desaa-group">
                        <label for="desa_nama" class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Nama Desa</label>
                        <input type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-slate-100/50 px-3 py-1 text-sm text-slate-600 shadow-none focus-visible:outline-none cursor-not-allowed" 
                            name="desa_nama" id="desa_nama" value="<?= esc($address->desa_nama ?? '') ?>" readonly tabindex="-1">
                    </div>
                    <div class="space-y-2" id="kecamatan-group">
                        <label for="kecamatan_nama" class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Kecamatan</label>
                        <input type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-slate-100/50 px-3 py-1 text-sm text-slate-600 shadow-none focus-visible:outline-none cursor-not-allowed" 
                            id="kecamatan_nama" value="<?= esc($address->kecamatan_nama ?? '') ?>" readonly tabindex="-1">
                    </div>
                    <div class="space-y-2" id="kabupaten-group">
                        <label for="kabupaten_nama" class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Kabupaten</label>
                        <input type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-slate-100/50 px-3 py-1 text-sm text-slate-600 shadow-none focus-visible:outline-none cursor-not-allowed" 
                            id="kabupaten_nama" value="<?= esc($address->kabupaten_nama ?? '') ?>" readonly tabindex="-1">
                    </div>
                    <div class="space-y-2" id="provinsi-group">
                        <label for="provinsi_nama" class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Provinsi</label>
                        <input type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-slate-100/50 px-3 py-1 text-sm text-slate-600 shadow-none focus-visible:outline-none cursor-not-allowed" 
                            id="provinsi_nama" value="<?= esc($address->provinsi_nama ?? '') ?>" readonly tabindex="-1">
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-8 flex items-center border-t border-slate-200 pt-5">
            <p class="text-[13px] text-slate-500 created_by">
                Data pasien ditambah oleh <span class="font-semibold text-slate-900"><?= esc($created_by_name) ?></span> pada <span class="font-medium text-slate-700"><?= $created_at ?></span>
                <?php if ($has_updated): ?>
                    <span class="mx-1.5 text-slate-300">•</span>
                    Diedit oleh <span class="font-semibold text-slate-900"><?= esc($updated_by_name) ?></span> pada <span class="font-medium text-slate-700"><?= $updated_at ?></span>
                <?php endif; ?>
            </p>
        </div>
    </div>

</div>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/pages/patient-biodata.js') ?>"></script>
<?= $this->endSection() ?>