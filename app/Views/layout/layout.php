<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

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

    <style>
        .dataTables_wrapper .dataTables_filter { float: right; }
        .dataTables_wrapper .dataTables_length { display: inline-block; margin-right: 20px; }
        .export-hidden { display: none; }
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

    <script>
        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfHash = $('meta[name="csrf-token-hash"]').attr('content');

        function updateAjaxSetup(newHash) {
            if (csrfName && newHash) {
                $.ajaxSetup({
                    data: { [csrfName]: newHash }
                });
                $('meta[name="csrf-token-hash"]').attr('content', newHash);
            }
        }

        updateAjaxSetup(csrfHash);

        $(document).ajaxComplete(function(event, xhr, settings) {
            let newHash = xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newHash) {
                updateAjaxSetup(newHash);
            }
        });
    </script>

    <?php if(session()->has('msg')): ?>
        <?php $msg = session('msg'); ?>
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