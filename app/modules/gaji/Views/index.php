<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<style>
    /* Optimasi responsivitas tabel Gaji pada layar tablet (portrait & landscape) */
    @media (min-width: 768px) and (max-width: 1200px) {
        #tab-estimasi table, #tab-riwayat table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #tab-estimasi th, #tab-estimasi td,
        #tab-riwayat th, #tab-riwayat td {
            font-size: 11px !important;
            padding: 8px 6px !important;
        }

        /* Pembagian kolom tabel Periode Berjalan (6 kolom) */
        #tab-estimasi th:nth-child(1), #tab-estimasi td:nth-child(1) { width: 22% !important; } /* Nama Karyawan */
        #tab-estimasi th:nth-child(2), #tab-estimasi td:nth-child(2) { width: 18% !important; } /* Jabatan */
        #tab-estimasi th:nth-child(3), #tab-estimasi td:nth-child(3) { width: 12% !important; } /* Wilayah */
        #tab-estimasi th:nth-child(4), #tab-estimasi td:nth-child(4) { width: 26% !important; } /* Tipe & Gaji Dasar */
        #tab-estimasi th:nth-child(5), #tab-estimasi td:nth-child(5) { width: 8% !important; }  /* Tindakan */
        #tab-estimasi th:nth-child(6), #tab-estimasi td:nth-child(6) { width: 14% !important; } /* Aksi (Proses Gaji) */

        /* Pembagian kolom tabel Riwayat Transaksi (4 kolom) */
        #tab-riwayat th:nth-child(1), #tab-riwayat td:nth-child(1) { width: 25% !important; } /* Tanggal Bayar */
        #tab-riwayat th:nth-child(2), #tab-riwayat td:nth-child(2) { width: 35% !important; } /* Nama Karyawan */
        #tab-riwayat th:nth-child(3), #tab-riwayat td:nth-child(3) { width: 20% !important; } /* Periode */
        #tab-riwayat th:nth-child(4), #tab-riwayat td:nth-child(4) { width: 20% !important; } /* Total Dibayar */

        /* Potong teks yang terlalu panjang dan berikan elipsis agar tidak meluber */
        #tab-estimasi td, #tab-riwayat td {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Jangan potong tombol & elemen di dalam Tipe & Gaji Dasar atau Aksi */
        #tab-estimasi td:nth-child(1),
        #tab-estimasi td:nth-child(4),
        #tab-estimasi td:nth-child(6) {
            overflow: visible !important;
            white-space: normal !important;
        }

        #tab-estimasi td:nth-child(1) span {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            max-width: calc(100% - 36px);
            vertical-align: middle;
        }

        /* Ukuran font tombol Proses Gaji disesuaikan */
        .btn-proses-gaji {
            font-size: 10px !important;
            padding: 4px 8px !important;
            white-space: nowrap !important;
        }
    }
</style>

