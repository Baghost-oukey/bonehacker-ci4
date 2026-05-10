<!DOCTYPE html>
<html lang="id">

<?php
$isDevEnvironment = ENVIRONMENT === 'development';
// Selalu gunakan 127.0.0.1 untuk cek koneksi (lebih cepat di Windows, hindari DNS overhead)
$viteCheckHost = '127.0.0.1';
$vitePort = 5173;
// URL yang diinject ke browser — gunakan 'localhost' agar dapat diakses dari domain .test
// (sudah diizinkan oleh cors config di vite.config.js)
$viteBrowserUrl = 'http://localhost:5173';
$shouldUseViteDevServer = false;

if ($isDevEnvironment) {
    // Dipaksa false agar selalu menggunakan aset hasil build (npm run build)
    // untuk menghindari masalah CORS pada environment Windows/Laragon .test
    $shouldUseViteDevServer = false;
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <?php if ($shouldUseViteDevServer): ?>
        <link rel="stylesheet" href="<?= $viteBrowserUrl ?>/resources/css/app.css">
    <?php else: ?>
        <link rel="stylesheet"
            href="<?= base_url('build/assets/app.css') . '?v=' . (is_file(FCPATH . 'build/assets/app.css') ? filemtime(FCPATH . 'build/assets/app.css') : time()) ?>">
    <?php endif; ?>

    <style>
        .export-hidden {
            display: none;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body>
    <div id="app" class="min-h-screen flex bg-slate-50 text-slate-900">

        <!-- ================= SIDEBAR ================= -->
        <?php if (!isset($isPublic) || !$isPublic): ?>
        <aside id="sidebar" class="
            w-64 bg-white border-r border-slate-200 transition-transform duration-300 ease-in-out flex flex-col
            fixed inset-y-0 left-0 z-40 -translate-x-full
            lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:z-10 lg:shrink-0
        ">
            <?= $this->include('App\Views\layout\sidebar') ?>
        </aside>
        <?php endif; ?>

        <!-- ================= BACKDROP ================= -->
        <div id="sidebarBackdrop" class="fixed inset-0 z-20 hidden bg-black/40 lg:hidden"></div>

        <!-- ================= MAIN ================= -->
        <div class="flex flex-col flex-1 min-h-screen">

            <!-- HEADER -->
            <?php if (!isset($isPublic) || !$isPublic): ?>
            <header class="sticky top-0 z-20 bg-white border-b border-slate-200">

                <div class="flex items-center justify-between px-4 h-14">

                    <!-- TOGGLE BUTTON MOBILE -->
                    <button id="sidebarToggle"
                        class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100">
                        <i class="fas fa-bars text-lg"></i>
                    </button>

                    <!-- HEADER CONTENT -->
                    <div class="flex-1">
                        <?= $this->include('App\Views\layout\header') ?>
                    </div>

                </div>
            </header>
            <?php endif; ?>

            <!-- CONTENT -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
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
        <script type="module" src="<?= $viteBrowserUrl ?>/@vite/client"></script>
        <script type="module" src="<?= $viteBrowserUrl ?>/resources/js/app.js"></script>
    <?php else: ?>
        <script type="module"
            src="<?= base_url('build/assets/app.js') . '?v=' . (is_file(FCPATH . 'build/assets/app.js') ? filemtime(FCPATH . 'build/assets/app.js') : time()) ?>"></script>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const sidebar = document.getElementById("sidebar");
            const backdrop = document.getElementById("sidebarBackdrop");
            const toggle = document.getElementById("sidebarToggle");

            const openSidebar = () => {
                sidebar.classList.remove("-translate-x-full");
                backdrop.classList.remove("hidden");
                document.body.style.overflow = "hidden";
            };

            const closeSidebar = () => {
                sidebar.classList.add("-translate-x-full");
                backdrop.classList.add("hidden");
                document.body.style.overflow = "";
            };

            toggle?.addEventListener("click", openSidebar);
            backdrop?.addEventListener("click", closeSidebar);

            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape") closeSidebar();
            });
        });
    </script>

    <script>
        (function() {
            var csrfName = $('meta[name="csrf-token-name"]').attr('content');

            function refreshCsrfToken() {
                $.get('<?= site_url('auth/get_csrf') ?>', function(data) {
                    $('meta[name="csrf-token-hash"]').attr('content', data.token);
                    $('input[name="' + csrfName + '"]').val(data.token);
                    $.ajaxSetup({
                        data: {
                            [csrfName]: data.token
                        }
                    });
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


                // Sembunyikan alert error bawaan DataTables yang mengganggu (Ajax error)
                if ($.fn.dataTable) {
                    $.fn.dataTable.ext.errMode = 'none';
                }

                // Handle jika sesi habis (Unauthorized 401) pada semua request AJAX
                $(document).ajaxError(function(event, xhr, settings) {
                    if (xhr.status === 401) {
                        window.location.href = '<?= base_url('auth') ?>';
                    }
                });

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