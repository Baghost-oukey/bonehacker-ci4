<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>File Unggahan</h4>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mauupload">
            <i class="fas fa-upload mr-1"></i> Unggah File Baru
        </button>
    </div>
    <div class="card-body">
        <div id="filePreview" class="mt-3 mb-3"></div>
        <form action="<?= site_url('patient/update_files') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($patient_id) ?>">
            <?php if (!empty($file_urls)) : ?>
                <?php $files = is_array($file_urls) ? $file_urls : (json_decode($file_urls, true) ?? []); ?>
                <?php if (!empty($files)) : ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Aksi</th>
                                <th class="text-center">Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $index => $file_url) :
                                $file_extension = pathinfo($file_url, PATHINFO_EXTENSION);
                                $file_name = basename($file_url);
                            ?>
                                <tr class="align-middle">
                                    <td><span class="text-dark"><?= esc($file_name) ?></span></td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-info">
                                            <?= strtoupper($file_extension) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- <a href="<?= base_url($file_url) ?>" target="_blank" class="btn btn-outline-primary btn-sm previewBtn">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a> -->
                                        <button type="button" class="btn btn-outline-primary btn-sm previewBtn" data-id="<?= $index ?>">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check">
                                            <input class="form-check-input delete-checkbox" type="checkbox" name="delete_files[]" value="<?= $index ?>"
                                                id="delete_file_<?= $index ?>">
                                            <label class="form-check-label text-danger" for="delete_file_<?= $index ?>"><i class="fas fa-trash-alt"></i>Hapus</label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" id="batchDeleteBtn" class="btn btn-danger mt-3 d-none" onclick="return confirm('Yakin ingin menghapus file yang dipilih?')">
                        <i class="fas fa-trash-alt"></i> Hapus File Dipilih
                    </button>

                <?php else : ?>
                    <p class="text-muted mb-0">Tidak Ada File Diunggah.</p>
                <?php endif ?>
            <?php else : ?>
                <p class="text-muted">Belum Ada File Diunggah.</p>
            <?php endif ?>
        </form>
    </div>
</div>


<!-- Modal Upload -->
<div id="mauupload" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mengunggah File Baru</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="uploadAlert" class="alert" style="display:none;"></div>
                <form id="uploadForm" enctype="multipart/form-data">
                    <?= csrf_field() ?> <input type="hidden" name="id" value="<?= esc($patient_id) ?>">

                    <div class="mb-3">
                        <label class="form-label">Pilih Berkas (PDF, JPG, PNG)</label>
                        <br>
                        <small class="text-danger">Maks. Ukuran File: 2MB</small>
                        <input type="file" name="userfiles[]" class="form-control" multiple id="modalFileInput">
                    </div>
                    <button type="submit" class="btn btn-primary">Unggah Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- File Upload Modal preview -->
<div id="fileUploadModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileUploadModalLabel">Uploaded Files</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="fileUploadContent">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Preview</th>
                            <th>Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="fileUploadTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?= $this->section('scripts') ?>
