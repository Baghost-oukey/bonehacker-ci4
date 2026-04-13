<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-header" content="<?= csrf_token() ?>" id="csrf-header">
    <meta name="csrf-token" content="<?= csrf_hash() ?>" id="csrf-token">

    <title><?= $title ?? 'Dashboard' ?> | Bonehacker</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/modules/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/datatables/datatables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/toastr/css/toastr.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <!-- Buat Aktifkan Lib notif yang modern -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .dataTables_wrapper .dataTables_length {
            display: inline-block;
            margin-right: 20px;
        }

        .export-hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>

            <?= $this->include('App\Views\layout\headers') ?>

            <div class="main-content">
                <?= $this->renderSection('content') ?>
            </div>

            <?= $this->include('App\Views\layout\sidebar') ?>
            <?= $this->include('App\Views\layout\footer') ?>
        </div>
    </div>

    <script src="<?= base_url('assets/modules/jquery.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= base_url('assets/modules/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('assets/modules/nicescroll/jquery.nicescroll.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/stisla.js') ?>"></script>
    <script src="<?= base_url('assets/modules/datatables/datatables.min.js') ?>"></script>
    <script src="<?= base_url('assets/modules/toastr/js/toastr.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ini juga kalo mau pakai notif yang modern -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script> -->
    <!-- Kalo mau pakai tag diaktifkan -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

    <script>
        (function() {
            // var metaName = document.querySelector('meta[name="csrf-token-name"]');
            // var metaHash = document.querySelector('meta[name="csrf-token-hash"]');
            // var csrfName = metaName ? metaName.getAttribute('content') : null;
            var csrfName = $('meta[name="csrf-token-name"]').attr('content');
            // var csrfHash = metaHash ? metaHash.getAttribute('content') : null;

            function refreshCsrfToken() {
                $.get('<?= site_url('auth/get_csrf'); ?>', function(data) {
                    $('meta[name="csrf-token-hash"]').attr('content', data.token);
                    // 2. Update semua input hidden yang dibuat pake csrf_field()
                    $('input[name="' + csrfName + '"]').val(data.token);
                    // 3. Update settingan global JQuery AJAX
                    $.ajaxSetup({
                        data: {
                            [csrfName]: data.token
                        }
                    });
                    // console.log('CSRF token Berhasil Diperbarui');
                });
            }

            $(document).ready(function() {
                // Set up awal pas halaman load
                var initialHash = $('meta[name="csrf-token-hash"]').attr('content');
                if (csrfName && initialHash) {
                    $.ajaxSetup({
                        data: {
                            [csrfName]: initialHash
                        }
                    });
                }


                // Jurus Anti-Macet: Setiap request POST selesai, kita minta token baru
                $(document).ajaxComplete(function(event, xhr, settings) {
                    if (settings.type === 'POST' || settings.type === 'post') {
                        refreshCsrfToken();
                    }
                });
            });

            // function updateAjaxSetup(newHash) {
            //     if (window.jQuery && csrfName && newHash) {
            //         $.ajaxSetup({
            //             data: {
            //                 [csrfName]: newHash
            //             }
            //         });
            //         var hashField = document.querySelector('meta[name="csrf-token-hash"]');
            //         if (hashField) hashField.setAttribute('content', newHash);
            //     }
            // }

            // // Tunggu dokumen siap untuk perintah jQuery
            // document.addEventListener('DOMContentLoaded', function() {
            //     if (window.jQuery) {
            //         updateAjaxSetup(csrfHash);
            //         $(document).ajaxComplete(function(event, xhr, settings) {
            //             var newHash = xhr.getResponseHeader('X-CSRF-TOKEN');
            //             if (newHash) updateAjaxSetup(newHash);
            //         });
            //     }
            // });
        })();
    </script>

    <?php if (session()->has('message')): ?>
        <?php $msg = session('message'); ?>
        <script>
            $(document).ready(function() {
                toastr.options = {
                    "progressBar": true,
                    "positionClass": "toast-top-right"
                };

                toastr['<?= $msg[0] ?>']('<?= $msg[1] ?>');
            });
        </script>
    <?php endif; ?>

    <?= $this->renderSection('scripts') ?>
</body>

</html>