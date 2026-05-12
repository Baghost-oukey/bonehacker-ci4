<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<div class="p-6 space-y-6 bg-slate-50/50 min-h-screen" id="cutiPage">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Cuti Karyawan</h1>
            <p class="text-slate-500 text-sm mt-1">Atur kuota dan input cuti karyawan Bone Hacker.</p>
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
            <div class="overflow-y-auto max-h-[600px] custom-scrollbar">
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
        </div>
    </div>
</div>

<!-- Modal Input Cuti -->
<div id="modalCuti" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-teal-600">
            <h3 class="text-xl font-black text-white uppercase tracking-tight">Formulir Pengajuan Cuti</h3>
            <button onclick="closeModalCuti()" class="text-white/70 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="formCuti" action="<?= base_url('kehadiran/cuti/simpan') ?>" method="POST" class="p-8 space-y-6">
            <?= csrf_field() ?>
            
            <div class="space-y-2">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Karyawan</label>
                <select name="terapis_id" id="terapis_id_select" onchange="checkSisaCuti(this.value)" required class="w-full h-14 rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-teal-500 focus:border-teal-500 transition-all">
                    <option value="">Pilih Karyawan</option>
                    <?php foreach ($terapis as $t): ?>
                    <option value="<?= $t->id ?>"><?= esc($t->nama) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Info Sisa Cuti -->
                <div id="infoSisaCuti" class="hidden mt-3 p-4 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-teal-600 uppercase tracking-widest">Sisa Kuota Cuti</p>
                            <p class="text-sm font-black text-teal-900"><span id="sisa_hari_label">0</span> Hari <span class="text-teal-600/50 font-medium">/ 1 Tahun</span></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-teal-600 uppercase tracking-widest">Terpakai</p>
                        <p class="text-sm font-black text-teal-900"><span id="terpakai_hari_label">0</span> Hari</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Start Date Hidden (Defaults to Today) -->
                <input type="hidden" name="tanggal_mulai" value="<?= date('Y-m-d') ?>">
                
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Durasi Cuti</label>
                    <select name="jumlah_hari" required class="w-full h-14 rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-teal-500 focus:border-teal-500 transition-all">
                        <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> Hari</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Keterangan / Alasan</label>
                <textarea name="keterangan" rows="3" placeholder="Contoh: Acara Keluarga, Mudik, dll..." class="w-full rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-teal-500 focus:border-teal-500 transition-all p-4"></textarea>
            </div>

            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full h-14 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl font-black uppercase tracking-widest transition-all shadow-xl shadow-teal-100 flex items-center justify-center gap-3">
                    <i class="fas fa-paper-plane"></i>
                    Simpan Data Cuti
                </button>
                <p class="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                    <i class="fas fa-lock mr-1"></i> Data akan otomatis terhubung ke presensi
                </p>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kuota -->
<div id="modalKuota" class="modal-wrapper hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Edit Kuota Cuti</h3>
            <button onclick="closeModalKuota()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formKuota" action="<?= base_url('kehadiran/cuti/update_kuota') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="terapis_id" id="kuota_terapis_id">
            <div class="p-4 bg-teal-50 rounded-xl mb-4">
                <span class="text-xs text-teal-600 font-bold block">Karyawan:</span>
                <span id="kuota_nama_terapis" class="text-sm font-black text-teal-800">Nama Terapis</span>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kuota Cuti (Hari/Tahun)</label>
                <input type="number" name="jatah_cuti" id="kuota_input" required min="0" class="w-full h-11 rounded-xl border-slate-200 bg-slate-50 text-sm font-black focus:ring-teal-500 focus:border-teal-500 px-4">
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full h-12 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-black uppercase tracking-widest transition-all shadow-lg shadow-slate-200 flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    Update Kuota
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
<?= $this->endSection() ?>
