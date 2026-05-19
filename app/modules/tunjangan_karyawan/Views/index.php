<?= $this->extend('layout/layout'); ?>
<?= $this->section('content'); ?>

<div class="p-4 sm:p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="w-full md:w-auto">
            <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Master Gaji</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola tunjangan & potongan rutin.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full lg:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('gaji') ?>">💵 Gaji Karyawan</option>
                <option value="<?= site_url('transaksi-tunjangan') ?>">💰 Tunjangan Terapis</option>
                <option value="<?= site_url('master-gaji') ?>" selected>⚙️ Master Gaji</option>
                <option value="<?= site_url('kasbon') ?>">💸 Kasbon Karyawan</option>
            </select>
        </div>

        <button id="btnTambahTunjangan"
            class="w-full md:w-auto px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-indigo-100 transition flex items-center justify-center gap-2 uppercase tracking-widest">
            <i class="fas fa-plus"></i> Tambah Item
        </button>
    </div>

    <!-- TAMPILAN DESKTOP (TABLE) -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden p-6">
        <table id="tableTunjangan" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="p-4 w-12 text-center">No</th>
                    <th class="p-4">Nama Item</th>
                    <th class="p-4 w-32">Kategori</th>
                    <th class="p-4 w-32">Tipe</th>
                    <th class="p-4 w-40 text-right">Nominal</th>
                    <th class="p-4 w-32 text-center">Terapis</th>
                    <th class="p-4 w-24 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-700 text-sm divide-y divide-slate-100"></tbody>
        </table>
    </div>

    <!-- TAMPILAN MOBILE (CARDS) -->
    <div id="mobile-card-container" class="md:hidden space-y-4 pb-20">
        <!-- Rendered via JS -->
        <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
            Memuat master data...
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="modalTunjangan" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h5 id="modalTitle" class="text-lg font-bold text-slate-800">Tambah Item Gaji</h5>
            <button type="button" class="btn-close-modal rounded-md p-2 text-slate-400 hover:bg-slate-100">&times;</button>
        </div>

        <form id="formTunjangan" class="space-y-4 p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="id_tunjangan" name="id">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Nama Item <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_tunjangan" name="nama_tunjangan" required
                        placeholder="Contoh: BPJS Kesehatan, Tunjangan Makan"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-slate-50/50">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Kategori <span class="text-red-500">*</span></label>
                    <select id="kategori" name="kategori" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="tunjangan">Tunjangan (Benefit)</option>
                        <option value="potongan">Potongan</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Tipe <span class="text-red-500">*</span></label>
                    <select id="tipe" name="tipe" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none bg-white">
                        <option value="bulanan">Bulanan (tetap/bulan)</option>
                        <option value="harian">Harian (× hari hadir)</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" id="nominal" name="nominal" required placeholder="Contoh: 100.000"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-indigo-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-slate-50/50">
                    <p id="keteranganNominal" class="text-xs text-slate-400 mt-1">Nominal tetap per bulan</p>
                </div>
            </div>

            <!-- Pilih Terapis -->
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-2 block">Terapis yang Mendapat Item Ini</label>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="selectAllTerapis" class="rounded border-slate-300 text-indigo-600">
                    <label for="selectAllTerapis" class="text-xs font-bold text-slate-600 cursor-pointer">Pilih Semua</label>
                </div>
                <div class="border border-slate-200 rounded-xl max-h-48 overflow-y-auto p-3 space-y-2 bg-slate-50">
                    <?php if (empty($terapis)): ?>
                        <p class="text-xs text-slate-400 italic">Tidak ada terapis aktif</p>
                    <?php else: ?>
                        <?php foreach ($terapis as $t): ?>
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white rounded-lg p-1.5 transition">
                                <input type="checkbox" name="terapis_ids[]" value="<?= $t['id'] ?>"
                                    class="terapis-checkbox rounded border-slate-300 text-indigo-600">
                                <span class="text-sm text-slate-700"><?= esc($t['nama']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" class="btn-close-modal rounded-xl border border-slate-300 px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" id="btnSimpan"
                    class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-bold text-white hover:bg-indigo-700 shadow-md shadow-indigo-100">
                    <i class="fas fa-save mr-1.5"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
window.masterTunjanganConfig = {
    csrfName:  '<?= csrf_token() ?>',
    csrfHash:  '<?= csrf_hash() ?>',
    urlFetch:  '<?= base_url('master-gaji/fetch') ?>',
    urlStore:  '<?= base_url('master-gaji/store') ?>',
    urlDelete: '<?= base_url('master-gaji/delete') ?>'
};

$(document).ready(function () {
    const cfg = window.masterTunjanganConfig;

    // Load table
    function loadTable() {
        $.ajax({
            url: cfg.urlFetch, type: 'POST',
            data: { [cfg.csrfName]: cfg.csrfHash },
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                const tbody = $('#tableTunjangan tbody');
                tbody.empty();
                if (!res.data || res.data.length === 0) {
                    tbody.append('<tr><td colspan="7" class="p-8 text-center text-slate-400 italic">Belum ada data</td></tr>');
                    return;
                }
                res.data.forEach(row => {
                    tbody.append(`
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 text-center text-slate-400">${row.no}</td>
                            <td class="p-4 font-semibold text-slate-800">${row.nama_tunjangan}</td>
                            <td class="p-4">${row.kategori}</td>
                            <td class="p-4">${row.tipe}</td>
                            <td class="p-4 text-right font-bold text-slate-700">${row.nominal}</td>
                            <td class="p-4 text-center text-xs text-slate-500">${row.terapis_count}</td>
                            <td class="p-4 text-center">
                                <button onclick="editItem(${row.id})" class="text-indigo-500 hover:text-indigo-700 p-1.5 rounded-lg hover:bg-indigo-50 transition mr-1">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="deleteItem(${row.id})" class="text-slate-300 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                // RENDER MOBILE CARDS
                const mobileContainer = document.getElementById('mobile-card-container');
                if (mobileContainer) {
                    mobileContainer.innerHTML = '';
                    if (!res.data || res.data.length === 0) {
                        mobileContainer.innerHTML = `
                            <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
                                Belum ada data master gaji.
                            </div>
                        `;
                    } else {
                        res.data.forEach(row => {
                            const card = `
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col gap-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex flex-col gap-1">
                                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">${row.nama_tunjangan}</h3>
                                            <div class="flex gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">${row.tipe}</span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                                    <i class="fas fa-users"></i> ${row.terapis_count} Terapis
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex gap-1">
                                            <button onclick="editItem(${row.id})" class="h-9 w-9 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl active:scale-95 transition-all">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button onclick="deleteItem(${row.id})" class="h-9 w-9 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl active:scale-95 transition-all">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-end pt-4 border-t border-slate-50">
                                        <div>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kategori</p>
                                            ${row.kategori}
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Nominal</p>
                                            <span class="text-sm font-black text-slate-700">${row.nominal}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            mobileContainer.insertAdjacentHTML('beforeend', card);
                        });
                    }
                }
            }
        });
    }
    loadTable();

    // Format rupiah
    $('#nominal').on('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? parseInt(v).toLocaleString('id-ID') : '';
    });

    // Update keterangan tipe
    $('#tipe').on('change', function () {
        $('#keteranganNominal').text(this.value === 'harian'
            ? 'Nominal per hari hadir (dikalikan jumlah hari hadir saat proses gaji)'
            : 'Nominal tetap per bulan');
    });

    // Select all terapis
    $('#selectAllTerapis').on('change', function () {
        $('.terapis-checkbox').prop('checked', this.checked);
    });
    $(document).on('change', '.terapis-checkbox', function () {
        const all = $('.terapis-checkbox').length;
        const checked = $('.terapis-checkbox:checked').length;
        $('#selectAllTerapis').prop('checked', all === checked).prop('indeterminate', checked > 0 && checked < all);
    });

    // Open modal tambah
    $('#btnTambahTunjangan').on('click', function () {
        $('#modalTitle').text('Tambah Item Gaji');
        $('#formTunjangan')[0].reset();
        $('#id_tunjangan').val('');
        $('.terapis-checkbox').prop('checked', false);
        $('#selectAllTerapis').prop('checked', false);
        $('#modalTunjangan').removeClass('hidden').addClass('flex');
    });

    // Close modal
    $(document).on('click', '.btn-close-modal', function () {
        $('#modalTunjangan').removeClass('flex').addClass('hidden');
    });
    $('#modalTunjangan').on('click', function (e) {
        if (e.target === this) $(this).removeClass('flex').addClass('hidden');
    });

    // Submit form
    $('#formTunjangan').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnSimpan');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i> Menyimpan...');

        $.ajax({
            url: cfg.urlStore, type: 'POST',
            data: $(this).serialize() + '&' + cfg.csrfName + '=' + cfg.csrfHash,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    $('#modalTunjangan').removeClass('flex').addClass('hidden');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    loadTable();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan' });
                }
            },
            complete: () => btn.prop('disabled', false).html('<i class="fas fa-save mr-1.5"></i> Simpan')
        });
    });
});

