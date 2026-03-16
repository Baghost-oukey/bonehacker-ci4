<div class="card mt-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h4>File Unggahan</h4>
        <button type="button" class="btn btn-primary btn-rounded shadow-sm" data-toggle="modal" data-target="#mauupload" style="border-radius: 20px;">
            <i class="fas fa-upload mr-1"></i> Unggah File Baru
        </button>
    </div>
    <div class="card-body">
        <div id="filePreview" class="mb-3"></div>

        <form action="<?= site_url('patient/update_files') ?>" method="POST" id="formDeleteFiles">

            <?= csrf_field() ?>

            <input type="hidden" name="id" value="<?= esc($patient_id) ?>">

            <?php
            $files = [];
            if (!empty($file_urls)) {
                $files = is_array($file_urls) ? $file_urls : json_decode($file_urls, true);
            }
            ?>

            <?php if (!empty($files) && is_array($files)) : ?>
                <div class="table-responsive">
                    <table class="table table-borderless table-hover">
                        <thead class="text-muted small text-uppercase">
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
                                        <span class="badge badge-soft-info p-2" style="background-color: #e0f2f1; color: #00897b; border-radius: 4px;">
                                            <?= strtoupper($file_extension) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url($file_url) ?>" target="_blank" class="btn btn-sm btn-light text-primary border shadow-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="delete_files[]" value="<?= $index ?>"
                                                class="custom-control-input delete-checkbox" id="del_<?= $index ?>">
                                            <label class="custom-control-label" for="del_<?= $index ?>"></label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" id="batchDeleteBtn" class="btn btn-danger btn-sm d-none mt-2"
                    onclick="return confirm('Apakah Anda yakin ingin menghapus file yang dipilih?')">
                    <i class="fas fa-trash mr-1"></i> Hapus Terpilih
                </button>

            <?php else : ?>
                <div>
                    <p class="text-muted mb-0">Belum Ada File Diunggah.</p>
                </div>
            <?php endif ?>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
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
        $('#modalFileInput').on('change', function() {
            $('#uploadAlert').hide().removeClass('alert-success alert-danger').text('');
        });

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            $('#uploadAlert').hide().removeClass('alert-success alert-danger').text('');
            var files = $('#modalFileInput')[0].files;
            if (files.length === 0) {
                $('#uploadAlert').addClass('alert-danger').text(
                    'Silakan pilih file terlebih dahulu sebelum mengunggah.').show();
                return;
            }

            var maxSize = 2048 * 1024;
            var validFormats = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            for (var i = 0; i < files.length; i++) {
                if (!validFormats.includes(files[i].type)) {
                    $('#uploadAlert').addClass('alert-danger').text('Format file "' + files[i].name +
                        '" tidak didukung. Silakan unggah file dalam format PDF atau gambar (JPEG, JPG, PNG).'
                    ).show();
                    return;
                }
                if (files[i].size > maxSize) {
                    $('#uploadAlert').addClass('alert-danger').text('Ukuran file "' + files[i].name +
                        '" melebihi 2MB. Silakan pilih file yang lebih kecil.').show();
                    return;
                }
            }

            var formData = new FormData(this);

            $.ajax({
                url: '<?= base_url('patient/update_files') ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#uploadAlert').addClass('alert-success').text(
                        'Berkas berhasil diunggah').show();
                    setTimeout(function() {
                        location.reload();
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