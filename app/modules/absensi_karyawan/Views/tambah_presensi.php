<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-4xl" id="tambahPresensiPage">

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <?php if (session()->get('role') !== 'owner'): ?>
            <a href="<?= base_url('kehadiran') ?>" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition-all">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <?php endif; ?>
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Tambah Presensi</h1>
                <p class="text-slate-500 text-sm mt-1">Pilih tanggal dan terapis untuk menambah presensi</p>
            </div>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        
        <div class="w-full lg:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= base_url('kehadiran') ?>">📅 Rekap Presensi</option>
                <option value="<?= base_url('kehadiran/tambah') ?>" selected>✍️ Input Presensi Baru</option>
                <option value="<?= base_url('kehadiran/cuti') ?>">🏖️ Cuti Karyawan</option>
            </select>
        </div>
    

        <!-- Form -->
        <form id="formTambahPresensi" class="space-y-6">
            <?= csrf_field() ?>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <!-- Pilih Tanggal -->
                <div class="space-y-2 mb-6">
                    <label class="text-sm font-medium text-slate-700">Tanggal Presensi <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="tanggal_presensi" required
                        value="<?= date('Y-m-d') ?>"
                        max="<?= date('Y-m-d') ?>"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <p class="text-xs text-slate-500">Pilih tanggal untuk presensi (maksimal hari ini)</p>
                </div>

                <!-- Pilih Terapis -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Pilih Terapis <span class="text-red-500">*</span></label>
                    
                    <?php if (empty($terapis)): ?>
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 text-center text-slate-400 font-semibold">
                            Tidak ada terapis aktif yang ikut presensi
                        </div>
                    <?php else: ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($terapis as $t): ?>
                                <label class="flex items-center gap-4 p-3 rounded-lg border border-slate-300 bg-white hover:border-teal-500 hover:bg-teal-50 cursor-pointer transition-all">
                                    <input type="checkbox" name="terapis_ids[]" value="<?= $t->id ?>" 
                                        class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" checked>
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-black text-sm">
                                            <?= strtoupper(substr($t->nama, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800"><?= esc($t->nama) ?></p>
                                            <p class="text-xs text-slate-500"><?= esc($t->region_name ?? 'Terapis') ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Select All -->
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="selectAll" 
                                    class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500" checked>
                                <span class="text-sm font-semibold text-slate-700">Pilih Semua Terapis</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-2xl p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-bold mb-1">Informasi:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Status default adalah <strong>Hadir</strong></li>
                                <li>Pilih satu atau lebih terapis</li>
                                <li>Jika terapis sudah ada presensi di tanggal ini, data akan diupdate</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3">
                <a href="<?= session()->get('role') === 'owner' ? base_url('beranda') : base_url('kehadiran') ?>" 
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">
                    Batal
                </a>
                <button type="submit" id="btnSimpan" 
                    class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> Simpan Presensi
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<script>
    window.tambahPresensiConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        storeUrl: "<?= base_url('kehadiran/simpan_presensi_baru') ?>",
        redirectUrl: "<?= session()->get('role') === 'owner' ? base_url('beranda') : base_url('kehadiran') ?>",
    };

    // Inline script untuk handle form
    $(document).ready(function() {
        const selectAllCheckbox = $('#selectAll');
        const terapisCheckboxes = $('input[name="terapis_ids[]"]');

        // Select All functionality
        if (selectAllCheckbox.length && terapisCheckboxes.length > 0) {
            selectAllCheckbox.on('change', function() {
                terapisCheckboxes.prop('checked', this.checked);
            });

            // Update Select All checkbox when individual checkboxes change
            terapisCheckboxes.on('change', function() {
                const allChecked = terapisCheckboxes.toArray().every(cb => cb.checked);
                const someChecked = terapisCheckboxes.toArray().some(cb => cb.checked);
                selectAllCheckbox.prop('checked', allChecked);
                selectAllCheckbox.prop('indeterminate', someChecked && !allChecked);
            });
        }

        // Form submit
        $('#formTambahPresensi').on('submit', function(e) {
            e.preventDefault();
            
            const tanggal = $('#tanggal_presensi').val();
            const selectedTerapis = $('input[name="terapis_ids[]"]:checked').length;

            if (!tanggal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih tanggal presensi terlebih dahulu',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            // Validasi tanggal tidak boleh lebih dari hari ini
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selectedDate = new Date(tanggal);
            selectedDate.setHours(0, 0, 0, 0);

            if (selectedDate > today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: 'Tidak dapat input presensi untuk tanggal masa depan',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            if (selectedTerapis === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih minimal satu terapis',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            const btn = $('#btnSimpan');
            const originalText = btn.html();
            
            btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...').prop('disabled', true).addClass('opacity-70');

            // Ambil data form
            let formData = $(this).serializeArray();
            
            // Tambahkan CSRF Token bila belum ada
            if (!formData.some(field => field.name === window.tambahPresensiConfig.csrfName)) {
                formData.push({ 
                    name: window.tambahPresensiConfig.csrfName, 
                    value: window.tambahPresensiConfig.csrfHash 
                });
            }

            $.ajax({
                url: window.tambahPresensiConfig.storeUrl,
                type: "POST",
                data: $.param(formData),
                dataType: "json",
                success: function(response) {
                    if (response.csrfHash) {
                        window.tambahPresensiConfig.csrfHash = response.csrfHash;
                    }

                    if (response.status === 'success') {
                        // Langsung redirect tanpa alert
                        window.location.href = window.tambahPresensiConfig.redirectUrl;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || "Terjadi kesalahan",
                            confirmButtonColor: '#4f46e5'
                        });
                        btn.html(originalText).prop('disabled', false).removeClass('opacity-70');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Gagal menghubungi server: ' + error,
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.html(originalText).prop('disabled', false).removeClass('opacity-70');
                }
            });
        });
    });
</script>
<?= $this->endSection(); ?>
