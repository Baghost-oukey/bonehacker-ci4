<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>MENU SALAM</h4>
                    </div>
                    <div class="card-body">
                        <h5>Salam Yang Ada</h5>
                        <ul class="list-group mb-4">
                            <?php if (isset($greetings) && count($greetings) > 0): ?>
                                <?php foreach ($greetings as $index => $greeting): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($greeting) ?>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-primary mr-2" onclick="editGreeting(<?= $index ?>, '<?= addslashes((string)esc($greeting)) ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="<?= base_url('greeting/delete/' . $index) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus salam ini?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-center text-muted">Belum ada salam. Silakan tambah baru di bawah ini!</li>
                            <?php endif; ?>
                        </ul>

                        <div class="d-flex justify-content-center mb-4">
                            <?= $pager ?>
                        </div>

                        <hr>

                        <h5 id="form-title">Menambah & Mengubah Salam</h5>
                        <form method="POST" action="<?= base_url('greeting/save') ?>" class="form">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <label for="greetings">Salam (Satu baris untuk satu salam):</label>
                                <textarea id="greetings_input" name="greetings" class="form-control" rows="5" placeholder="Ketik salam Anda di sini..." required></textarea>
                            </div>

                            <input type="hidden" id="greeting_index" name="greeting_index" value="">

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Greetings
                                </button>
                                <button type="button" id="btn-cancel" class="btn btn-secondary" style="display:none;" onclick="resetForm()">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    /**
     * Mengisi form untuk proses edit
     * @param {number} index - Index dari array JSON
     * @param {string} text - Isi teks sapaan
     */
    function editGreeting(index, text) {
        document.getElementById('greetings_input').value = text;
        document.getElementById('greeting_index').value = index;
        document.getElementById('form-title').innerText = "Edit Salam (Index: " + index + ")";
        document.getElementById('btn-cancel').style.display = "inline-block";

        // Scroll otomatis ke form agar user tahu sedang mengedit
        document.getElementById('greetings_input').focus();
    }

    /**
     * Mengembalikan form ke mode tambah baru
     */
    function resetForm() {
        document.getElementById('greetings_input').value = "";
        document.getElementById('greeting_index').value = "";
        document.getElementById('form-title').innerText = "Menambah & Mengubah Salam";
        document.getElementById('btn-cancel').style.display = "none";
    }
</script>
<?= $this->endSection() ?>