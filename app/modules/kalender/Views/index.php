<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section class="w-full space-y-6 p-4 md:p-6">

    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">Kalender Kerja</h1>
            <p class="text-sm text-slate-500">
                <?= $role === 'superadmin' ? 'Kelola hari libur global untuk semua cabang' : 'Kalender hari libur cabang Anda' ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Tahun -->
            <form method="GET" class="flex items-center gap-2">
                <select name="tahun" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 focus:border-teal-500 focus:outline-none">
                    <?php for ($y = date('Y') + 1; $y >= date('Y') - 2; $y--): ?>
                        <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>

            <?php if ($can_copy): ?>
                <button id="btnCopyGlobal"
                    class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-medium text-teal-700 transition hover:bg-teal-100">
                    <i class="fas fa-copy"></i>
                    <?= $has_kalender_cabang ? 'Sync Ulang dari Global' : 'Copy dari Global' ?>
                    <?php if ($global_count > 0): ?>
                        <span class="rounded-full bg-teal-600 px-1.5 py-0.5 text-[10px] text-white"><?= $global_count ?></span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>

            <?php if ($can_edit): ?>
                <button id="btnTambahLibur"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    <i class="fas fa-plus"></i>
                    Tambah Libur
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kalender Visual -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Nav Bulan -->
            <div class="flex items-center justify-between bg-white rounded-2xl border border-slate-200 px-5 py-3 shadow-sm">
                <button id="btnPrevBulan" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 id="judulBulan" class="text-base font-bold text-slate-800"></h2>
                <button id="btnNextBulan" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Grid Kalender -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden text-sm">
                <div class="grid grid-cols-7 border-b border-slate-100">
                    <?php foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $i => $hari): ?>
                        <div class="py-2 text-center text-[10px] font-bold uppercase tracking-wider <?= $i === 0 ? 'text-red-500' : 'text-slate-500' ?>">
                            <?= $hari ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="gridKalender" class="grid grid-cols-7 gap-px bg-slate-100"></div>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-100 border border-red-300"></div>
                    <span>Hari Libur</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-indigo-100 border border-indigo-300"></div>
                    <span>Hari Ini</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-slate-100 border border-slate-200"></div>
                    <span>Hari Kerja</span>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Daftar Libur -->
        <div class="space-y-4">

            <?php if ($role === 'superadmin' && $can_edit): ?>
            <!-- Libur Rutin -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700">Libur Rutin Mingguan</h3>
                    <button id="btnTambahRutin"
                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Tambah
                    </button>
                </div>
                <div class="p-4 space-y-2">
                    <?php if (empty($libur_rutin)): ?>
                        <p class="text-xs text-slate-400 italic text-center py-2">Belum ada libur rutin</p>
                    <?php else: ?>
                        <?php
                        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        foreach ($libur_rutin as $lr):
                        ?>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">
                                        Setiap <?= $namaHari[$lr['hari_rutin']] ?>
                                    </p>
                                    <p class="text-xs text-slate-500"><?= esc($lr['keterangan']) ?></p>
                                </div>
                                <button onclick="hapusLibur(<?= $lr['id'] ?>)"
                                    class="text-slate-300 hover:text-red-500 transition p-1">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Daftar Libur Khusus -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-3">
                    <h3 class="text-sm font-bold text-slate-700">
                        Libur Khusus <?= $tahun ?>
                        <span class="ml-1 text-xs font-normal text-slate-400">(<?= count($libur_khusus) ?> hari)</span>
                    </h3>
                </div>
                <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
                    <?php if (empty($libur_khusus)): ?>
                        <div class="p-6 text-center text-slate-400 text-sm italic">Belum ada libur khusus</div>
                    <?php else: ?>
                        <?php
                        $bulan_indo = [
                            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                            7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
                        ];
                        foreach ($libur_khusus as $lk):
                            $tgl = date('d', strtotime($lk['tanggal']));
                            $bln = $bulan_indo[(int)date('m', strtotime($lk['tanggal']))];
                            $isGlobal = $lk['region_id'] === null;
                        ?>
                            <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-100 flex flex-col items-center justify-center shrink-0">
                                        <span class="text-[10px] font-bold text-red-400 uppercase"><?= $bln ?></span>
                                        <span class="text-sm font-black text-red-600"><?= $tgl ?></span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800"><?= esc($lk['keterangan']) ?></p>
                                        <?php if ($isGlobal && $role !== 'superadmin'): ?>
                                            <span class="text-[10px] text-teal-600 font-medium">Global</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($can_edit && (!$isGlobal || $role === 'superadmin')): ?>
                                    <button onclick="hapusLibur(<?= $lk['id'] ?>)"
                                        class="text-slate-300 hover:text-red-500 transition p-1 shrink-0">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Modal Tambah Libur Khusus -->
