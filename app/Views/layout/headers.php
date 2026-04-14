 <style>
        /* CSS agar dropdown rapi dan bisa di-scroll */
        .dropdown-list-content.dropdown-list-icons {
            max-height: 350px !important; /* Batasi tinggi agar muncul scroll */
            overflow-y: auto !important;  /* Aktifkan scroll vertikal */
            scrollbar-width: thin;        /* Styling scrollbar untuk browser modern */
        }
        .dropdown-item-active {
            background-color: #f8f9fa !important;
            border-left: 4px solid #6777ef !important; /* Memberi tanda warna primer pada cabang aktif */
        }
        .dropdown-item-desc b {
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>

<header class="navbar navbar-expand-lg bg-primary">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <?php if (session()->get('role') === 'owner' || session()->get('role') === 'superadmin'): ?>

            <li class="dropdown dropdown-list-toggle">
                <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg" title="Pindah Cabang">
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="d-none d-lg-inline-block ml-1" style="font-size: 13px; font-weight: 600;">
                        <?= (session()->get('active_region') == 'all') ? 'Global View' : 'Cabang: ' . (session()->get('active_region_name') ?? 'Terpilih') ?>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-list dropdown-menu-right" style="width: 280px;">
                    <div class="dropdown-header">Pilih Cabang Pantauan</div>
                    <div class="dropdown-list-content dropdown-list-icons">

                        <a href="javascript:void(0)"
                            class="dropdown-item switch-region <?= (session()->get('active_region') == 'all') ? 'dropdown-item-active' : '' ?>"
                            data-id="all"
                            data-name="Semua Wilayah">
                            <div class="dropdown-item-icon bg-primary text-white">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="dropdown-item-desc">
                                <b>Semua Cabang</b>
                                <div class="time">MODE GLOBAL</div>
                            </div>
                        </a>

                        <?php
                        $all_regions = session()->get('list_regions_global') ?? [];
                        foreach ($all_regions as $rg):
                        ?>
                            <a href="javascript:void(0)"
                                class="dropdown-item switch-region <?= (session()->get('active_region') == $rg['id']) ? 'dropdown-item-active' : '' ?>"
                                data-id="<?= $rg['id'] ?>"
                                data-name="<?= $rg['name'] ?>">
                                <div class="dropdown-item-icon bg-info text-white">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="dropdown-item-desc">
                                    <b><?= $rg['name'] ?></b>
                                    <div class="time">MODE CABANG</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </li>
        <?php endif; ?>
        <li class="dropdown">
            <a href="javascript:void(0)" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img src="<?= base_url('assets/img/avatar/default.png') ?>" class="rounded-circle mr-1">
                <div class="d-sm-none d-lg-inline-block">Hi, <?= $realname ?? 'User' ?> </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="javascript:void(0)" class="dropdown-item has-icon" id="editAccountBtn">
                    <i class="far fa-user"></i> Akun Saya
                </a>
                <a href="<?= site_url('auth/destroy') ?>" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </li>
    </ul>
</header>

<div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccountModalLabel">Edit Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAccountForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div id="accountAlert" class="alert" style="display: none;"></div>
                    <div class="form-group">
                        <label for="realname">Nama Lengkap</label>
                        <input type="text" class="form-control" id="realname" name="realname" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (Kosongkan jika tidak ingin mengganti)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <input type="hidden" id="user_id" name="user_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 1. Membuka modal dan fetch user data
        $('#editAccountBtn').on('click', function() {
            $('#accountAlert').removeClass('alert-success alert-danger').hide();
            $('#editAccountModal').modal('show');

            $.ajax({
                // Perbaikan site_url ke sintaks PHP
                url: '<?= site_url("users/edit_account") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#realname').val(response.realname);
                    $('#username').val(response.username);
                    $('#user_id').val(response.userId);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching user data:', error);
                }
            });
        });

        // 2. Handle submit form via AJAX
        $('#editAccountForm').on('submit', function(event) {
            event.preventDefault();
            $('#accountAlert').removeClass('alert-success alert-danger').hide();

            var formData = $(this).serialize();

            $.ajax({
                // Perbaikan site_url ke sintaks PHP
                url: '<?= site_url("users/update_account") ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#accountAlert').addClass('alert-success').text(response.message).show();
                        $('.nav-link-user .d-lg-inline-block').text('Hi, ' + response.realname);

                        setTimeout(function() {
                            $('#editAccountModal').modal('hide');
                            location.reload();
                        }, 1000);
                    } else {
                        $('#accountAlert').addClass('alert-danger').text(response.message).show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#accountAlert').addClass('alert-danger').text('Gagal memperbarui akun.').show();
                }
            });
        });

        $('#editAccountModal').on('hidden.bs.modal', function() {
            $('#editAccountForm')[0].reset();
            $('#accountAlert').hide();
        });

            // Handle Klik Switch Region
            $(document).on('click', '.switch-region', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                const name = $(this).data('name');
                const $this = $(this);

                // Tambahkan efek loading visual biar pro
                $this.css('opacity', '0.5');

                $.ajax({
                    url: '<?= site_url("auth/switch_region") ?>',
                    type: 'POST',
                    data: {
                        region_id: id,
                        region_name: name,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Refresh halaman agar session baru terbaca di header & konten
                            window.location.reload();
                        }
                    },
                    error: function() {
                        alert('Gagal berpindah wilayah. Silakan coba lagi.');
                        $this.css('opacity', '1');
                    }
                });
            });
    });
</script>