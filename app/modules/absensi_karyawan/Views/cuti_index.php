<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<div class="p-6 space-y-6 bg-slate-50/50 min-h-screen" id="cutiPage">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Cuti Karyawan</h1>
            <p class="text-slate-500 text-sm mt-1">Atur kuota dan input cuti karyawan Bone Hacker.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full lg:hidden mt-4">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= base_url('kehadiran') ?>">📅 Rekap Presensi</option>
                <option value="<?= base_url('kehadiran/tambah') ?>">✍️ Input Presensi Baru</option>
                <option value="<?= base_url('kehadiran/cuti') ?>" selected>🏖️ Cuti Karyawan</option>
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openModalTambahCuti()" class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-teal-100">
                <i class="fas fa-plus"></i>
                Input Cuti
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kuota Cuti List -->
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-calendar-check text-teal-600"></i>
                    Kuota Cuti Karyawan
                </h3>
            </div>
            <div class="p-4 overflow-y-auto max-h-[600px] custom-scrollbar">
                <div class="space-y-3">
                    <?php foreach ($terapis as $t): ?>
                    <div class="p-4 rounded-xl border border-slate-100 hover:border-teal-200 hover:bg-teal-50/30 transition-all group">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 text-sm"><?= esc($t->nama) ?></span>
                                <span class="text-[10px] text-slate-400">
                                    Sisa: <span class="font-bold text-teal-600"><?= $t->sisa ?></span> 
                                    <span class="mx-1">/</span> 
                                    Kuota: <span class="font-bold text-slate-600"><?= $t->jatah_cuti ?> Hari</span>
                                </span>
                            </div>
                            <button onclick="editKuota('<?= $t->id ?>', '<?= esc($t->nama) ?>', '<?= $t->jatah_cuti ?>')" class="opacity-0 group-hover:opacity-100 p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-100 rounded-lg transition-all">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Riwayat Cuti Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-history text-teal-600"></i>
                    Riwayat Cuti Terbaru
                </h3>
            </div>
            <!-- Tampilan Desktop & Tablet (Table) -->
            <div class="hidden md:block overflow-y-auto max-h-[600px] custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Karyawan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Periode</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Durasi</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Keterangan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($cuti)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                Belum ada data cuti yang diinput.
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($cuti as $c): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800 text-sm"><?= esc($c->nama_terapis) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700"><?= date('d M Y', strtotime($c->tanggal_mulai)) ?></span>
                                    <span class="text-[10px] text-slate-400 font-medium">s/d <?= date('d M Y', strtotime($c->tanggal_selesai)) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-md bg-teal-50 text-teal-600 text-[10px] font-black uppercase">
                                    <?= $c->jumlah_hari ?> Hari
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 italic"><?= esc($c->keterangan) ?></td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="hapusCuti('<?= $c->id ?>')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tampilan Mobile (Cards) -->
            <div class="block md:hidden overflow-y-auto max-h-[600px] custom-scrollbar bg-slate-50/30 p-4 space-y-3">
                <?php if (empty($cuti)): ?>
                    <div class="p-8 text-center text-slate-400 italic text-sm">
                        Belum ada data cuti yang diinput.
                    </div>
                <?php endif; ?>
                <?php foreach ($cuti as $c): ?>
                    <div class="p-4 bg-white rounded-xl border border-slate-200/50 shadow-sm flex flex-col space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-50 pb-2">
                            <span class="font-bold text-slate-800 text-sm"><?= esc($c->nama_terapis) ?></span>
                            <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-600 text-[10px] font-black uppercase">
                                <?= $c->jumlah_hari ?> Hari
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                            <i class="far fa-calendar-alt text-teal-600"></i>
                            <span><?= date('d M Y', strtotime($c->tanggal_mulai)) ?> s/d <?= date('d M Y', strtotime($c->tanggal_selesai)) ?></span>
                        </div>
                        <?php if (!empty($c->keterangan)): ?>
                            <div class="text-xs text-slate-600 italic bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                "<?= esc($c->keterangan) ?>"
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-end pt-1">
                            <button onclick="hapusCuti('<?= $c->id ?>')" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all">
                                <i class="fas fa-trash-alt"></i>
                                Hapus
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Cuti -->
<div id="modalCuti" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 bg-white">
            <h5 class="text-lg font-semibold text-slate-800">Formulir Pengajuan Cuti</h5>
            <button type="button" onclick="closeModalCuti()" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formCuti" action="<?= base_url('kehadiran/cuti/simpan') ?>" method="POST" class="p-5 space-y-4">
            <?= csrf_field() ?>
            
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Karyawan <span class="text-red-500">*</span></label>
                <select name="terapis_id" id="terapis_id_select" onchange="checkSisaCuti(this.value)" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                    <option value="">Pilih Karyawan</option>
                    <?php foreach ($terapis as $t): ?>
                    <option value="<?= $t->id ?>"><?= esc($t->nama) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Info Sisa Cuti -->
                <div id="infoSisaCuti" class="hidden mt-3 p-4 rounded-lg bg-teal-50/50 border border-teal-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-600">
                            <i class="fas fa-info-circle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Sisa Kuota Cuti</p>
                            <p class="text-sm font-bold text-teal-900"><span id="sisa_hari_label">0</span> Hari <span class="text-slate-400 text-xs font-normal">/ 1 Tahun</span></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Terpakai</p>
                        <p class="text-sm font-bold text-slate-800"><span id="terpakai_hari_label">0</span> Hari</p>
                    </div>
                </div>
            </div>

            <!-- Start Date Hidden (Defaults to Today) -->
            <input type="hidden" name="tanggal_mulai" value="<?= date('Y-m-d') ?>">
            
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Durasi Cuti <span class="text-red-500">*</span></label>
                <select name="jumlah_hari" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
                    <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> Hari</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Keterangan / Alasan</label>
                <textarea name="keterangan" rows="3" placeholder="Contoh: Acara Keluarga, Mudik, dll..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" onclick="closeModalCuti()"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 flex items-center gap-2">
                    <i class="fas fa-paper-plane text-xs"></i>
                    <span>Simpan Data Cuti</span>
                </button>
            </div>
            <p class="text-center text-[10px] text-slate-400 font-semibold tracking-wide mt-2">
                <i class="fas fa-lock mr-1"></i> Data akan otomatis terhubung ke presensi
            </p>
        </form>
    </div>