<script>
    function file_preview(id) {

        // $('#fileUploadModal').appendTo('body').modal('show'); // Show the modal
        // $('#fileUploadContent').html(''); // Clear any existing content in the modal

        $('#mauupload').modal('hide');
        var modalPreview = $('#fileUploadModal');
        modalPreview.appendTo('body');
        $('#fileUploadContent').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

        // Assuming `file_urls` is available as a global variable or accessible via AJAX
        var fileUrls = <?= json_encode($file_urls) ?>; // Encode the PHP array into a JSON array
        // console.log("Membuka preview untuk index:", id);
        // console.log("Daftar URL:", fileUrls);

        if (fileUrls[id]) {
            var fileUrl = fileUrls[id];

            if (!fileUrl.startsWith('http')) {
                var baseURL = '<?= base_url() ?>';
                if (!baseURL.endsWith('/') && !fileUrl.startsWith('/')) {
                    baseURL += '/';
                }
                fileUrl = baseURL + 'patient_file/' + fileUrl;
                console.log("Mencoba buka file di: ", fileUrl);
            }
            var fileExtension = fileUrl.split('.').pop().toLowerCase();
            var fileContent = '';

            if (fileExtension === 'pdf') {
                fileContent = '<embed src="' + fileUrl + '" type="application/pdf" width="100%" height="500px" />';
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                fileContent = '<img src="' + fileUrl + '" class="img-fluid" style="max-width: 100%; height: auto;" />';
            } else {
                fileContent = '<a href="' + fileUrl + '" target="_blank">' + fileUrl.split('/').pop() + '</a>';
            }

            $('#fileUploadContent').html(fileContent);

            modalPreview.modal('show');
            modalPreview.off('shown.bs.modal').on('shown.bs.modal', function() {
                var zIndexBase = 1060;
                // Pastikan backdrop ada di bawah modal
                $('.modal-backdrop').last().css('z-index', zIndexBase);
                // Paksa modal ada di atas backdrop
                $(this).css('z-index', zIndexBase + 10);
            });

        } else {
            $('#fileUploadContent').html('<p>No file available for preview.</p>');
        }
    }

    //JS for Upload File
    $(document).ready(function() {
        $('.previewBtn').on('click', function() {
            var id = $(this).data('id');
            file_preview(id);
        });
    });


    // Get references to the batch delete button and checkboxes
    // const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    // const checkboxes = document.querySelectorAll('.delete-checkbox');

    // // Function to toggle the visibility of the batch delete button
    // function toggleBatchDeleteButton() {
    //     const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
    //     if (anyChecked) {
    //         batchDeleteBtn.classList.remove('d-none');
    //     } else {
    //         batchDeleteBtn.classList.add('d-none');
    //     }
    // }


    $(document).ready(function() {
        $(document).on('change', '.delete-checkbox', function() {
            if ($('.delete-checkbox:checked').length > 0) {
                $('#batchDeleteBtn').removeClass('d-none');
            } else {
                $('#batchDeleteBtn').addClass('d-none');

            }
        })
    })

    $(document).ready(function() {

        // Clear alert when file input changes
        $('#modalFileInput').on('change', function() {
            $('#uploadAlert').hide().removeClass('alert-success alert-danger').text('');
        });

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            $('#uploadAlert').hide().removeClass('alert-success alert-danger').text('');
            var files = $('#modalFileInput')[0].files;

            if (files.length === 0) {
                $('#uploadAlert').addClass('alert-danger').text(
                    'Silakan pilih file terlebih dahulu sebelum mengunggah.').show();
                return; // Stop the form submission
            }

            var maxSize = 2048 * 1024; // 2048 KB = 2MB
            var validFormats = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

            for (var i = 0; i < files.length; i++) {
                if (!validFormats.includes(files[i].type)) {
                    $('#uploadAlert').addClass('alert-danger').text('Format file "' + files[i].name +
                        '" tidak didukung. Silakan unggah file dalam format PDF atau gambar (JPEG, JPG, PNG).'
                    ).show();
                    return; // Stop the form submission
                }
                if (files[i].size > maxSize) {
                    $('#uploadAlert').addClass('alert-danger').text('Ukuran file "' + files[i].name +
                        '" melebihi 2MB. Silakan pilih file yang lebih kecil.').show();
                    return; // Stop the form submission
                }
            }

            var formData = new FormData(this);

            $.ajax({
                url: '<?= base_url('patient/update_files') ?>', // URL to your update function
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#uploadAlert').addClass('alert-success').text(
                        'Berkas berhasil diunggah').show();
                    setTimeout(function() {
                        location.reload(); // Reload the page after a short delay
                    }, 600);
                },
                error: function(response) {
                    $('#uploadAlert').addClass('alert-danger').text(
                            'Terjadi kesalahan saat mengunggah berkas. Silakan coba lagi.')
                        .show();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>