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
                        <h4>Data Users</h4>
                        <div class="card-header-action">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalAdd">Tambah Data</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-user" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Wilayah</th>
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

<div id="modalAdd" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('users/store') ?>" method="post" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama *</label>
                        <input type="text" class="form-control" name="realname" required>
                        <div class="invalid-feedback">Nama user tidak boleh kosong</div>
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" class="form-control" name="username" id="username_add" required>
                        <div class="invalid-feedback" id="usernameError">Username tidak boleh kosong</div>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control role-select" name="role" data-target="#regionFieldAdd" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="superadmin">Super Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="form-group" id="regionFieldAdd" style="display: none;">
                        <label>Wilayah</label>
                        <select class="form-control select2" name="regions_patient[]" style="width:100%" multiple>
                            <?php foreach ($regions as $region) : ?>
                                <option value="<?= $region->id ?>"><?= $region->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtnAdd">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalEdit" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Data User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama *</label>
                        <input type="text" class="form-control" id="edit_realname" name="realname" required>
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                        <div class="invalid-feedback" id="editUsernameError"></div>
                    </div>
                    <div class="form-group">
                        <label>Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control role-select" id="edit_role" name="role" data-target="#regionFieldEdit" required>
                            <option value="superadmin">Super Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="form-group" id="regionFieldEdit" style="display: none;">
                        <label>Wilayah</label>
                        <select class="form-control select2" id="edit_regions" name="regions_patient[]" style="width:100%" multiple>
                            <?php foreach ($regions as $region) : ?>
                                <option value="<?= $region->id ?>"><?= $region->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtnEdit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Initialization DataTable
        var table = $('#table-user').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'asc']],
            ajax: {
                url: "<?= base_url('users/fetch'); ?>",
                type: "POST",
                data: function(d) {
                    d.search_value = d.search.value;
                    d.<?= csrf_token() ?> = $('meta[name="csrf-token-hash"]').attr('content');
                },

                dataSrc: function(json) {
                    if (json.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', json.csrfHash);
                    }
                    return json.data;
                }
            },
            columns: [{
                    data: "no",
                    width: "5%",
                    sortable: false,
                    searchable: false
                },
                {
                    data: "realname",
                    width: "25%"
                },
                
                {
                    data: "username",
                    width: "15%"
                },
                {
                    data: "role",
                    width: "15%"
                },
                {
                    data: "region_name",
                    width: "20%",
                    orderable: false
                },
                {
                    data: "action",
                    class: "text-right",
                    width: "20%",
                    sortable: false
                }
            ]
        });

        // Toggle Region Field based on Role
        $(document).on('change', '.role-select', function() {
            const target = $(this).data('target');
            if ($(this).val() === 'user') {
                $(target).fadeIn();
                $(target).find('select').attr('required', true);
            } else {
                $(target).fadeOut();
                $(target).find('select').attr('required', false).val(null).trigger('change');
            }
        });

        // Edit Button Handler
        $(document).on('click', '.btn_edit', function() {
            const d = $(this).data();
            const modal = $('#modalEdit');

            modal.find('form').attr('action', d.href);
            $('#edit_realname').val(d.realname);
            $('#edit_username').val(d.username);
            $('#edit_role').val(d.role).trigger('change');

            if (d.regions_patient) {
                $('#edit_regions').val(d.regions_patient).trigger('change');
            }

            modal.modal('show');
        });

        // Delete Handler (SweetAlert recommended)
        $(document).on('click', '.btn_delete', function() {
            const url = $(this).data('href');
            swal({
                title: "Konfirmasi Hapus",
                text: "Apakah Anda yakin ingin menghapus user ini?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) window.location.href = url;
            });
        });

        // Real-time Username Validation (Add)
        $('#username_add').on('keyup', function() {
            const username = $(this).val();
            if (username.length < 3) return;

            $.post("<?= base_url('users/check_username_exists') ?>", {
                username: username
            }, function(res) {
                if (res.exists) {
                    $('#username_add').addClass('is-invalid');
                    $('#usernameError').text('Username sudah digunakan');
                    $('#submitBtnAdd').prop('disabled', true);
                } else {
                    $('#username_add').removeClass('is-invalid');
                    $('#submitBtnAdd').prop('disabled', false);
                }
            }, 'json');
        });

        // Redirect to Patient Management
        $(document).on('click', '.btn_add_patient', function() {
            window.location.href = "<?= base_url('users/view_patient') ?>/" + $(this).data('userid');
        });
    });
</script>
<?= $this->endSection() ?>