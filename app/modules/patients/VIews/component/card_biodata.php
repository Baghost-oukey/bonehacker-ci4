<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Biodata</h4>
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
    </div>
    <div class="card-body">

        <?php if (isset($is_suspective) && $is_suspective) : ?>
            <div class="alert alert-danger alert-has-icon mb-4">
                <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                <div class="alert-body">
                    <div class="alert-title">Peringatan</div>
                    Termasuk kategori Pasien Rentan
                </div>
            </div>
        <?php endif ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" id="name"
                        value="<?= esc($name) ?>" required>
                    <div class="invalid-feedback">Nama lengkap tidak boleh kosong</div>
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select class="form-control" name="gender" id="gender" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Man" <?= ($gender == 'Man') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Woman" <?= ($gender == 'Woman') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                    <div class="invalid-feedback">Jenis kelamin tidak boleh kosong</div>
                </div>

                <div class="form-group">
                    <label for="age">Usia</label>
                    <input type="number" class="form-control" name="age" id="age"
                        value="<?= esc($age) ?>" min="1" max="99">
                </div>

                <div class="form-group">
                    <label for="visit_date">Tanggal Kedatangan</label>
                    <input type="date" class="form-control" name="visit_date" id="visit_date"
                        value="<?= isset($created_at) ? date('Y-m-d', strtotime($created_at)) : '' ?>"
                        required>
                    <div class="invalid-feedback">Tanggal Kedatangan tidak boleh kosong</div>
                </div>

                <div class="form-group">
                    <label for="phone">No. Telepon/WhatsApp</label>
                    <input type="tel" class="form-control" name="phone" id="phone"
                        value="<?= esc($phone) ?>" minlength="10" maxlength="14">
                </div>

                <div class="form-group">
                    <label for="address">Alamat Jalan</label>
                    <textarea rows="3" class="form-control" name="address" id="address"><?= esc($address) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Termasuk Kelompok Rentan</label> <br>
                    <div class="custom-switch">
                        <label class="custom-switch mt-2">
                            <input type="checkbox" name="is_suspective" class="custom-switch-input"
                                id="isSuspectiveCheckbox" <?= $is_suspective ? 'checked' : '' ?>>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Ya, Kelompok Rentan</span>
                        </label>
                    </div>
                </div>

                <div id="keterangan_rentan" style="display: <?= $is_suspective ? 'block' : 'none' ?>;">
                    <label for="ket_rentan">Keterangan Rentan</label>
                    <textarea id="ket_rentan" class="form-control" name="ket_rentan" rows="4"
                        placeholder="Masukkan keterangan rentan di sini..."><?= esc($ket_suspect) ?></textarea>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="form-group">
                    <label>Sumber Informasi</label>
                    <select class="form-control" name="patient_information" id="patient_information">
                        <option value="">Pilih Sumber Informasi</option>
                        <?php foreach ($resources as $value) : ?>
                            <option value="<?= $value->id ?>" <?= ($patient_information == $value->id) ? 'selected' : '' ?>>
                                <?= esc($value->nama) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="domestic-group">
                    <label>Dalam Negeri / Luar Negeri</label> <br>
                    <div class="custom-switch">
                        <label class="custom-switch mt-2">
                            <input type="checkbox" name="domestic" class="custom-switch-input"
                                <?= $domestic ? 'checked' : '' ?>>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Dalam Negeri</span>
                        </label>
                    </div>
                </div>
                <div class="form-group" id="domestic-group">
                    <label for="address">Dalam Negeri / Luar Negeri</label> <br>
                    <div class="custom-switch">
                        <label class="custom-switch mt-2">
                            <input type="checkbox" name="domestic" class="custom-switch-input"
                                <?= (isset($domestic) && $domestic) ? 'checked' : '' ?>>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Dalam Negeri</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="country-group">
                    <label for="country_id">Negara</label>
                    <select class="form-control" name="country_id" id="country_id">
                        <option value="">PILIH</option>
                        <?php foreach ($negara as $value): ?>
                            <option value="<?= esc($value->id) ?>"
                                <?= (isset($country_id) && $country_id == $value->id) ? 'selected' : '' ?>>
                                <?= esc($value->country) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Negara tidak boleh kosong</div>
                </div>

                <div class="form-group" id="region-group">
                    <label for="region_id">Wilayah</label>
                    <select class="form-control" name="region_id" id="region_id">
                        <option value="">PILIH</option>
                        <?php foreach ($wilayah as $value): ?>
                            <option value="<?= esc($value->id) ?>"
                                <?= (isset($region_id) && $region_id == $value->id) ? 'selected' : '' ?>>
                                <?= esc($value->name ?? 'Data Tidak Terbaca') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Wilayah tidak boleh kosong</div>
                </div>

                <div class="form-group" id="desa-group">
                    <label for="desa_id">Pencarian Desa</label>
                    <select class="form-control" name="desa_id" id="desa_id" style="width: 100%;">
                        <?php if (!empty($desa_id)): ?>
                            <option value="<?= esc($desa_id) ?>" selected><?= esc($desa_nama) ?></option>
                        <?php else: ?>
                            <option value="">Temukan Desa</option>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">Desa tidak boleh kosong</div>
                </div>

                <input type="hidden" name="kecamatan_id" id="kecamatan_id" value="<?= $kecamatan_id ?>">
                <input type="hidden" name="kabupaten_id" id="kabupaten_id" value="<?= $kabupaten_id ?>">
                <input type="hidden" name="provinsi_id" id="provinsi_id" value="<?= $provinsi_id ?>">

                <div class="form-group">
                    <label>Nama Desa</label>
                    <input type="text" class="form-control" name="desa_nama" value="<?= esc($desa_nama) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Kecamatan</label>
                    <input type="text" class="form-control" value="<?= esc($kecamatan_nama) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Kabupaten</label>
                    <input type="text" class="form-control" value="<?= esc($kabupaten_nama) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Provinsi</label>
                    <input type="text" class="form-control" value="<?= esc($provinsi_nama) ?>" readonly>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group">
            <small class="text-muted">
                Data pasien ditambah oleh <strong><?= esc($created_by_name) ?></strong> pada <strong><?= $created_at ?></strong>
                <?php if ($has_updated): ?>
                    , dan diedit oleh <strong><?= esc($updated_by_name) ?></strong> pada <strong><?= $updated_at ?></strong>
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var apiUrl = 'https://wilayah.smartsociety.id/public/desa';

        // Inisialisasi Select2
        $('#desa_id').select2({
            placeholder: "Temukan Desa",
            allowClear: true,
            ajax: {
                url: apiUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    var options = [];
                    if (data.data && data.data.data) {
                        $.each(data.data.data, function(index, item) {
                            var optionText = item.desNama;
                            if (item.kecamatan && item.kecamatan.kabupaten) {
                                optionText += '<br><small>Kec. ' + item.kecamatan.kecNama + ', ' + item.kecamatan.kabupaten.kabNama + '</small>';
                            }
                            options.push({
                                id: item.desIdDesa,
                                text: optionText,
                                data: {
                                    desa_nama: item.desNama,
                                    kecamatan_id: item.kecamatan ? item.kecamatan.kecIdKecamatan : '',
                                    kecamatan_nama: item.kecamatan ? item.kecamatan.kecNama : '',
                                    kabupaten_id: item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.kabIdKabupaten : '',
                                    kabupaten_nama: item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.kabNama : '',
                                    provinsi_id: item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi ? item.kecamatan.kabupaten.provinsi.provIdProvinsi : '',
                                    provinsi_nama: item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi ? item.kecamatan.kabupaten.provinsi.provNama : ''
                                }
                            });
                        });
                    }
                    return {
                        results: options,
                        pagination: {
                            more: !!(data.data && data.data.next_page_url)
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            escapeMarkup: function(markup) {
                return markup;
            },
            templateResult: function(item) {
                return item.text;
            },
            templateSelection: function(item) {
                // Menghilangkan tag HTML saat item dipilih agar tampilan bersih
                return item.text ? item.text.split('<br>')[0] : item.text;
            }
        });

        // Handle selection change
        $('#desa_id').on('select2:select', function(e) {
            var data = e.params.data.data;
            if (data) {
                $('#desa_nama').val(data.desa_nama);
                $('#kecamatan_id').val(data.kecamatan_id);
                $('#kecamatan_nama').val(data.kecamatan_nama);
                $('#kabupaten_id').val(data.kabupaten_id);
                $('#kabupaten_nama').val(data.kabupaten_nama);
                $('#provinsi_id').val(data.provinsi_id);
                $('#provinsi_nama').val(data.provinsi_nama);
            }
        });

        // Switch Rentan Toggle
        $('#isSuspectiveCheckbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#keterangan_rentan').slideDown();
            } else {
                $('#keterangan_rentan').slideUp();
                $('#ket_rentan').val('');
            }
        });
    });
</script>
<?= $this->endSection() ?>