<form id="patientForm" action="<?= site_url('patient/update') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $patient_id ?>">

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Biodata Pasien</h3>
                <p class="text-sm text-slate-500" id="biodata-subtitle">Informasi dasar pasien</p>
            </div>
            <div class="flex gap-2">
                <button type="button" id="btn-edit-biodata"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <i class="fas fa-edit"></i> Edit Data
                </button>
                <button type="button" id="btn-cancel-edit" style="display: none;"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" id="btn-save-biodata" style="display: none;"
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>

        <div class="p-6" id="biodata-content">
            <?php if (isset($patient->is_suspective) && $patient->is_suspective): ?>
                <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                    <div>
                        <h5 class="text-sm font-semibold text-red-800">Peringatan Kategori</h5>
                        <p class="text-sm text-red-600">Termasuk kategori Pasien Rentan</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="<?= esc($patient->name) ?>" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            placeholder="Masukkan nama lengkap">
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Nama lengkap tidak boleh kosong</div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="gender" id="gender" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">-- Pilih --</option>
                            <option value="Man" <?= ($patient->gender == 'Man') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Woman" <?= ($patient->gender == 'Woman') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Jenis kelamin tidak boleh kosong</div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Usia</label>
                        <input type="number" name="age" id="age" value="<?= esc($patient->age) ?>" min="1" max="99"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            placeholder="Misal: 25">
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Tanggal Kedatangan <span class="text-red-500">*</span></label>
                        <input type="date" name="visit_date" id="visit_date" 
                            value="<?= isset($created_at) ? date('Y-m-d', strtotime($created_at)) : '' ?>" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <div class="invalid-feedback text-xs text-red-500 mt-1 hidden">Tanggal kedatangan tidak boleh kosong</div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">No. Telepon/WhatsApp</label>
                        <input type="tel" name="phone" id="phone" value="<?= esc($patient->phone) ?>"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            placeholder="0812xxxxx">
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Alamat Jalan</label>
                        <textarea name="address" id="address" rows="3"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            placeholder="Masukkan alamat lengkap"><?= esc($patient->address ?? '') ?></textarea>
                    </div>

                    <!-- Toggle Rentan -->
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Termasuk Kelompok Rentan</label>
                            <p class="text-xs text-slate-500">Tandai jika pasien membutuhkan penanganan khusus</p>
                        </div>
                        <label class="inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="is_suspective" id="isSuspectiveCheckbox" class="sr-only peer"
                                <?= ($patient->is_suspective ?? false) ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all relative"></div>
                        </label>
                    </div>

                    <div class="space-y-1 hidden" id="keterangan_rentan">
                        <label class="text-sm font-medium text-slate-700">Keterangan Rentan</label>
                        <textarea name="ket_rentan" id="ket_rentan" rows="3"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                            placeholder="Masukkan keterangan rentan"><?= esc($patient->ket_suspect ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Sumber Informasi</label>
                        <select name="patient_information" id="patient_information"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">Pilih Sumber Informasi</option>
                            <?php foreach ($resources as $value): ?>
                                <option value="<?= $value->id ?>" <?= ($patient->patient_information == $value->id) ? 'selected' : '' ?>>
                                    <?= esc($value->nama) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Toggle Domestik -->
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Domestik</label>
                            <p class="text-xs text-slate-500">Status domisili pasien</p>
                        </div>
                        <div class="flex gap-2">
                            <label class="inline-flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="domestic" value="dalam_negeri" class="text-teal-600 focus:ring-teal-500"
                                    <?= ($patient->domestic == 1 || !isset($patient->domestic)) ? 'checked' : '' ?>>
                                <span class="text-xs text-slate-600">Dalam Negeri</span>
                            </label>
                            <label class="inline-flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="domestic" value="luar_negeri" class="text-teal-600 focus:ring-teal-500"
                                    <?= (isset($patient->domestic) && $patient->domestic == 0) ? 'checked' : '' ?>>
                                <span class="text-xs text-slate-600">Luar Negeri</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1 hidden" id="country-group">
                        <label class="text-sm font-medium text-slate-700">Negara</label>
                        <select name="country_id" id="country_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">PILIH</option>
                            <?php foreach ($negara as $value): ?>
                                <option value="<?= $value->id ?>" <?= ($patient->country_id == $value->id) ? 'selected' : '' ?>>
                                    <?= esc($value->country) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Wilayah</label>
                        <select name="region_id" id="region_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">PILIH</option>
                            <?php foreach ($wilayah as $value): ?>
                                <option value="<?= $value->id ?>" <?= ($patient->region_id == $value->id) ? 'selected' : '' ?>>
                                    <?= esc($value->name ?? $value->nama ?? 'Tanpa Nama') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Pencarian Desa</label>
                        <select name="desa_id" id="desa_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <?php if (!empty($address->desa_id)): ?>
                                <option value="<?= esc($address->desa_id) ?>" selected><?= esc($address->desa_nama) ?></option>
                            <?php else: ?>
                                <option value="">Temukan Desa</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <input type="hidden" name="desa_nama" id="desa_nama" value="<?= esc($address->desa_nama ?? '') ?>">
                    <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="<?= esc($address->kecamatan_id ?? '') ?>">
                    <input type="hidden" name="kabupaten_id" id="kabupaten_id" value="<?= esc($address->kabupaten_id ?? '') ?>">
                    <input type="hidden" name="provinsi_id" id="provinsi_id" value="<?= esc($address->provinsi_id ?? '') ?>">

                    <!-- Info Wilayah Readonly -->
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-100">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-slate-500">Kecamatan</label>
                            <input type="text" id="kecamatan_nama" value="<?= esc($address->kecamatan_nama ?? '') ?>" readonly
                                class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs text-slate-600 cursor-not-allowed">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-slate-500">Kabupaten</label>
                            <input type="text" id="kabupaten_nama" value="<?= esc($address->kabupaten_nama ?? '') ?>" readonly
                                class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs text-slate-600 cursor-not-allowed">
                        </div>
                        <div class="space-y-1 col-span-2">
                            <label class="text-xs font-medium text-slate-500">Provinsi</label>
                            <input type="text" id="provinsi_nama" value="<?= esc($address->provinsi_nama ?? '') ?>" readonly
                                class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs text-slate-600 cursor-not-allowed">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-6 border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500">
                    Data pasien ditambah oleh <span class="font-semibold text-slate-700"><?= esc($created_by_name) ?></span> 
                    pada <span class="font-medium text-slate-600"><?= $created_at ?></span>
                    <?php if ($has_updated): ?>
                        <span class="mx-1.5 text-slate-300">•</span>
                        Diedit oleh <span class="font-semibold text-slate-700"><?= esc($updated_by_name) ?></span> 
                        pada <span class="font-medium text-slate-600"><?= $updated_at ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</form>