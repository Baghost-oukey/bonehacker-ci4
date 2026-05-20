<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section id="jaspelSettingsPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500">
                Atur nominal jaspel dan terapis yang berhak menerima per cabang
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= site_url('jasa-pelayanan/reguler') ?>" 
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <i class="fas fa-arrow-left text-slate-500"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form Pengaturan -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Pengaturan Jaspel Per Cabang</h3>
            <p class="text-sm text-slate-500">Tentukan nominal per pasien dan terapis yang berhak menerima jaspel</p>
        </div>

        <div class="p-6">
            <form id="formJaspelSettings" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Pilih Cabang -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-800">Cabang <span class="text-red-500">*</span></label>
                    <select name="region_id" id="region_id" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">-- Pilih Cabang --</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region->id ?>"><?= esc($region->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500">Pilih cabang untuk mengatur jaspel</p>
                </div>

                <!-- Tipe Jaspel -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-800">Tipe Jaspel <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipe" value="reguler" checked
                                class="text-teal-600 focus:ring-teal-500">
                            <span class="text-sm text-slate-700 font-medium">Reguler</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipe" value="kejantanan"
                                class="text-teal-600 focus:ring-teal-500">
                            <span class="text-sm text-slate-700 font-medium">Kejantanan</span>
                        </label>
                    </div>
                    <p class="text-xs text-slate-500">Reguler dan Kejantanan memiliki nominal yang berbeda</p>
                </div>

                <!-- Nominal Per Pasien -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-800">Nominal Per Pasien <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                        <input type="number" name="nominal_per_pasien" id="nominal_per_pasien" required min="0" step="1000"
                            placeholder="5000"
                            class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <p class="text-xs text-slate-500">Nominal ini akan dibagi rata ke terapis yang hadir</p>
                </div>

                <!-- Pilih Terapis -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-800">Terapis Yang Berhak Menerima Jaspel</label>
                    <div id="terapisList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 border border-slate-200 rounded-lg p-4 max-h-64 overflow-y-auto">
                        <p class="col-span-full text-sm text-slate-400 italic">Pilih cabang terlebih dahulu</p>
                    </div>
                    <p class="text-xs text-slate-500">Centang terapis yang berhak menerima jaspel di cabang ini</p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Cara Kerja Pembagian Jaspel:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Jaspel hanya diberikan kepada terapis yang <strong>hadir</strong> sesuai presensi</li>
                                <li>Setiap terapis yang hadir mendapatkan nominal penuh per pasien</li>
                                <li>Contoh: Rp 5.000 per pasien. Jika ada 10 pasien, setiap terapis yang hadir hari itu mendapatkan Rp 5.000 × 10 = Rp 50.000</li>
                                <li>Terapis yang tidak hadir tidak mendapat jaspel</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="window.location.href='<?= site_url('jasa-pelayanan/reguler') ?>'"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btnSave"
                        class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Removed static tables from bottom as they are moved to their respective report tables index pages -->
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const allTerapis = <?= json_encode($all_terapis) ?>;
    const existingSettings = <?= json_encode(array_merge($settings ?? [], $settings_kejantanan ?? [])) ?>;

    $(document).ready(function() {
        // Load terapis when region or tipe changes
        $('#region_id, input[name="tipe"]').on('change', function() {
            const regionId = $('#region_id').val();
            if (!regionId) {
                $('#terapisList').html('<p class="col-span-full text-sm text-slate-400 italic">Pilih cabang terlebih dahulu</p>');
                $('#nominal_per_pasien').val('');
                return;
            }
            loadTerapisByRegion(regionId);
            loadExistingSettings(regionId);
        });

        // Submit form
        $('#formJaspelSettings').on('submit', function(e) {
            e.preventDefault();

            const regionId  = $('#region_id').val();
            const nominal   = $('#nominal_per_pasien').val();
            const tipe      = $('input[name="tipe"]:checked').val();
            const terapisIds = [];

            $('input[name="terapis_ids[]"]:checked').each(function() {
                terapisIds.push($(this).val());
            });

            if (!regionId || !nominal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: 'Cabang dan nominal wajib diisi',
                    confirmButtonColor: '#0d9488'
                });
                return;
            }

            $('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');

            $.ajax({
                url: '<?= site_url('jasa-pelayanan/saveSettings') ?>',
                type: 'POST',
                data: {
                    region_id: regionId,
                    nominal_per_pasien: nominal,
                    tipe: tipe,
                    terapis_ids: terapisIds,
                    <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.csrfHash) {
                        $('input[name="<?= csrf_token() ?>"]').val(response.csrfHash);
                    }
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            confirmButtonColor: '#0d9488'
                        }).then(() => {
                            window.location.href = '<?= site_url('jasa-pelayanan/') ?>' + tipe;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal menyimpan pengaturan',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Terjadi kesalahan saat menyimpan pengaturan',
                        confirmButtonColor: '#ef4444'
                    });
                },
                complete: function() {
                    $('#btnSave').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Simpan Pengaturan');
                }
            });
        });

        // Handle URL parameters for editing setting
        const urlParams = new URLSearchParams(window.location.search);
        const urlRegionId = urlParams.get('region_id');
        const urlTipe = urlParams.get('tipe');

        if (urlRegionId && urlTipe) {
            $(`input[name="tipe"][value="${urlTipe}"]`).prop('checked', true);
            $('#region_id').val(urlRegionId).trigger('change');
        }
    });

    function loadTerapisByRegion(regionId) {
        const terapisInRegion = allTerapis.filter(t => t.region_id == regionId);

        if (terapisInRegion.length === 0) {
            $('#terapisList').html('<p class="col-span-full text-sm text-slate-400 italic">Tidak ada terapis di cabang ini</p>');
            return;
        }

        let html = '';
        terapisInRegion.forEach(terapis => {
            html += `
                <label class="flex items-center gap-3 p-2.5 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer transition duration-150">
                    <input type="checkbox" name="terapis_ids[]" value="${terapis.id}" 
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                    <span class="text-sm font-semibold text-slate-700">${terapis.nama}</span>
                </label>
            `;
        });

        $('#terapisList').html(html);

        // Load existing settings setelah terapis di-render
        loadExistingSettings(regionId);
    }

    function loadExistingSettings(regionId) {
        const tipe    = $('input[name="tipe"]:checked').val();
        const setting = existingSettings.find(s => s.region_id == regionId && s.tipe == tipe);

        // Reset dulu
        $('#nominal_per_pasien').val('');
        $('input[name="terapis_ids[]"]').prop('checked', false);

        if (setting) {
            $('#nominal_per_pasien').val(setting.nominal_per_pasien);
            const terapisIds = JSON.parse(setting.terapis_ids || '[]');
            terapisIds.forEach(id => {
                $(`input[name="terapis_ids[]"][value="${id}"]`).prop('checked', true);
            });
        }
    }
</script>

<?= $this->endSection() ?>
