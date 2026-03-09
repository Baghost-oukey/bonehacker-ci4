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
                                <form action="<?= site_url('auth/validate'); ?>" method="post" class="needs-validation" novalidate="">

                                    <?= csrf_field() ?>

                                    <div class="form-group">
                                        <label>Nama Pengguna</label>
                                        <input type="text" class="form-control" name="username" value="<?= old('username') ?>" required autofocus>
                                        <div class="invalid-feedback">Nama pengguna tidak boleh kosong</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Kata Sandi</label>
                                        <input type="password" class="form-control" name="password" required>
                                        <div class="invalid-feedback">Kata sandi tidak boleh kosong</div>
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
    <script src="<?= base_url('assets/modules/popper.js') ?>"></script>
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

    <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
</body>

</html>