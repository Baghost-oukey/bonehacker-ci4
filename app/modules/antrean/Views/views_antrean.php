<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Antrean
                            <?php
                            $regionQuery = '';
                            if (isset($regions_patient) && !empty($regions_patient)) {
                                $val = is_array($regions_patient) ? implode(',', $regions_patient) : $regions_patient;
                                $regionQuery = '?region=' . $val;
                            }
                            ?>
                            <a href="<?= site_url('antrean/daftarAntrean') . $regionQuery ?>" class="btn btn-info">Lihat Antrian</a>
                        </h4>
                        <div class="card-header-action">
                            <div class="date-filter mb-3">
                                <label for="startDate">Start Date:</label>
                                <input type="date" id="startDate" class="form-control"
                                    style="display: inline-block; width: auto; margin-right: 10px;"
                                    value="<?= date('Y-m-d') ?>">

                                <label for="endDate">End Date:</label>
                                <input type="date" id="endDate" class="form-control"
                                    style="display: inline-block; width: auto;"
                                    value="<?= date('Y-m-d') ?>">
                            </div>

                            <a href="#" class="btn btn-primary" data-toggle="modal"
                                data-target="#addPatientQueueModal">Tambah Data</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-1" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>ID Pasien</th>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Usia</th>
                                        <th>Alamat</th>
                                        <th>No WA</th>
                                        <th>Keterangan</th>
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

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">Anda yakin ingin menghapus data ini?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<div id="addPatientQueueModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="card-header">
                <h4>Daftar Pasien</h4>
                <div class="card-header-action">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Tambah Data</a>
                </div>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>ID Pasien</th>
                                <th>Nama</th>
                                <th>Alamat</th>
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

