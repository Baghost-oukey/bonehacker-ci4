<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Bonehacker</title>
    <link rel="stylesheet" href="<?= base_url('assets/modules/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/toastr/css/toastr.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                        <div class="login-brand">
                            <img src="<?= base_url('assets/img/stisla-fill.svg'); ?>" alt="Logo" width="100" class="shadow-light rounded-circle">
                        </div>
                        <div class="card card-primary">
                            <div class="card-body">
                                <form action="<?= site_url('auth/validate'); ?>" method="post" class="needs-validation" novalidate="" id="loginForm">

                                    <?= csrf_field() ?>

                                    <div class="form-group">
                                        <label>Nama Pengguna</label>
                                        <input type="text" class="form-control" name="username" id="username" value="" required autofocus autocomplete="username">
                                        <div class="invalid-feedback">Nama pengguna tidak boleh kosong</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Kata Sandi</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="password" id="password" required autocomplete="current-password">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">Kata sandi tidak boleh kosong</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="ingat_saya" class="custom-control-input" id="remember-me">
                                            <label class="custom-control-label" for="remember-me">Ingat Saya</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">Masuk</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="simple-footer">&copy; <?= date('Y') ?> Hak Cipta Terpelihara Bonehacker.</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="<?= base_url('assets/modules/jquery.min.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="<?= base_url('assets/modules/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/stisla.js') ?>"></script>
    <script src="<?= base_url('assets/modules/toastr/js/toastr.min.js') ?>"></script>

    <?php if (isset($msg) && is_array($msg) && $msg[0] == '1'): ?>
        <script>
            $(document).ready(function() {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                }
                toastr['<?= $msg[1]; ?>']('<?= $msg[2]; ?>');
            });
        </script>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').click(function() {
                const passwordInput = $('#password');
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                passwordInput.attr('type', type);
                
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            // Auto-fill username dari localStorage
            const savedUsername = localStorage.getItem('saved_username');
            const rememberChecked = localStorage.getItem('remember_me_checked');
            
            if (savedUsername) {
                $('#username').val(savedUsername);
                // Auto-focus ke password jika username sudah terisi
                $('#password').focus();
            }
            
            if (rememberChecked === 'true') {
                $('#remember-me').prop('checked', true);
            }

            // Simpan username ke localStorage saat submit form
            $('#loginForm').on('submit', function() {
                const username = $('#username').val();
                const rememberMe = $('#remember-me').is(':checked');
                
                // Selalu simpan username untuk auto-fill
                if (username) {
                    localStorage.setItem('saved_username', username);
                }
                
                // Simpan status checkbox
                localStorage.setItem('remember_me_checked', rememberMe);
            });
        });
    </script>

    <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
</body>

</html>