</div>

<!-- Modal Edit Kuota -->
<div id="modalKuota" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 bg-white">
            <h5 class="text-lg font-semibold text-slate-800">Edit Kuota Cuti</h5>
            <button type="button" onclick="closeModalKuota()" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formKuota" action="<?= base_url('kehadiran/cuti/update_kuota') ?>" method="POST" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="terapis_id" id="kuota_terapis_id">
            
            <div class="p-3 bg-teal-50/50 border border-teal-100 rounded-lg">
                <span class="text-xs text-slate-500 block">Karyawan:</span>
                <span id="kuota_nama_terapis" class="text-sm font-semibold text-slate-800">Nama Terapis</span>
            </div>
            
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Kuota Cuti (Hari/Tahun) <span class="text-red-500">*</span></label>
                <input type="number" name="jatah_cuti" id="kuota_input" required min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 transition-colors">
            </div>
            
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" onclick="closeModalKuota()"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 flex items-center gap-2">
                    <i class="fas fa-check-circle text-xs"></i>
                    <span>Update Kuota</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalTambahCuti() {
        document.getElementById('modalCuti').classList.remove('hidden');
    }

    function checkSisaCuti(id) {
        const infoBox = document.getElementById('infoSisaCuti');
        if (!id) {
            infoBox.classList.add('hidden');
            return;
        }

        fetch(`<?= base_url('kehadiran/cuti/cek_sisa_cuti') ?>/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('sisa_hari_label').textContent = data.sisa;
                document.getElementById('terpakai_hari_label').textContent = data.terpakai;
                
                // Ubah warna jika sisa 0
                const labelSisa = document.getElementById('sisa_hari_label').parentElement;
                if (data.sisa <= 0) {
                    labelSisa.classList.remove('text-teal-900');
                    labelSisa.classList.add('text-rose-600');
                } else {
                    labelSisa.classList.remove('text-rose-600');
                    labelSisa.classList.add('text-teal-900');
                }
                
                infoBox.classList.remove('hidden');
            }
        });
    }

    function closeModalCuti() {
        document.getElementById('modalCuti').classList.add('hidden');
        document.getElementById('infoSisaCuti').classList.add('hidden');
        document.getElementById('formCuti').reset();
    }

    function editKuota(id, nama, current) {
        document.getElementById('kuota_terapis_id').value = id;
        document.getElementById('kuota_nama_terapis').textContent = nama;
        document.getElementById('kuota_input').value = current;
        document.getElementById('modalKuota').classList.remove('hidden');
    }

    function closeModalKuota() {
        document.getElementById('modalKuota').classList.add('hidden');
    }

    // AJAX Form Submission
    document.getElementById('formCuti').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        // Validasi Sisa Cuti di Client Side
        const sisa = parseInt(document.getElementById('sisa_hari_label').textContent);
        const durasi = parseInt(form.querySelector('[name="jumlah_hari"]').value);

        if (sisa < durasi) {
            Swal.fire({
                icon: 'error',
                title: 'Kuota Tidak Mencukupi',
                text: `Sisa kuota (${sisa} hari) tidak cukup untuk durasi cuti (${durasi} hari).`,
                confirmButtonColor: '#0d9488'
            });
            return;
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => window.location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        });
    });

    document.getElementById('formKuota').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => window.location.reload());
            }
        });
    });

    function hapusCuti(id) {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Hapus data cuti ini? Absensi terkait akan dikembalikan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?= base_url('kehadiran/cuti/hapus') ?>/${id}`, { 
                    method: 'DELETE', 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= config('Security')->headerName ?>': '<?= csrf_hash() ?>'
                    } 
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Berhasil', data.message, 'success').then(() => window.location.reload());
                    }
                });
            }
        });
    }
</script>

<style>
    /* Memastikan modal input cuti & kuota selalu berada di tengah layar secara vertikal & horizontal pada semua ukuran layar */
    #modalCuti, #modalKuota {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 1040 !important;
        background-color: rgba(15, 23, 42, 0.6) !important; /* Overlay gelap premium */
    }

    #modalCuti.hidden, #modalKuota.hidden {
        display: none !important; /* Tetap sembunyikan jika kelas hidden aktif */
    }

    /* Memastikan card modal di dalamnya terpusat dengan indah */
    #modalCuti > div, #modalKuota > div {
        margin: auto !important;
    }
</style>

<?= $this->endSection() ?>