<div id="exampleModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Pasien</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="<?= site_url('patient/store') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select class="form-control" name="gender" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Man">Laki-laki</option>
                                    <option value="Woman">Perempuan</option>
                                </select>
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

                    <div id="keterangan_rentan" class="form-group" style="display: none;">
                        <label>Keterangan Rentan</label>
                        <textarea class="form-control" name="ket_rentan" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Domestik</label>
                        <div class="d-block">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="dom1" name="domestic" value="dalam_negeri" class="custom-control-input" checked>
                                <label class="custom-control-label" for="dom1">Dalam Negeri</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="dom2" name="domestic" value="luar_negeri" class="custom-control-input">
                                <label class="custom-control-label" for="dom2">Luar Negeri</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="country-group" style="display: none;">
                        <label>Negara</label>
                        <select class="form-control" name="country_id">
                            <option value="">PILIH</option>
                            <?php foreach ($negara as $v): ?>
                                <option value="<?= $v->id ?>"><?= $v->country ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="desa-group">
                        <label>Pencarian Desa</label>
                        <select class="form-control select2" name="desa_id" id="desa_id" style="width: 100%;">
                            <option value="">Temukan Desa</option>
                        </select>
                    </div>

                    <div class="form-group" id="region-group">
                        <label>Wilayah</label>
                        <select class="form-control" name="region_id" id="region_id">
                            <option value="">PILIH</option>
                            <?php foreach ($wilayah as $v): ?>
                                <?php
                                $selected = '';
                                if (isset($regions_patient) && !empty($regions_patient)) {
                                    if (is_array($regions_patient)) {
                                        $selected = in_array($v->id, $regions_patient) ? 'selected' : '';
                                    } else {
                                        $selected = ($v->id == $regions_patient) ? 'selected' : '';
                                    }
                                }
                                ?>
                                <option value="<?= $v->id ?>" <?= $selected ?>><?= $v->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Umur</label>
                                <input type="number" class="form-control" name="age">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>No. WhatsApp</label>
                                <input type="number" id="phone" class="form-control" name="phone">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat Jalan</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Kedatangan</label>
                        <input type="datetime-local" class="form-control" name="visit_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Simpan Pasien</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {

        function getCsrfData() {
            return {
                "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
            };
        }

        $.ajaxSetup({
            data: getCsrfData()
        });

        // Inisialisasi DataTables Antrean
        var table1 = $('#table-1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('antrean/fetchDataTable') ?>",
                type: "POST",
                data: function(d) {
                    $.extend(d, getCsrfData());
                    d.region = $('#region_id').val() || '';
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                },
            },
            columns: [{
                    data: 'patient_id'
                },
                {
                    data: 'date'
                }, // Controller menggunakan ->add('date', ...)
                {
                    data: 'patient_name'
                }, // Sesuai alias p.name as patient_name
                {
                    data: 'age'
                },
                {
                    data: 'address_full'
                }, // Sesuai ->add('address_full', ...)
                {
                    data: 'phone'
                },
                {
                    data: 'description'
                }, // Sesuai ->add('description', ...)
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-right'
                }
            ]
        });

        // Inisialisasi DataTables Pasien
        var table2;

        $('#addPatientQueueModal').on('shown.bs.modal', function() {
            console.log('MODAL SHOWN');
            if (!$.fn.DataTable.isDataTable('#table-2')) {
                table2 = $('#table-2').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "<?= site_url('antrean/fetchPatientDataTables') ?>",
                        type: "POST",
                        data: function(d) {
                            d.<?= csrf_token() ?> = "<?= csrf_hash() ?>";
                            d.region = $('#region_id').val() || '';
                        }
                    },
                    columns: [{
                            data: 'patient_id'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'address'
                        },
                        {
                            data: 'description'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ]
                });
            } else {
                table2.ajax.reload(null, false); // Reload tanpa reset paging
                table2.columns.adjust().draw(); // Perbaiki layout kolom
            }
        });


        $('#startDate, #endDate, #region_id').on('change', function() {
            table1.ajax.reload();
            table2.ajax.reload();
        });

        if ($('#search_patient').length > 0) {
            $('#search_patient').select2({
                placeholder: "Temukan Pasien",
                ajax: {
                    url: '<?= site_url('antrean/fetchJson') ?>',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1,
                            ...getCsrfData()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.items.map(item => ({
                                id: item.id,
                                text: item.name
                            })),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    }
                },
                minimumInputLength: 1
            });
        }

        // Select2 Wilayah Desa
        $('#desa_id').select2({
            placeholder: "Temukan Desa",
            dropdownParent: $("#exampleModal"),
            ajax: {
                url: 'https://wilayah.smartsociety.id/public/desa',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    let options = [];
                    if (data.data && data.data.data) {
                        $.each(data.data.data, function(index, item) {
                            let subText = item.kecamatan ? `Kec. ${item.kecamatan.kecNama}, ${item.kecamatan.kabupaten.kabNama}` : '';
                            options.push({
                                id: item.desIdDesa,
                                text: `<strong>${item.desNama}</strong><br><small>${subText}</small>`,
                                full_data: item
                            });
                        });
                    }
                    return {
                        results: options,
                        pagination: {
                            more: data.data?.next_page_url ? true : false
                        }
                    };
                }
            },
            minimumInputLength: 1,
            escapeMarkup: m => m,
            templateResult: i => i.text,
            templateSelection: i => i.text ? i.text.replace(/<br\s*\/?>/gi, ' ').replace(/<\/?[^>]+(>|$)/g, "") : i.text
        });

        // Auto-fill dari Desa
        $('#desa_id').on('select2:select', function(e) {
            const d = e.params.data.full_data;
            $('#desa_nama').val(d.desNama || '');
            $('#kecamatan_id').val(d.kecamatan?.kecIdKecamatan || '');
            $('#kecamatan_nama').val(d.kecamatan?.kecNama || '');
            $('#kabupaten_id').val(d.kecamatan?.kabupaten?.kabIdKabupaten || '');
            $('#kabupaten_nama').val(d.kecamatan?.kabupaten?.kabNama || '');
            $('#provinsi_id').val(d.kecamatan?.kabupaten?.provinsi?.provIdProvinsi || '');
            $('#provinsi_nama').val(d.kecamatan?.kabupaten?.provinsi?.provNama || '');
        });

        // Logika UI Toggle
        $('#isSuspectiveCheckbox').on('change', function() {
            $('#keterangan_rentan').css('display', this.checked ? 'block' : 'none');
        });

        $('input[name="domestic"]').on('change', function() {
            const isLuarNegeri = (this.value === 'luar_negeri');
            $('#country-group').toggle(isLuarNegeri);
            $('#desa-group, #region-group').toggle(!isLuarNegeri);
        });

        if ($('input[name="domestic"]:checked').length > 0) {
            $('input[name="domestic"]:checked').trigger('change');
        }

        // Hapus Data
        let deleteId = null;
        window.destroy = function(id) {
            deleteId = id;
            $('#deleteModal').modal('show');
        };

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;
            $.ajax({
                url: "<?= site_url('antrean/destroy') ?>/" + deleteId,
                type: "POST",
                data: getCsrfData(),
                dataType: "JSON",
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        });

        // Pengecekan Nomor WA saat submit form
        $('#submitBtn').on('click', function(e) {
            e.preventDefault();
            const $form = $(this).closest('form');
            const phone = $('#phone').val();

            $.ajax({
                url: '<?= site_url('patient/check_phone') ?>',
                type: 'POST',
                data: {
                    phone: phone,
                    ...getCsrfData()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        alert("Nomor HP sudah terdaftar di sistem.");
                    } else {
                        $form[0].submit();
                    }
                },
                error: function() {
                    alert('Gagal melakukan verifikasi nomor HP.');
                }
            });
        });

        // Cegah submit pada pencarian Select2 jika dienter
        $(document).on('keypress', '.select2-search__field', function(e) {
            if (e.which === 13) e.preventDefault();
        });
    });
</script>
<?= $this->endSection() ?>