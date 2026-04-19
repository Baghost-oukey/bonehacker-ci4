<!DOCTYPE html>
<html lang="id">

<?php
$isDevEnvironment = ENVIRONMENT === 'development';
$viteDevServer = rtrim((string) (env('vite.devServerUrl') ?? 'http://localhost:5173'), '/');
$shouldUseViteDevServer = false;

if ($isDevEnvironment) {
  $viteHost = (string) parse_url($viteDevServer, PHP_URL_HOST);
  $vitePort = (int) (parse_url($viteDevServer, PHP_URL_PORT) ?: 5173);

  if ($viteHost !== '') {
    $viteSocket = @fsockopen($viteHost, $vitePort, $errno, $errstr, 0.15);
    if (is_resource($viteSocket)) {
      $shouldUseViteDevServer = true;
      fclose($viteSocket);
    }
  }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-header" content="<?= csrf_token() ?>" id="csrf-header">
    <meta name="csrf-token" content="<?= csrf_hash() ?>" id="csrf-token">

    <title><?= $title ?? 'Dashboard' ?> | Bonehacker</title>

    <link rel="stylesheet" href="<?= base_url('assets/modules/fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/datatables/datatables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/modules/toastr/css/toastr.min.css') ?>">

    <?php if ($shouldUseViteDevServer): ?>
        <link rel="stylesheet" href="<?= $viteDevServer ?>/resources/css/app.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?= base_url('build/assets/app.css') . '?v=' . (is_file(FCPATH . 'build/assets/app.css') ? filemtime(FCPATH . 'build/assets/app.css') : time()) ?>">
    <?php endif; ?>

    <style>
        .export-hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div id="app" class="min-h-screen text-slate-900">
        <?= $this->include('App\Views\layout\sidebar') ?>

        <div id="sidebarBackdrop" class="fixed inset-0 z-20 hidden bg-slate-900/40 lg:hidden"></div>

        <div class="min-h-screen lg:ml-72">
            <?= $this->include('App\Views\layout\header') ?>

            <main class="w-full">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <script src="<?= base_url('assets/modules/jquery.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= base_url('assets/modules/nicescroll/jquery.nicescroll.min.js') ?>"></script>
    <script src="<?= base_url('assets/modules/datatables/datatables.min.js') ?>"></script>
    <script src="<?= base_url('assets/modules/toastr/js/toastr.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ini juga kalo mau pakai notif yang modern -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script> -->
    <!-- Kalo mau pakai tag diaktifkan -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <?php if ($shouldUseViteDevServer): ?>
        <script type="module" src="<?= $viteDevServer ?>/@vite/client"></script>
        <script type="module" src="<?= $viteDevServer ?>/resources/js/app.js"></script>
    <?php else: ?>
        <script type="module" src="<?= base_url('build/assets/app.js') . '?v=' . (is_file(FCPATH . 'build/assets/app.js') ? filemtime(FCPATH . 'build/assets/app.js') : time()) ?>"></script>
    <?php endif; ?>

    <script>
        (function() {
            // var metaName = document.querySelector('meta[name="csrf-token-name"]');
            // var metaHash = document.querySelector('meta[name="csrf-token-hash"]');
            // var csrfName = metaName ? metaName.getAttribute('content') : null;
            var csrfName = $('meta[name="csrf-token-name"]').attr('content');
            // var csrfHash = metaHash ? metaHash.getAttribute('content') : null;

            function refreshCsrfToken() {
                $.get('<?= site_url('auth/get_csrf') ?>', function(data) {
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
