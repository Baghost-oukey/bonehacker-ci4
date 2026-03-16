<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= is_array($title) ? 'Daftar Pasien' : $title ?></h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Pasien</h4>
                        <div class="card-header-action">
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-1" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>ID Pasien</th>
                                        <th>Nama</th>
                                        <th>
                                            <select id="region" class="form-control">
                                                <option value="">Semua Wilayah</option>
                                                <?php if (!empty($regions_patient) && is_array($regions_patient)): ?>
                                                    <?php foreach ($wilayah as $value): ?>
                                                        <?php
                                                        $selected = (in_array($value->id, $regions_patient)) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $value->id ?>" <?= $selected ?>>
                                                            <?= $value->name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?php foreach ($wilayah as $value): ?>
                                                        <?php
                                                        $selected = (isset($_GET['region']) && $_GET['region'] == $value->id) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $value->id ?>" <?= $selected ?>>
                                                            <?= $value->name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </th>
                                        <th>Alamat</th>
                                        <th>Kunjungan Terakhir</th>
                                        <th>Jumlah Kunjungan</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="exampleModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Pasien</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>

                <?= csrf_field() ?>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                        <div class="invalid-feedback">Nama tidak Boleh Kosong</div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select class="form-control" name="gender" required>
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Man">Laki-laki</option>
                                    <option value="Woman">Perempuan</option>
                                </select>
                                <div class="invalid-feedback">Jenis Kelamin tidak boleh kosong</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <div class="control-label">Pasien Rentan</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="is_suspective" class="custom-switch-input" id="isSuspectiveCheckbox">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">YA</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div id="keterangan_rentan" style="display: none;">
                            <label for="suspectiveNotes">Keterangan Rentan</label>
                            <textarea id="ket_rentan" class="form-control" name="ket_rentan" rows="4"
                                placeholder="Masukkan keterangan rentan di sini..."></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Domestik</label>
                        <div class="radio">
                            <label>
                                <input type="radio" name="domestic" value="dalam_negeri" checked>
                                Dalam Negeri
                            </label>
                            <label>
                                <input type="radio" name="domestic" value="luar_negeri">
                                Luar Negeri
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="country-group" style="display: none;">
                        <label>Negara</label>
                        <select class="form-control" name="country_id" id="country_id">
                            <option value="">PILIH</option>
                            <?php foreach ($negara as $value): ?>
                                <option value="<?= $value->id ?>"><?= $value->country ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="desa-group">
                        <label for="desa_id">Pencarian Desa</label>
                        <select class="form-control" name="desa_id" id="desa_id" style="width: 100%;">
                            <option value="">Temukan Desa</option>
                            <!-- Options will be populated dynamically via AJAX -->
                        </select>
                        <div class="invalid-feedback">Wilayah tidak boleh kosong</div>
                    </div>

                    <div class="form-group" id="region-group">
                        <label>Wilayah</label>
                        <select class="form-control" name="region_id" id="region_id">
                            <option value="">PILIH</option>
                            <?php foreach ($wilayah as $value): ?>
                                <?php
                                $isSelected = false;
                                if (isset($regions_patient[0])) {
                                    $isSelected = is_array($regions_patient[0]) ? in_array($value->id, $regions_patient[0]) : ($value->id == $regions_patient[0]);
                                }
                                ?>
                                <option value="<?= $value->id ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= $value->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Umur</label>
                                <input type="number" class="form-control" name="age" minlength="1"
                                    maxlength="2">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label>No. Telepon/WhatsApp</label>
                                <input type="number" class="form-control" id="phone" name="phone"
                                    minlength="10" maxlength="14">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Jalan</label>
                        <textarea rows="10" class="form-control" name="address"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Files & Pictures</label>
                        <input type="file" class="form-control" name="userfiles[]" id="userfiles" multiple
                            onchange="previewFiles()">
                        <div id="file-previews" class="mt-3"></div>
                    </div>

                    <div class="form-group">
                        <label>Sumber Informasi</label>
                        <select class="form-control" name="patient_information">
                            <option value="">Pilih Sumber</option>
                            <?php foreach ($resources as $value): ?>
                                <option value="<?= $value->id ?>"
                                    <?= (isset($patient_information) && $patient_information == $value->id) ? 'selected' : '' ?>>
                                    <?= $value->nama ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Kedatangan</label>
                        <input type="datetime-local" class="form-control" name="visit_date" required>
                        <div class="invalid-feedback">Tanggal Kedatangan tidak boleh kosong</div>
                    </div>

                    <input type="hidden" name="desa_nama" id="desa_nama">
                    <input type="hidden" name="kecamatan_id" id="kecamatan_id">
                    <input type="hidden" name="kecamatan_nama" id="kecamatan_nama">
                    <input type="hidden" name="kabupaten_id" id="kabupaten_id">
                    <input type="hidden" name="kabupaten_nama" id="kabupaten_nama">
                    <input type="hidden" name="provinsi_id" id="provinsi_id">
                    <input type="hidden" name="provinsi_nama" id="provinsi_nama">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#region_id, #region').select2({});

        // Logic Checkbox Pasien Rentan
        $('#isSuspectiveCheckbox').on('change', function() {
            if (this.checked) {
                $('#keterangan_rentan').slideDown();
            } else {
                $('#keterangan_rentan').slideUp();
            }
        });

        // API Wilayah (Desa)
        var apiUrl = 'https://wilayah.smartsociety.id/public/desa';
        $('#desa_id').select2({
            placeholder: "Temukan Desa",
            dropdownParent: $("#exampleModal"),
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
                            if (item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi) {
                                optionText += '<br><small>Kec. ' + item.kecamatan.kecNama +
                                    ' - ' + item.kecamatan.kabupaten.kabNama + '</small>';
                            }
                            options.push({
                                id: item.desIdDesa,
                                text: optionText,
                                data: item // Simpan seluruh object untuk populate hidden fields
                            });
                        });
                    }
                    return {
                        results: options,
                        pagination: {
                            more: data.data && data.data.next_page_url ? true : false
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
                return item.text ? item.text.replace(/<br\s*\/?>/gi, ' ').replace(/<small>/gi, '').replace(/<\/small>/gi, '') : item.text;
            }
        }).on('select2:select', function(e) {
            var item = e.params.data.data;
            $('#desa_nama').val(item.desNama || '');
            $('#kecamatan_id').val(item.kecIdKecamatan || '');
            $('#kecamatan_nama').val(item.kecamatan ? item.kecamatan.kecNama : '');
            $('#kabupaten_id').val(item.kecamatan ? item.kecamatan.kabIdKabupaten : '');
            $('#kabupaten_nama').val(item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.kabNama : '');
            $('#provinsi_id').val(item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.provIdProvinsi : '');
            $('#provinsi_nama').val(item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi ? item.kecamatan.kabupaten.provinsi.provNama : '');
        });

        // DataTable Initialization
        var buttonsConfig = [];
        <?php if (isset($role) && $role == 'superadmin'): ?>
            buttonsConfig.push({
                className: 'btn btn-danger btn-sm',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                action: function(e, dt, node, config) {
                    var regionId = $('#region').val();
                    window.open('<?= site_url('patient/print_pdf') ?>?region_id=' + regionId, '_blank');
                }
            });
            buttonsConfig.push({
                className: 'btn btn-success btn-sm',
                text: '<i class="fas fa-file-excel"></i> Excel',
                action: function(e, dt, node, config) {
                    var regionId = $('#region').val();
                    window.location.href = '<?= site_url('patient/export') ?>?region_id=' + regionId;
                }
            });
        <?php endif; ?>

        var table = $('#table-1').DataTable({
            dom: '<"top"lBf>rt<"bottom"ip><"clear">',
            buttons: buttonsConfig,
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('patients/fetch2') ?>",
                type: "POST",
                data: function(d) {
                    d.region = $('#region').val();
                    d.<?= csrf_token() ?> = "<?= csrf_hash() ?>"; 
                }
            },
            columns: [{
                    data: "id",
                    class: "text-center"
                },
                {
                    data: "name",
                    // render: function (data, type, row){
                    //     var phoneDisplay = (row.phone && row.phone !== "" && row.phone !== null) ? `<br><small class="text-muted">(${row.phone})</small>` : "";
                    //     return `<strong>${data}</strong>${phoneDisplay}`;
                    // }
                },
                {
                    data: "name_region"
                },
                {
                    data: "address"
                },
                {
                    data: "date"
                },
                {
                    data: "visit_count",
                    class: "text-center"
                },
                {
                    data: "action",
                    class: "text-center",
                    orderable: false
                },
                {
                    data: "phone",
                    visible: false
                }
            ],
            rowCallback: function(row, data) {
                if (data.is_delete === "1") {
                    $(row).css({
                        'color': 'red',
                        'text-decoration': 'line-through'
                    });
                }
            }
        });

        // Reload table on region change
        $("#region").change(function() {
            table.ajax.reload();
        });

        $('#submitBtn').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form')[0];
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var phone = $('#phone').val();
            $.ajax({
                url: '<?= site_url('patient/check_phone') ?>',
                type: 'POST',
                data: {
                    phone: phone,
                    "<?= csrf_token() ?>": "<?= csrf_hash() ?>" 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        var message = "<p>Nomor HP sudah terdaftar:</p><ul>";
                        response.patients.forEach(function(p) {
                            message += `<li><strong>${p.name}</strong> (${p.address})</li>`;
                        });
                        message += "</ul><p>Tetap simpan data baru?</p>";

                        $('#modalBodyContent').html(message);
                        $('#modalConfirm').modal('show');

                        $('#confirmSave').off('click').on('click', function() {
                            form.submit();
                        });
                    } else {
                        form.submit();
                    }
                }
            });
        });

        window.destroy = function(id) {
            $('#deleteModal').modal('show');
            $('#confirmDelete').off('click').on('click', function() {
                $.ajax({
                    url: "<?= site_url('patient/destroy') ?>/" + id,
                    type: "POST",
                    data: {
                        "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                    },
                    success: function() {
                        window.location.reload();
                    },
                    error: function() {
                        alert('Gagal menghapus data.');
                    }
                });
            });
        };

        // Domestic vs International Toggle
        $('input[name="domestic"]').on('change', function() {
            if (this.value === 'luar_negeri') {
                $('#country-group').fadeIn();
                $('#desa-group, #region-group').hide();
            } else {
                $('#country-group').hide();
                $('#desa-group, #region-group').fadeIn();
            }
        });
    });
</script>
<?= $this->endSection() ?>