<div id="modalTambahLibur" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-base font-bold text-slate-800">Tambah Hari Libur</h5>
            <button class="close-modal rounded-md p-2 text-slate-400 hover:bg-slate-100">&times;</button>
        </div>
        <form id="formTambahLibur" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="inputTanggal" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Keterangan <span class="text-red-500">*</span></label>
                <input type="text" name="keterangan" required placeholder="Contoh: Libur Lebaran, Libur Nasional..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="close-modal rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Libur Rutin -->
<div id="modalTambahRutin" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-base font-bold text-slate-800">Tambah Libur Rutin Mingguan</h5>
            <button class="close-modal rounded-md p-2 text-slate-400 hover:bg-slate-100">&times;</button>
        </div>
        <form id="formTambahRutin" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="tahun" value="<?= $tahun ?>">
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Hari <span class="text-red-500">*</span></label>
                <select name="hari_rutin" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">-- Pilih Hari --</option>
                    <option value="0">Minggu</option>
                    <option value="1">Senin</option>
                    <option value="2">Selasa</option>
                    <option value="3">Rabu</option>
                    <option value="4">Kamis</option>
                    <option value="5" selected>Jumat</option>
                    <option value="6">Sabtu</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Keterangan</label>
                <input type="text" name="keterangan" value="Libur Rutin" placeholder="Contoh: Libur Jumat"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="close-modal rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.kalenderConfig = {
    csrfName:     '<?= csrf_token() ?>',
    csrfHash:     '<?= csrf_hash() ?>',
    tahun:        <?= $tahun ?>,
    bulan:        <?= $bulan ?>,
    semuaLibur:   <?= json_encode(array_values($semua_libur)) ?>,
    liburKhusus:  <?= json_encode($libur_khusus) ?>,
    urlStore:     '<?= site_url('kalender/store') ?>',
    urlStoreRutin:'<?= site_url('kalender/store-rutin') ?>',
    urlDestroy:   '<?= site_url('kalender/destroy') ?>',
    urlCopy:      '<?= site_url('kalender/copy-global') ?>',
    canEdit:      <?= $can_edit ? 'true' : 'false' ?>,
    canCopy:      <?= $can_copy ? 'true' : 'false' ?>,
};

