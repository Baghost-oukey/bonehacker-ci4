<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= esc($title) ?></h1>
        <div class="section-header-breadcrumb">
            <a href="<?= site_url('terapis') ?>" class="btn btn-primary">Kembali</a>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <form action="<?= site_url('terapis/update') ?>" id="detailterapis" method="post"
                    class="needs-validation" novalidate enctype="multipart/form-data">

                    <?= csrf_field() ?>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Biodata</h4>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="photo-container"
                                            style="position: relative; display: inline-block; margin-right:100px">
                                            <img id="photo-preview"
                                                src="<?= base_url('foto_terapis/' . ($terapis->foto ? $terapis->foto : 'no_profile.png')) ?>"
                                                alt="Foto Profil" class="img-fluid rounded"
                                                style="width: 200px; height: 200px; object-fit: cover; cursor: pointer; border: 1px solid #969696;">

                                            <div class="overlay"
                                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                                                       background-color: #00000080; display: none; justify-content: center; 
                                                       align-items: center; border-radius: 8px; pointer-events: none;">
                                                <button type="button" class="btn btn-primary btn-sm mr-2"
                                                    onclick="triggerEdit()"><i class="fas fa-pen"></i></button>
                                                <button type="button" class="btn btn-success btn-sm mr-2"
                                                    onclick="previewImageModal()"><i class="fas fa-eye"></i></button>
                                                <?php if ($terapis->foto): ?>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="confirmDeletePhoto()"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-sm" onclick="previewQr()"><i
                                                class="fas fa-user-circle mr-2"></i>Info Publik</button>

                                        <input type="file" class="form-control-file d-none" id="foto"
                                            name="foto" accept="image/*" onchange="previewImage(event)">
                                        <div class="invalid-feedback">Foto tidak boleh kosong</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="terapis_id">ID</label>
                                        <input type="hidden" name="id" value="<?= $terapis->id ?>">
                                        <input type="text" class="form-control" name="terapis_id" id="terapis_id"
                                            value="<?= esc($terapis->terapis_id) ?>" required>
                                        <div id="idError" class="invalid-feedback">ID tidak boleh kosong</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="nama">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama" id="nama"
                                            value="<?= esc($terapis->nama) ?>" required>
                                        <div class="invalid-feedback">Nama lengkap tidak boleh kosong</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="tempat_lahir">Tempat Lahir</label>
                                        <input type="text" class="form-control" name="tempat_lahir" id="tempat_lahir"
                                            value="<?= esc($terapis->tempat_lahir) ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="tgl_lahir">Tanggal Lahir</label>
                                        <input type="date" class="form-control" name="tgl_lahir" id="tgl_lahir"
                                            value="<?= $terapis->tanggal_lahir ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="alamat">Alamat</label>
                                        <textarea rows="3" class="form-control" name="alamat" id="alamat"><?= esc($terapis->alamat) ?></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group" id="region-group">
                                        <label for="region_id">Wilayah Kerja</label>
                                        <select class="form-control" name="region_id" id="region_id">
                                            <option value="">PILIH</option>
                                            <?php foreach ($wilayah as $region): ?>
                                                <option value="<?= $region->id ?>"
                                                    <?= $region->id == $terapis->region_id ? 'selected' : '' ?>>
                                                    <?= esc($region->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Wilayah tidak boleh kosong</div>
                                    </div>

                                    <div class="form-group" id="jabatan-group">
                                        <label for="jabatan_id">Jabatan</label>
                                        <select class="form-control" name="jabatan_id" id="jabatan_id">
                                            <option value="">PILIH</option>
                                            <?php foreach ($jabatan as $jab): ?>
                                                <option value="<?= $jab->id ?>"
                                                    <?= $jab->id == $terapis->jabatan_id ? 'selected' : '' ?>>
                                                    <?= esc($jab->nama_jabatan) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group" id="rank-group">
                                        <label for="rank">Rank</label>
                                        <select class="form-control" id="rank" name="rank">
                                            <option value="">--Pilih Rank--</option>
                                            <?php $ranks = ['SS', 'S', 'A', 'B', 'C']; ?>
                                            <?php foreach ($ranks as $r): ?>
                                                <option value="<?= $r ?>" <?= $terapis->rank == $r ? 'selected' : '' ?>><?= $r ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="tgl_kerja">Tanggal Mulai Kerja</label>
                                        <input type="date" class="form-control" name="tgl_kerja"
                                            value="<?= isset($terapis->tgl_mulai_kerja) ? date('Y-m-d', strtotime($terapis->tgl_mulai_kerja)) : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="keterangan">Keterangan</label>
                                        <textarea rows="3" class="form-control" name="keterangan" id="keterangan"><?= esc($terapis->keterangan) ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Status</label> <br>
                                        <div class="custom-switch">
                                            <label class="custom-switch mt-2">
                                                <input type="checkbox" name="status" class="custom-switch-input"
                                                    <?= $terapis->is_active == 1 ? 'checked' : '' ?>>
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Aktif</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="fileErrorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kesalahan Unggah File</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="fileErrorMessage"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="photoPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Foto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="full-photo-preview" class="img-fluid" src="" alt="Preview Foto">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePhotoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Foto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('terapis/deletefoto') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $terapis->id ?>">
                <input type="hidden" name="terapis_id" value="<?= $terapis->terapis_id ?>">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus foto ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="previewQrModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR code info publik terapis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="<?= $qr_code_base64 ?>" alt="QR Code Terapis" class="img-fluid">
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <a href="<?= $qr_code_base64 ?>" download="qr_code_terapis.png" class="btn btn-info text-center">
                    <i class="fas fa-download mr-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Overlay logic
    const photoContainer = document.querySelector('.photo-container');
    const overlay = document.querySelector('.overlay');

    photoContainer.addEventListener('mouseenter', () => {
        overlay.style.display = 'flex';
        overlay.style.pointerEvents = 'auto';
    });

    photoContainer.addEventListener('mouseleave', () => {
        overlay.style.display = 'none';
        overlay.style.pointerEvents = 'none';
    });

    function triggerEdit() {
        document.getElementById('foto').click();
    }

    function previewQr() {
        $('#previewQrModal').modal('show');
    }

    function previewImageModal() {
        document.getElementById('full-photo-preview').src = document.getElementById('photo-preview').src;
        $('#photoPreviewModal').modal('show');
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('photo-preview').src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function confirmDeletePhoto() {
        $('#deletePhotoModal').modal('show');
    }

    // Validation ID via AJAX
    $(document).ready(function() {
        $('#terapis_id').on('input', function() {
            const id = $(this).val();
            const currentId = '<?= $terapis->terapis_id ?>';
            const submitBtn = $('.btn-success');
            const errorElement = $('#idError');

            if (id.trim() === '') {
                setInvalid(errorElement, submitBtn, 'ID tidak boleh kosong.');
                return;
            }

            $.ajax({
                url: '<?= base_url('terapis/checkId') ?>',
                type: 'POST',
                data: {
                    terapis_id: id,
                    currentId: currentId,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        setInvalid(errorElement, submitBtn, 'ID sudah ada, gunakan ID lain.');
                    } else {
                        setValid(errorElement, submitBtn);
                    }
                }
            });
        });

        function setInvalid(el, btn, msg) {
            $('#terapis_id').addClass('is-invalid');
            el.text(msg).show();
            btn.prop('disabled', true);
        }

        function setValid(el, btn) {
            $('#terapis_id').removeClass('is-invalid');
            el.hide();
            btn.prop('disabled', false);
        }
    });

    // Form submission validation
    document.getElementById('detailterapis').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('foto');
        const file = fileInput.files[0];

        if (file) {
            const validFormats = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validFormats.includes(file.type)) {
                e.preventDefault();
                showErrorModal('Format file harus JPG, JPEG, atau PNG.');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                e.preventDefault();
                showErrorModal('Ukuran file maksimal 2MB.');
                return;
            }
        }
    });

    function showErrorModal(msg) {
        document.getElementById('fileErrorMessage').innerText = msg;
        $('#fileErrorModal').modal('show');
    }
</script>
<?= $this->endSection() ?>