<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
        <div class="section-header-breadcrumb">
            <a href="<?= base_url('users') ?>" class="btn btn-primary">Kembali</a>
        </div>
    </div>

    <div id="user-info" data-user-id="<?= $user_id ?>"></div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data <?= ($user_role === 'superadmin') ? 'Semua Pasien' : 'Pasien ' . $region_name ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-patients" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Usia</th>
                                        <th>Alamat</th>
                                        <th>Wilayah</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($user_role !== 'superadmin') : ?>
                    <div class="card">
                        <div class="card-header">
                            <h4>Data Pasien Luar</h4>
                            <div class="card-header-action">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddOutside">Tambah Pasien Luar</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table-patients-luar" class="table table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Usia</th>
                                            <th>Alamat</th>
                                            <th>Wilayah</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalAddOutside" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cari & Tambah Pasien Luar</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addOutsidePatientForm" action="<?= base_url('users/add_outside_patient'); ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Cari Pasien (Nama/NIK)</label>
                        <select id="outsidePatientSelect" name="patient_id" class="form-control select2" required style="width:100%">
                            <option value="">PILIH PASIEN</option>
                        </select>
                    </div>
                    <div id="patientInfo" class="p-3 bg-light rounded" style="display: none; border: 1px solid #e3e3e3;">
                        <h6 class="text-primary border-bottom pb-2">Detail Pasien</h6>
                        <div class="row">
                            <div class="col-6"><small class="text-muted">Gender:</small>
                                <p id="pGender" class="mb-2">-</p>
                            </div>
                            <div class="col-6"><small class="text-muted">Usia:</small>
                                <p id="pAge" class="mb-2">-</p>
                            </div>
                            <div class="col-12"><small class="text-muted">Wilayah:</small>
                                <p id="pWilayah" class="mb-2">-</p>
                            </div>
                            <div class="col-12"><small class="text-muted">Alamat:</small>
                                <p id="pAddress" class="mb-0 small">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        console.log("DataTable Start...");
        const userId = $('#user-info').data('user-id');
        $('#table-patients').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('users/fetch_patients'); ?>",
                type: "POST",
                data: function(d) {
                    d.user_id = userId;
                    d["<?= csrf_token() ?>"] = typeof currentToken !== 'undefined' ? currentToken : "<?= csrf_hash() ?>";
                },
                dataSrc: function(json) {
                    // UPDATE TOKEN SETIAP KALI REQUEST BERHASIL
                    currentToken = json.csrfHash;
                    return json.data;
                }
            },
            columns: [{
                    data: "no",
                    width: "5%",
                    sortable: false
                },
                {
                    data: "nama"
                },
                {
                    data: "gender"
                },
                {
                    data: "age"
                },
                {
                    data: "address"
                },
                {
                    data: "wilayah"
                }
            ],
       

        });

        // 2. Table Pasien Luar
        const tableLuar = $('#table-patients-luar').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('users/fetch_patients_luar'); ?>",
                type: "POST",
                data: function(d) {
                    d.user_id = userId;
                    d.<?= csrf_token() ?> = "<?= csrf_hash() ?>"; // TAMBAHKAN INI
                }
            },
            columns: [{
                    data: "no",
                    width: "5%",
                    sortable: false
                },
                {
                    data: "nama"
                },
                {
                    data: "gender"
                },
                {
                    data: "age"
                },
                {
                    data: "address"
                },
                {
                    data: "wilayah"
                },
                {
                    data: "aksi",
                    class: "text-center",
                    sortable: false
                }
            ]
        });

        // 3. Select2 Remote Data (Cari Pasien Luar)
        $('#outsidePatientSelect').select2({
            dropdownParent: $('#modalAddOutside'),
            ajax: {
                url: '<?= base_url('users/get_outside_patients_select'); ?>',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                data: (params) => ({
                    searchTerm: params.term,
                    user_id: userId,
                    "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                }),
                processResults: (data) => ({
                    results: $.map(data, (item) => ({
                        id: item.id,
                        text: item.text,
                        nama: item.nama,
                        gender: item.gender,
                        age: item.age,
                        address: item.address,
                        wilayah: item.wilayah
                    }))
                })
            }
        });

        // Event saat pasien dipilih dari Select2
        $('#outsidePatientSelect').on('select2:select', function(e) {
            const d = e.params.data;
            $('#pGender').text(d.gender);
            $('#pAge').text(d.age);
            $('#pAddress').text(d.address);
            $('#pWilayah').text(d.wilayah);
            $('#patientInfo').slideDown();
        });

        // 4. Hapus Pasien Luar (SweetAlert)
        $(document).on('click', '.btn-delete-patient', function() {
            const pid = $(this).data('patient-id');
            const uid = $(this).data('user-id');

            Swal.fire({
                title: 'Hapus Pasien?',
                text: "Pasien akan dihapus dari daftar pantauan Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('users/delete_outside_patient'); ?>', {
                        patient_id: pid,
                        user_id: uid
                    }, function(res) {
                        if (res.success) {
                            tableLuar.ajax.reload();
                            Swal.fire('Berhasil', 'Daftar pasien diperbarui', 'success');
                        }
                    }, 'json');
                }
            });
        });

        // 5. Kirim Notifikasi WhatsApp
        $(document).on('click', '.btn-send-wa', function() {
            const pid = $(this).data('patient-id');
            const btn = $(this);

            Swal.fire({
                title: 'Kirim Notifikasi?',
                text: "Kirim pesan pengingat via WhatsApp ke pasien.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Kirim Sekarang'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.addClass('btn-progress disabled');
                    $.post("<?= base_url('whatsapp/send_notif_patients'); ?>/" + pid, function(res) {
                        btn.removeClass('btn-progress disabled');
                        if (res.status === 'success') {
                            Swal.fire('Berhasil!', 'Pesan dalam antrean server.', 'success');
                        } else {
                            Swal.fire('Gagal', 'Gagal menghubungi server WA.', 'error');
                        }
                    }, 'json').fail(() => {
                        btn.removeClass('btn-progress disabled');
                        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>