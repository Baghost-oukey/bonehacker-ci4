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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Anda yakin ingin menghapus data ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>


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


<!-- Export Modal -->
<div class="modal fade" id="modalExport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Laporan Pasien</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= site_url('patient/export_data') ?>" method="GET" target="_blank">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Rentang Tanggal</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                            </div>
                            <input type="text" name="date_range" id="export_date" class="form-control" placeholder="Pilih Periode Laporan">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pilih Wilayah</label>
                        <select name="region_id" class="form-control select2" style="width: 100%;">
                            <option value="">Semua Wilayah</option>
                            <?php foreach ($regions_patient as $r): ?>
                                <option value="<?= $r->id ?>"><?= $r->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Format Laporan</label>
                        <select name="type" class="form-control">
                            <option value="excel">Export ke Microsoft Excel (.xlsx)</option>
                            <option value="pdf">Export ke PDF (.pdf)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unduh Sekarang</button>
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

        var buttonsConfig = [];

        <?php if (isset($role) && $role == 'superadmin'): ?>
            $('#export_date').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
                }
            });

            $('#export_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });
            
            buttonsConfig.push({
                className: 'btn btn-primary btn-sm mr-1',
                text: '<i class="fas fa-file-pdf"></i> Download Data Pasien',
                action: function(e, dt, node, config) {
                    var regionId = $('#region').val();
                    $('#modalExport').modal('show');

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
                // {
                //     data: "phone",
                //     visible: false
                // }
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


        function updateCRSF(newToken) {
            if (newToken) {
                $('meta[name="csrf-token"]').attr('content', newToken);
                $('input[name="<?= csrf_token() ?>"]').val(newToken);
                // console.log("Security Token Synchronized.");
            }
        }

        $('#submitBtn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var btn = $(this);
            var $form = btn.closest('form');
            var formElement = $form[0];

            // 1. Validasi
            if (!formElement.checkValidity()) {
                $form.addClass('was-validated');
                return;
            }


            function simpanPasien() {
                var formData = new FormData(formElement);
                var csrfHeader = $('meta[name="csrf-header"]').attr('content');
                var csrfHash = $('meta[name="csrf-token"]').attr('content');
                formData.append(csrfHeader, csrfHash);

                // console.log("Mengirim Simpan dengan Token: " + csrfHash);
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    headers: {
                        [csrfHeader]: csrfHash
                    },
                    beforeSend: function() {
                        btn.prop('disabled', true).addClass('btn-progress').text('Proses Simpan...');
                    },
                    success: function(res) {
                        updateCRSF(res.new_token);

                        if (res.new_token) {
                            $('meta[name="csrf-token"]').attr('content', res.new_token);
                        }
                        if (res.status === 'success') {
                            $('#exampleModal').modal('hide');
                            $form[0].reset();
                            $form.removeClass('was-validated');

                            if ($.fn.DataTable.isDataTable('#table-1')) {
                                $('#table-1').DataTable().ajax.reload(null, false);
                            }
                            swal.fire({
                                title: 'Berhasil',
                                text: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((results) => {
                                if (result.isConfirmed) {
                                    window.location.href = "<?= site_url('dashboard') ?>";
                                }
                            })

                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: res.message,
                                icon: 'error',
                                confirmButtonText: 'Oke'
                            });
                            btn.prop('disabled', false).removeClass('btn-progress').text('Simpan');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Terjadi Kegagalan sistem',
                            icon: 'error',
                            // confirmButtonText: 'Perbaiki'
                        });
                        btn.prop('disabled', false).removeClass('btn-progress').text('Simpan');
                    }
                });
            }

            var phone = $('#phone').val();

            // 2. Cek Duplikasi Telepon
            if (!phone) {
                simpanPasien();
            } else {
                var currentToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '<?= site_url('patient/check_phone') ?>',
                    type: 'POST',
                    data: {
                        phone: phone,
                        "<?= csrf_token() ?>": currentToken
                    },
                    dataType: 'json',
                    success: function(response) {
                        updateCRSF(response.new_token);
                        if (response.exists) {
                            btn.prop('disabled', false).removeClass('btn-progress');

                            // Isi modal konfirmasi
                            var message = "<p>Nomor HP sudah terdaftar:</p><ul>";
                            response.patients.forEach(function(p) {
                                message += `<li><strong>${p.name}</strong> (${p.address})</li>`;
                            });
                            message += "</ul><p>Tetap simpan data baru?</p>";

                            $('#modalBodyContent').html(message);
                            $('#modalConfirm').modal('show');

                            // Jika user klik "Ya, Simpan" di modal konfirmasi
                            $('#confirmSave').off('click').on('click', function() {
                                $('#modalConfirm').modal('hide');
                                formElement.submit();
                                simpanPasien();
                            });
                        } else {
                            simpanPasien();
                            formElement.submit();
                        }
                    }
                });
            }
        });

        window.destroy = function(id) {
            // console.log("Tombol hapus diklik untuk ID:", id);
            $('#deleteModal').modal('show');
            $('#confirmDelete').off('click').one('click', function() {
                $.ajax({
                    url: "<?= site_url('patient/destroy') ?>/" + id,
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                    },
                    success: function(response) {

                        if (response.status) {
                            $('#deleteModal').modal('hide');
                            window.location.reload();
                        }

                        // Notif Lebih Modern
                        // $('body').trigger('focus');
                        // $('#deleteModal').modal('hide');
                        // if (response && response.status) {
                        //     iziToast.success({
                        //         title: 'Berhasil',
                        //         message: 'Data pasien berhasil dihapus',
                        //         position: 'topRight'
                        //     })

                        //     $('#table-1').DataTable().ajax.reload(null, false);

                        //     // console.log("DataTable reloaded automatically");
                        // }


                        // // if (typeof table !== 'undefined') {
                        // //     table.ajax.reload(null, false);
                        // // } else {
                        // //     window.location.reload();
                        // // }
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