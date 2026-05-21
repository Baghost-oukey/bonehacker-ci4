<?= $this->extend('layout/layout'); ?>

<?= $this->section('content'); ?>
<div class="container mx-auto px-4 py-8 max-w-6xl" id="tambahPresensiPage">

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <?php if (session()->get('role') !== 'owner'): ?>
                <a href="<?= base_url('kehadiran') ?>" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition-all">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <?php endif; ?>
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Tambah Presensi</h1>
                    <p class="text-slate-500 text-sm mt-1">Pilih tanggal dan isi presensi terapis.</p>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Tanggal Presensi</p>
                    <input type="date" id="tanggal_presensi" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
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
        <form id="formTambahPresensi" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="tanggal" id="tanggal_presensi_hidden" value="<?= date('Y-m-d') ?>">

            <?php if (empty($terapis)): ?>
                <div class="bg-white rounded-4xl border border-slate-200 p-8 text-center text-slate-400 font-semibold shadow-sm">
                    Belum ada data terapis aktif.
                </div>
            <?php else: ?>
                <?php foreach ($terapis as $index => $t): ?>
                    <div class="rounded-4xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-teal-100 text-teal-700 font-bold text-xl">
                                    <?= strtoupper(substr($t->nama, 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 uppercase"><?= esc($t->nama) ?></p>
                                    <p class="text-xs font-medium text-slate-500 mt-0.5"><?= esc($t->region_name ?? 'Terapis') ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-slate-700">Keterangan</span>
                                <input type="text" name="absen[<?= $index ?>][keterangan]" placeholder="Sakit / Izin..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 lg:grid-cols-[1.3fr_1fr] lg:items-center">
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="absen[<?= $index ?>][terapis_id]" value="<?= $t->id ?>">
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="absen[<?= $index ?>][status]" value="Hadir" class="peer sr-only" checked>
                                    <div class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-all peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                        <i class="fas fa-check-circle"></i> Hadir
                                    </div>
                                </label>
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="absen[<?= $index ?>][status]" value="Tidak Hadir" class="peer sr-only">
                                    <div class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-all peer-checked:border-rose-500 peer-checked:ring-1 peer-checked:ring-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700">
                                        <i class="fas fa-times-circle"></i> Tidak Hadir
                                    </div>
                                </label>
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="absen[<?= $index ?>][status]" value="Izin" class="peer sr-only">
                                    <div class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-all peer-checked:border-blue-500 peer-checked:ring-1 peer-checked:ring-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700">
                                        <i class="fas fa-info-circle"></i> Izin
                                    </div>
                                </label>
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="absen[<?= $index ?>][status]" value="Cuti" class="peer sr-only">
                                    <div class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-all peer-checked:border-orange-500 peer-checked:ring-1 peer-checked:ring-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700">
                                        <i class="fas fa-calendar-times"></i> Cuti
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="sticky bottom-0 z-40 bg-slate-50/80 pt-4 backdrop-blur-sm">
                <div class="mx-auto flex w-full max-w-6xl justify-center px-4 py-3">
                    <button type="submit" id="btnSimpan" class="inline-flex items-center justify-center rounded-3xl bg-teal-600 px-8 py-4 text-sm font-bold uppercase tracking-[0.2em] text-white shadow-lg shadow-teal-200 transition-all hover:bg-teal-700">
                        <i class="fas fa-save mr-3"></i> Simpan Presensi Baru
                    </button>
                </div>
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

    $(document).ready(function() {
        const config = window.tambahPresensiConfig;
        const swalLib = window.Swal || window.swal;
        const formTambahPresensi = $('#formTambahPresensi');
        const tanggalInput = $('#tanggal_presensi');
        const tanggalHidden = $('#tanggal_presensi_hidden');

        // Sinkronisasi tanggal input dengan hidden field
        if (tanggalInput.length && tanggalHidden.length) {
            tanggalInput.on('change', function() {
                const newDate = $(this).val();
                tanggalHidden.val(newDate);
            });
        }

        formTambahPresensi.on('submit', function(e) {
            e.preventDefault();
            
            const tanggal = tanggalHidden.val();
            if (!tanggal) {
                swalLib.fire({
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
                swalLib.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: 'Tidak dapat input presensi untuk tanggal masa depan',
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
            if (!formData.some(field => field.name === config.csrfName)) {
                formData.push({ name: config.csrfName, value: config.csrfHash });
            }

            $.ajax({
                url: config.storeUrl,
                type: "POST",
                data: $.param(formData),
                dataType: "json",
                success: function(response) {
                    if (response.csrfHash) config.csrfHash = response.csrfHash; 

                    if (response.status === 'success') {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Tersimpan!',
                            text: response.message,
                            confirmButtonColor: '#4f46e5',
                            customClass: { popup: 'rounded-3xl' }
                        }).then(() => {
                            window.location.href = config.redirectUrl;
                        });
                    } else {
                        swalLib.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || "Terjadi kesalahan",
                            confirmButtonColor: '#4f46e5'
                        });
                        btn.html(originalText).prop('disabled', false).removeClass('opacity-70');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    swalLib.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Gagal menghubungi server.',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.html(originalText).prop('disabled', false).removeClass('opacity-70');
                }
            });
        });
    });
</script>
<?= $this->endSection(); ?>