// Edit item
window.editItem = function (id) {
    const cfg = window.masterTunjanganConfig;
    $.ajax({
        url: cfg.urlFetch, type: 'POST',
        data: { [cfg.csrfName]: cfg.csrfHash },
        dataType: 'json',
        success: function (res) {
            // Cari data dari tabel yang sudah dirender
            // Ambil langsung dari server dengan endpoint detail
            $.ajax({
                url: cfg.urlStore.replace('store', 'detail') + '/' + id,
                type: 'GET',
                dataType: 'json',
                success: function (detail) {
                    if (!detail.data) return;
                    const d = detail.data;
                    $('#modalTitle').text('Edit Item Gaji');
                    $('#id_tunjangan').val(d.id);
                    $('#nama_tunjangan').val(d.nama_tunjangan);
                    $('#kategori').val(d.kategori);
                    $('#tipe').val(d.tipe).trigger('change');
                    $('#nominal').val(parseInt(d.nominal).toLocaleString('id-ID'));

                    const ids = JSON.parse(d.terapis_ids || '[]');
                    $('.terapis-checkbox').each(function () {
                        $(this).prop('checked', ids.includes(parseInt($(this).val())));
                    });
                    const all = $('.terapis-checkbox').length;
                    const checked = $('.terapis-checkbox:checked').length;
                    $('#selectAllTerapis').prop('checked', all === checked && all > 0);

                    $('#modalTunjangan').removeClass('hidden').addClass('flex');
                }
            });
        }
    });
};

// Delete item
window.deleteItem = function (id) {
    const cfg = window.masterTunjanganConfig;
    Swal.fire({
        title: 'Hapus item ini?', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: cfg.urlDelete + '/' + id, type: 'POST',
            data: { [cfg.csrfName]: cfg.csrfHash },
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) cfg.csrfHash = res.csrfHash;
                Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
                // Reload table
                setTimeout(() => location.reload(), 1200);
            }
        });
    });
};
</script>
<?= $this->endSection(); ?>