$(document).ready(function () {
    const cfg = window.kalenderConfig;
    let currentBulan = cfg.bulan;
    let currentTahun = cfg.tahun;

    const bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];

    // ---- RENDER KALENDER ----
    function renderKalender(tahun, bulan) {
        const grid = $('#gridKalender');
        grid.empty();

        const today = new Date();
        const firstDay = new Date(tahun, bulan - 1, 1).getDay(); // 0=Minggu
        const daysInMonth = new Date(tahun, bulan, 0).getDate();

        $('#judulBulan').text(bulanIndo[bulan - 1] + ' ' + tahun);

        // Padding awal
        for (let i = 0; i < firstDay; i++) {
            grid.append('<div class="h-14 lg:h-20 bg-white"></div>');
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const mm = String(bulan).padStart(2, '0');
            const dd = String(d).padStart(2, '0');
            const tgl = `${tahun}-${mm}-${dd}`;
            const isToday = (today.getFullYear() === tahun && today.getMonth() + 1 === bulan && today.getDate() === d);
            const isLibur = cfg.semuaLibur.includes(tgl);
            const dayOfWeek = new Date(tahun, bulan - 1, d).getDay();
            const isFriday = dayOfWeek === 5;

            let cls = 'h-14 lg:h-20 bg-white flex flex-col items-center justify-center text-sm font-medium transition cursor-default relative ';

            if (isToday) {
                cls += 'text-indigo-700 font-bold';
            } else if (isLibur) {
                cls += 'bg-red-50 text-red-600';
            } else if (dayOfWeek === 0) { // Minggu
                cls += 'text-red-500';
            } else {
                cls += 'text-slate-700 hover:bg-slate-50';
            }

            // Tooltip keterangan libur
            let title = '';
            let indicator = '';
            if (isLibur) {
                const found = cfg.liburKhusus.find(l => l.tanggal === tgl);
                title = found ? ` title="${found.keterangan}"` : ' title="Libur Rutin"';
                indicator = '<div class="absolute bottom-1 w-1.5 h-1.5 rounded-full bg-red-400"></div>';
            } else if (isToday) {
                indicator = '<div class="absolute bottom-1 w-1.5 h-1.5 rounded-full bg-indigo-500"></div>';
            }

            grid.append(`<div class="${cls}"${title}><span>${d}</span>${indicator}</div>`);
        }
    }

    renderKalender(currentTahun, currentBulan);

    $('#btnPrevBulan').on('click', function () {
        currentBulan--;
        if (currentBulan < 1) { currentBulan = 12; currentTahun--; }
        renderKalender(currentTahun, currentBulan);
    });

    $('#btnNextBulan').on('click', function () {
        currentBulan++;
        if (currentBulan > 12) { currentBulan = 1; currentTahun++; }
        renderKalender(currentTahun, currentBulan);
    });

    // ---- MODAL ----
    function openModal(id) { $(id).removeClass('hidden').addClass('flex'); }
    function closeAllModals() { $('.fixed.z-50').removeClass('flex').addClass('hidden'); }

    $('#btnTambahLibur').on('click', () => openModal('#modalTambahLibur'));
    $('#btnTambahRutin').on('click', () => openModal('#modalTambahRutin'));
    $(document).on('click', '.close-modal', closeAllModals);
    $(document).on('click', '.fixed.z-50', function(e) {
        if (e.target === this) closeAllModals();
    });

    // ---- SIMPAN LIBUR KHUSUS ----
    $('#formTambahLibur').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: cfg.urlStore,
            type: 'POST',
            data: $(this).serialize() + `&${cfg.csrfName}=${cfg.csrfHash}`,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            complete: () => btn.prop('disabled', false).text('Simpan')
        });
    });

    // ---- SIMPAN LIBUR RUTIN ----
    $('#formTambahRutin').on('submit', function (e) {
        e.preventDefault();
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: cfg.urlStoreRutin,
            type: 'POST',
            data: $(this).serialize() + `&${cfg.csrfName}=${cfg.csrfHash}`,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            complete: () => btn.prop('disabled', false).text('Simpan')
        });
    });

    // ---- HAPUS LIBUR ----
    window.hapusLibur = function (id) {
        Swal.fire({
            title: 'Hapus hari libur ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: cfg.urlDestroy + '/' + id,
                type: 'POST',
                data: { [cfg.csrfName]: cfg.csrfHash, _method: 'DELETE' },
                dataType: 'json',
                success: function (res) {
                    if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                }
            });
        });
    };

    // ---- COPY GLOBAL ----
    $('#btnCopyGlobal').on('click', function () {
        Swal.fire({
            title: 'Salin kalender global?',
            text: 'Kalender cabang Anda untuk tahun <?= $tahun ?> akan diganti dengan kalender global. Anda bisa edit setelahnya.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Salin',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: cfg.urlCopy,
                type: 'POST',
                data: { tahun: cfg.tahun, [cfg.csrfName]: cfg.csrfHash },
                dataType: 'json',
                success: function (res) {
                    if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                }
            });
        });
    });

    // Set tanggal default input ke bulan yang sedang ditampilkan
    $('#btnTambahLibur').on('click', function () {
        const mm = String(currentBulan).padStart(2, '0');
        $('#inputTanggal').val(`${currentTahun}-${mm}-01`);
    });
});
</script>
<?= $this->endSection() ?>
