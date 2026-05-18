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
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">
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
        html,
        body {
            max-width: 100vw;
            overflow-x: hidden;
            position: relative;
            -webkit-overflow-scrolling: touch;
        }

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
            lg:translate-x-0 lg:z-30 lg:shrink-0
        ">
                <?= $this->include('App\Views\layout\sidebar') ?>
            </aside>
        <?php endif; ?>

        <!-- ================= BACKDROP ================= -->
        <div id="sidebarBackdrop" class="fixed inset-0 z-20 hidden bg-black/40 lg:hidden"></div>

        <!-- ================= MAIN ================= -->
        <div class="flex flex-col flex-1 min-h-screen min-w-0 lg:pl-64 transition-all duration-300">

            <!-- HEADER -->
            <?php if (!isset($isPublic) || !$isPublic): ?>
                <?= $this->include('App\Views\layout\header') ?>
            <?php endif; ?>

            <!-- CONTENT -->
            <main class="flex-1 p-4 md:p-6 mt-14">
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
            // Cleanup backdrop yang mungkin tersisa
            const cleanupBackdrops = () => {
                // Hapus semua SweetAlert backdrop
                document.querySelectorAll('.swal2-container').forEach(el => el.remove());
                document.querySelectorAll('.swal2-backdrop-show').forEach(el => el.remove());

                // Hapus Select2 backdrop
                document.querySelectorAll('.select2-dropdown').forEach(el => el.remove());
                document.querySelectorAll('.select2-container--open').forEach(el => {
                    el.classList.remove('select2-container--open');
                });

                // Reset body
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                document.body.classList.remove('swal2-shown', 'swal2-height-auto');
            };

            // Jalankan cleanup
            cleanupBackdrops();

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

    <!-- Pull to Refresh khusus Flutter App (BoneHacker-App) -->
    <script>
        (function() {
            if (!navigator.userAgent.includes('BoneHacker-App')) return;

            let startY = 0;
            let currentY = 0;
            let isPulling = false;
            const threshold = 100; // Pull threshold in pixels
            
            // Create the pull indicator element dynamically
            const pullIndicator = document.createElement('div');
            pullIndicator.style.position = 'fixed';
            pullIndicator.style.top = '-60px';
            pullIndicator.style.left = '50%';
            pullIndicator.style.transform = 'translateX(-50%)';
            pullIndicator.style.width = '42px';
            pullIndicator.style.height = '42px';
            pullIndicator.style.borderRadius = '50%';
            pullIndicator.style.backgroundColor = '#ffffff';
            pullIndicator.style.boxShadow = '0 4px 12px rgba(30, 58, 138, 0.15), 0 2px 4px rgba(0, 0, 0, 0.05)';
            pullIndicator.style.display = 'flex';
            pullIndicator.style.alignItems = 'center';
            pullIndicator.style.justifyContent = 'center';
            pullIndicator.style.zIndex = '9999';
            pullIndicator.style.transition = 'top 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            pullIndicator.innerHTML = '<i class="fas fa-sync-alt text-indigo-600" style="transition: transform 0.1s linear;"></i>';
            
            // Add custom animation style for spin
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes spin-refresh {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .animate-spin-refresh {
                    animation: spin-refresh 0.8s linear infinite !important;
                }
            `;
            document.head.appendChild(style);
            document.body.appendChild(pullIndicator);
            
            document.addEventListener('touchstart', function(e) {
                // Only trigger if we are at the very top of the page
                if (window.scrollY === 0) {
                    startY = e.touches[0].pageY;
                    isPulling = true;
                }
            }, { passive: true });
            
            document.addEventListener('touchmove', function(e) {
                if (!isPulling) return;
                
                currentY = e.touches[0].pageY;
                const diff = currentY - startY;
                
                if (diff > 0) {
                    // Dragging down
                    const topVal = Math.min(diff / 2, threshold) - 50; 
                    pullIndicator.style.top = topVal + 'px';
                    
                    const rotation = Math.min(diff * 2, 360);
                    const icon = pullIndicator.querySelector('i');
                    if (icon && !icon.classList.contains('animate-spin-refresh')) {
                        icon.style.transform = `rotate(${rotation}deg)`;
                    }
                } else {
                    isPulling = false;
                }
            }, { passive: true });
            
            document.addEventListener('touchend', function(e) {
                if (!isPulling) return;
                isPulling = false;
                
                const diff = currentY - startY;
                if (diff > threshold) {
                    // Trigger refresh
                    pullIndicator.style.top = '20px';
                    const icon = pullIndicator.querySelector('i');
                    if (icon) icon.classList.add('animate-spin-refresh');
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                } else {
                    // Cancel pull
                    pullIndicator.style.top = '-60px';
                }
            }, { passive: true });
        })();
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>