<div id="gajiPage" class="p-6 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="w-full md:w-auto">
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Analisis & Kelola Gaji</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">Sistem penggajian otomatis terintegrasi kehadiran.</p>
        </div>

        <!-- DROPDOWN NAVIGASI MOBILE -->
        <div class="w-full lg:hidden">
            <select onchange="window.location.href=this.value" class="w-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="<?= site_url('gaji') ?>" selected>💵 Gaji Karyawan</option>
                <option value="<?= site_url('transaksi-tunjangan') ?>">💰 Tunjangan Terapis</option>
                <option value="<?= site_url('master-gaji') ?>">⚙️ Master Gaji</option>
                <option value="<?= site_url('kasbon') ?>">💸 Kasbon Karyawan</option>
            </select>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Wrapper Utama -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">

        <!-- Tab Navigasi -->
        <div class="flex border-b border-slate-200 px-6 pt-2">
            <button class="tab-btn active px-4 py-3 font-semibold text-sm border-b-2 border-blue-600 text-blue-600" data-target="tab-estimasi">
                Periode Berjalan
            </button>
            <button class="tab-btn px-4 py-3 font-medium text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700" data-target="tab-riwayat">
                Riwayat Transaksi
            </button>
        </div>

        <!-- TAB 1: PERIODE BERJALAN (BELUM DIBAYAR) -->
        <div id="tab-estimasi" class="tab-content p-4 sm:p-6 block">
            <!-- DESKTOP TABLE -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                            <th class="p-4 rounded-tl-lg">Nama Karyawan</th>
                            <th class="p-4">Jabatan</th>
                            <th class="p-4">Wilayah</th>
                            <th class="p-4">Tipe & Gaji Dasar</th>
                            <th class="p-4 text-center">Tindakan</th>
                            <th class="p-4 text-right rounded-tr-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach ($estimasi_gaji as $row) : ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <span class="font-medium text-slate-800"><?= esc($row['nama']) ?></span>
                                </td>
                                <td class="p-4 text-slate-600 font-medium"><?= esc($row['nama_jabatan'] ?? '-') ?></td>
                                <td class="p-4 text-slate-600"><?= esc($row['wilayah']) ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <?php if ($row['tipe_gaji'] === 'Belum Diset') : ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-600 rounded text-xs font-semibold">Belum Diset</span>
                                        <?php else : ?>
                                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium uppercase"><?= esc($row['tipe_gaji']) ?></span>
                                            <span class="text-slate-600 font-medium">Rp <?= number_format($row['nominal_gaji'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                        <?= esc($row['jml_tindakan'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button type="button" class="btn-proses-gaji inline-flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white rounded-md hover:bg-slate-800 transition text-sm"
                                            data-terapis-id="<?= $row['terapis_id'] ?>">
                                        Proses Gaji
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS -->
            <div class="md:hidden space-y-4">
                <?php foreach ($estimasi_gaji as $row) : ?>
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-black text-sm">
                                    <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm"><?= esc($row['nama']) ?></h4>
                                    <p class="text-[10px] font-medium text-slate-500 uppercase tracking-wider"><?= esc($row['nama_jabatan'] ?? '-') ?> &bull; <?= esc($row['wilayah']) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 py-3 border-y border-slate-50">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tipe Gaji</p>
                                <?php if ($row['tipe_gaji'] === 'Belum Diset') : ?>
                                    <span class="text-[10px] font-bold text-red-500 italic">Belum Diset</span>
                                <?php else : ?>
                                    <span class="text-[10px] font-bold text-blue-600 uppercase"><?= esc($row['tipe_gaji']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gaji Dasar</p>
                                <span class="text-xs font-bold text-slate-700">Rp <?= number_format($row['nominal_gaji'], 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-stethoscope text-slate-400 text-xs"></i>
                                <span class="text-xs font-bold text-slate-600"><?= esc($row['jml_tindakan'] ?? 0) ?> Tindakan</span>
                            </div>
                            <button type="button" class="btn-proses-gaji px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold shadow-lg shadow-slate-900/10 active:scale-95 transition-all"
                                    data-terapis-id="<?= $row['terapis_id'] ?>">
                                Proses Gaji
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB 2: RIWAYAT TRANSAKSI -->
        <div id="tab-riwayat" class="tab-content p-4 sm:p-6 hidden">
            <!-- Filter Riwayat -->
            <form action="<?= base_url('gaji') ?>" method="GET" class="mb-6 flex flex-col gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100 sm:flex-row sm:items-center">
                <input type="hidden" name="tab" value="riwayat">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Bulan:</label>
                    <select name="bulan" onchange="this.form.submit()" class="text-sm font-bold border-slate-200 rounded-lg focus:ring-blue-500 bg-white">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $filter_bulan == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Tahun:</label>
                    <select name="tahun" onchange="this.form.submit()" class="text-sm font-bold border-slate-200 rounded-lg focus:ring-blue-500 bg-white">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="sm:ml-auto w-full sm:w-auto">
                    <a href="<?= base_url('gaji/export?bulan=' . $filter_bulan . '&tahun=' . $filter_tahun . '&region_id=' . $filter_region) ?>" 
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition text-xs font-bold shadow-md shadow-emerald-600/10 uppercase tracking-widest">
                        <i class="fas fa-file-excel text-sm"></i>
                        Export Excel
                    </a>
                </div>
            </form>

            <!-- DESKTOP TABLE -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                            <th class="p-4 rounded-tl-lg">Nama Karyawan</th>
                            <th class="p-4">Jabatan</th>
                            <th class="p-4">Wilayah</th>
                            <th class="p-4">Periode</th>
                            <th class="p-4 text-right">Total Dibayar</th>
                            <th class="p-4 text-center rounded-tr-lg" style="width: 80px;">Rincian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($riwayat_gaji)): ?>
                            <tr>
                                <td colspan="6" class="text-center p-4 text-slate-400">Belum ada riwayat penggajian</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat_gaji as $rw): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 flex items-center gap-3">
                                        <a href="<?= base_url('karyawan/show/' . $rw['terapis_id']) ?>" class="flex items-center gap-3 group">
                                            <?php if (!empty($rw['foto']) && file_exists(FCPATH . 'foto_karyawan/' . $rw['foto'])) : ?>
                                                <img src="<?= base_url('foto_karyawan/' . $rw['foto']) ?>" class="w-8 h-8 rounded-full object-cover border border-slate-200" alt="<?= esc($rw['nama']) ?>">
                                            <?php else : ?>
                                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold group-hover:bg-blue-100 group-hover:text-blue-600 transition">
                                                    <?= strtoupper(substr($rw['nama'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <span class="font-medium text-slate-800 group-hover:text-blue-600 transition"><?= esc($rw['nama']) ?></span>
                                        </a>
                                    </td>
                                    <td class="p-4 text-slate-600 font-medium"><?= esc($rw['nama_jabatan'] ?? '-') ?></td>
                                    <td class="p-4 text-slate-600"><?= esc($rw['wilayah'] ?? '-') ?></td>
                                    <td class="p-4 text-slate-600">
                                        <span class="font-medium"><?= date('F Y', mktime(0, 0, 0, $rw['periode_bulan'], 1, $rw['periode_tahun'])) ?></span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5">Bayar: <?= date('d/m/Y', strtotime($rw['tanggal_bayar'])) ?></span>
                                    </td>
                                    <td class="p-4 text-right font-black text-emerald-600">
                                        Rp <?= number_format($rw['gaji_bersih'], 0, ',', '.') ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button type="button" class="btn-show-detail-modal w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all shadow-sm" data-target="detail-data-<?= $rw['id'] ?>" title="Lihat Rincian Gaji">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS -->
            <div class="md:hidden space-y-4">
                <?php if (empty($riwayat_gaji)): ?>
                    <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
                        Belum ada riwayat penggajian.
                    </div>
                <?php else: ?>
                    <?php foreach ($riwayat_gaji as $rw) : ?>
                        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
                            <div class="flex justify-between items-start">
                                <a href="<?= base_url('karyawan/show/' . $rw['terapis_id']) ?>" class="flex items-center gap-3">
                                    <?php if (!empty($rw['foto']) && file_exists(FCPATH . 'foto_karyawan/' . $rw['foto'])) : ?>
                                        <img src="<?= base_url('foto_karyawan/' . $rw['foto']) ?>" class="w-10 h-10 rounded-xl object-cover border border-slate-200" alt="<?= esc($rw['nama']) ?>">
                                    <?php else : ?>
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm">
                                            <?= strtoupper(substr($rw['nama'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm hover:text-blue-600 transition-colors"><?= esc($rw['nama']) ?></h4>
                                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-wider"><?= esc($rw['nama_jabatan'] ?? '-') ?> &bull; <?= esc($rw['wilayah'] ?? '-') ?></p>
                                    </div>
                                </a>
                            </div>

                            <div class="grid grid-cols-2 gap-4 py-3 border-y border-slate-50">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Periode</p>
                                    <span class="text-xs font-bold text-slate-700"><?= date('F Y', mktime(0, 0, 0, $rw['periode_bulan'], 1, $rw['periode_tahun'])) ?></span>
                                    <span class="block text-[9px] text-slate-400 mt-0.5">Bayar: <?= date('d M Y', strtotime($rw['tanggal_bayar'])) ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Dibayar</p>
                                    <span class="text-sm font-black text-emerald-600">Rp <?= number_format($rw['gaji_bersih'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="button" class="btn-show-detail-modal w-full py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2" data-target="detail-data-<?= $rw['id'] ?>">
                                    <i class="fas fa-eye text-[10px]"></i>
                                    <span>Lihat Detail Rincian</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- HIDDEN DETAILS DATA CONTAINERS FOR MODAL POPUP -->
            <?php if (!empty($riwayat_gaji)): ?>
                <?php foreach ($riwayat_gaji as $rw): ?>
                    <?php 
                    $details = $riwayat_details[$rw['id']] ?? [
                        'take_home' => [], 'benefit' => [], 'benefit_non_cash' => [], 'potongan' => [],
                        'total_take_home' => 0, 'total_benefit' => 0, 'total_benefit_non_cash' => 0, 'total_potongan' => 0
                    ];
                    ?>
                    <div id="detail-data-<?= $rw['id'] ?>" class="hidden">
                        <!-- Header metadata -->
                        <div class="modal-header-data" 
                             data-nama="<?= esc($rw['nama']) ?>"
                             data-foto="<?= !empty($rw['foto']) && file_exists(FCPATH . 'foto_karyawan/' . $rw['foto']) ? base_url('foto_karyawan/' . $rw['foto']) : '' ?>"
                             data-initial="<?= strtoupper(substr($rw['nama'], 0, 1)) ?>"
                             data-jabatan="<?= esc($rw['nama_jabatan'] ?? '-') ?>"
                             data-wilayah="<?= esc($rw['wilayah'] ?? '-') ?>"
                             data-periode="<?= date('F Y', mktime(0, 0, 0, $rw['periode_bulan'], 1, $rw['periode_tahun'])) ?>"
                             data-tanggal-bayar="<?= date('d/m/Y', strtotime($rw['tanggal_bayar'])) ?>"
                             data-gaji-bersih="Rp <?= number_format($rw['gaji_bersih'], 0, ',', '.') ?>">
                        </div>
                        
                        <!-- Rincian Grid (Modern & Premium Visual) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Take Home Pay -->
                            <div class="bg-emerald-50/30 p-4 rounded-xl border border-emerald-100/50 flex flex-col justify-between">
                                <div>
                                    <h5 class="text-[10px] font-black text-emerald-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                        <i class="fas fa-wallet text-xs"></i> Take Home
                                    </h5>
                                    <div class="space-y-2">
                                        <?php if (empty($details['take_home'])): ?>
                                            <p class="text-xs text-slate-400 italic">Tidak ada data</p>
                                        <?php else: ?>
                                            <?php foreach ($details['take_home'] as $item): ?>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-slate-500"><?= esc($item['nama_komponen']) ?></span>
                                                    <span class="font-bold text-slate-700">Rp <?= number_format($item['nominal'], 0, ',', '.') ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-4 pt-2 border-t border-emerald-100 flex justify-between text-xs font-bold text-emerald-600">
                                    <span>Total Take Home</span>
                                    <span>Rp <?= number_format($details['total_take_home'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <!-- Tunjangan -->
                            <div class="bg-blue-50/30 p-4 rounded-xl border border-blue-100/50 flex flex-col justify-between">
                                <div>
                                    <h5 class="text-[10px] font-black text-blue-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                        <i class="fas fa-shield-alt text-xs"></i> Tunjangan (Cash)
                                    </h5>
                                    <div class="space-y-2">
                                        <?php if (empty($details['benefit'])): ?>
                                            <p class="text-xs text-slate-400 italic">Tidak ada tunjangan cash</p>
                                        <?php else: ?>
                                            <?php foreach ($details['benefit'] as $item): ?>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-slate-500"><?= esc($item['nama_komponen']) ?></span>
                                                    <span class="font-bold text-slate-700">Rp <?= number_format($item['nominal'], 0, ',', '.') ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-4 pt-2 border-t border-blue-100 flex justify-between text-xs font-bold text-blue-600">
                                    <span>Total Tunjangan</span>
                                    <span>Rp <?= number_format($details['total_benefit'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <!-- Benefit Non-Cash -->
                            <div class="bg-teal-50/30 p-4 rounded-xl border border-teal-100/50 flex flex-col justify-between">
                                <div>
                                    <h5 class="text-[10px] font-black text-teal-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                        <i class="fas fa-gift text-xs"></i> Benefit Non-Cash
                                    </h5>
                                    <div class="space-y-2">
                                        <?php if (empty($details['benefit_non_cash'])): ?>
                                            <p class="text-xs text-slate-400 italic">Tidak ada benefit non-cash</p>
                                        <?php else: ?>
                                            <?php foreach ($details['benefit_non_cash'] as $item): ?>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-slate-500"><?= esc($item['nama_komponen']) ?></span>
                                                    <span class="font-bold text-slate-700">Rp <?= number_format($item['nominal'], 0, ',', '.') ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-4 pt-2 border-t border-teal-100 flex justify-between text-xs font-bold text-teal-600">
                                    <span>Total Non-Cash</span>
                                    <span>Rp <?= number_format($details['total_benefit_non_cash'], 0, ',', '.') ?></span>
                                </div>
                            </div>

                            <!-- Potongan -->
                            <div class="bg-rose-50/30 p-4 rounded-xl border border-rose-100/50 flex flex-col justify-between">
                                <div>
                                    <h5 class="text-[10px] font-black text-rose-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                        <i class="fas fa-minus-circle text-xs"></i> Potongan
                                    </h5>
                                    <div class="space-y-2">
                                        <?php if (empty($details['potongan'])): ?>
                                            <p class="text-xs text-slate-400 italic">Tidak ada potongan</p>
                                        <?php else: ?>
                                            <?php foreach ($details['potongan'] as $item): ?>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-slate-500"><?= esc($item['nama_komponen']) ?></span>
                                                    <span class="font-bold text-rose-600">- Rp <?= number_format($item['nominal'], 0, ',', '.') ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-4 pt-2 border-t border-rose-100 flex justify-between text-xs font-bold text-rose-600">
                                    <span>Total Potongan</span>
                                    <span>- Rp <?= number_format($details['total_potongan'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- OFFCANVAS PROSES GAJI (Sliding dari Kanan) -->
<div id="offcanvasProses" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-lg font-bold text-slate-800">Rincian Penggajian</h3>
        <button class="btn-close-offcanvas text-slate-400 hover:text-slate-600 p-2">&times;</button>
    </div>

    <div class="flex-1 overflow-y-auto p-6" id="offcanvasContent">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-10 hidden">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900 mx-auto mb-4"></div>
            <p class="text-sm text-slate-500">Menghitung kalkulasi gaji...</p>
        </div>

        <!-- Form Konten -->
        <form id="formBayarGaji" action="<?= base_url('gaji/proses_bayar') ?>" method="POST" class="hidden">
            <?= csrf_field() ?>
            <input type="hidden" name="terapis_id" id="oc_terapis_id">
            <input type="hidden" name="tipe_gaji" id="oc_tipe_gaji" value="bulanan">

            <div class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-100">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Penerima</p>
                <p class="font-bold text-slate-800 text-lg" id="oc_nama_terapis">-</p>
                <p class="mt-2 text-xs text-slate-500" id="oc_tipe_info">-</p>
            </div>

            <!-- Input Dinamis (Sistem otomatis menghitung kehadiran terapis) -->
            <div class="mb-4" id="oc_kehadiran_group">
                <label class="block text-sm font-medium text-slate-700 mb-1" id="oc_kehadiran_label">Total Kehadiran (Hari)</label>
                <input type="number" name="total_kehadiran" id="oc_kehadiran" value="0" class="w-full border-slate-200 rounded-lg bg-slate-50 text-slate-500 font-semibold cursor-not-allowed mb-3" readonly required>
                
                <div id="oc_absen_display_group" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Total Absen (Hari)</label>
                    <input type="number" id="oc_absen" value="0" class="w-full border-slate-200 rounded-lg bg-slate-50 text-slate-500 font-semibold cursor-not-allowed" readonly>
                </div>
                <p class="mt-2 text-xs text-slate-500" id="oc_kehadiran_help">Kehadiran real terapis yang tercatat di sistem presensi bulan ini.</p>
            </div>

            <hr class="my-6 border-slate-100">

            <!-- Rincian Readonly -->
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Gaji Pokok / Dasar</span>
                    <input type="text" name="gaji_pokok_total" id="oc_gaji_pokok" class="text-right font-medium text-slate-800 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-indigo-700">Total Tunjangan</span>
                    <input type="text" name="total_tunjangan" id="oc_tunjangan" class="text-right font-medium text-indigo-700 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between items-center hidden" id="oc_benefit_non_cash_group">
                    <span class="text-slate-500 text-teal-600">Benefit (Non-Cash)</span>
                    <input type="text" id="oc_benefit_non_cash" class="text-right font-medium text-teal-600 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 text-red-500">Total Potongan (Rutin & Kasbon)</span>
                    <input type="text" name="total_potongan" id="oc_potongan" class="text-right font-medium text-red-600 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between items-center" id="oc_potongan_absen_group">
                    <span class="text-slate-500 text-amber-600">Potongan Hari (Absen)</span>
                    <input type="text" id="oc_potongan_absen" class="text-right font-medium text-amber-600 border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
                <div class="flex justify-between items-center pt-4 border-t border-slate-200 mt-4">
                    <span class="font-bold text-slate-800 text-base">Gaji Bersih (Take Home)</span>
                    <input type="text" name="gaji_bersih" id="oc_bersih" class="text-right font-bold text-green-600 text-lg border-none bg-transparent p-0 w-1/2 focus:ring-0" readonly>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full py-3 bg-slate-900 text-white rounded-xl font-medium hover:bg-slate-800 transition shadow-md">
                    Setujui & Bayarkan
                </button>
            </div>
        </form>
    </div>
</div>
<div id="offcanvasBackdrop" class="offcanvas-backdrop fixed inset-0 bg-slate-900/20 z-40 hidden transition-opacity"></div>

<!-- MODAL DETAIL RIWAYAT GAJI (Beautiful, Premium Tailwind Modal) -->
<div id="modalDetailRiwayat" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <!-- Backdrop Overlay -->
    <div class="absolute inset-0 bg-black/40" id="modalDetailRiwayatBackdrop"></div>
    
    <!-- Modal Card Content -->
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative z-10 border border-slate-100 mx-4" id="modalDetailRiwayatContent">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">Rincian Slip Gaji</h3>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5" id="modal-detail-periode-text">Periode: -</p>
            </div>
            <button type="button" class="btn-close-modal-detail p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Body / Content -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <!-- Employee Card & Summary -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100 mb-6">
                <div class="flex items-center gap-3">
                    <!-- Avatar Container -->
                    <div id="modal-detail-avatar-container" class="flex-shrink-0">
                        <!-- Will be populated dynamically by JS -->
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm" id="modal-detail-nama">-</h4>
                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider" id="modal-detail-meta">-</p>
                    </div>
                </div>
                <div class="text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-200">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Dibayar (Net)</p>
                    <span class="text-lg font-black text-emerald-600" id="modal-detail-gaji-bersih">Rp -</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5" id="modal-detail-tanggal-bayar">Tanggal Bayar: -</span>
                </div>
            </div>

            <!-- Breakdown Grid Container -->
            <div id="modal-detail-grid-container" class="space-y-4">
                <!-- Inside here will be cloned/injected from the hidden details data -->
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end bg-slate-50/50">
            <button type="button" class="btn-close-modal-detail px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Config Global untuk Gaji -->
<script>
    window.gajiConfig = {
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        fetchUrl: "<?= base_url('gaji/fetch_estimasi') ?>",
        detailUrl: "<?= base_url('gaji/detail') ?>", // Untuk Offcanvas
        saveSettingUrl: "<?= base_url('gaji/setting/save') ?>",
        prosesBayarUrl: "<?= base_url('gaji/proses_bayar') ?>"
    };</script>
<?= $this->endSection() ?>
