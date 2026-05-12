<div id="patientHistoryContainer" class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
    <!-- HEADER TABLE -->
    <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-800">Riwayat Kunjungan Pasien</h3>
            <p class="text-sm text-slate-500">Daftar rekam medis dan histori terapi</p>
        </div>
        <button id="btn-add-history" type="button" data-modal-open="exampleModal"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition">
            <i class="fas fa-plus"></i> Tambah Riwayat
        </button>
    </div>

    <!-- TABLE RIWAYAT -->
    <div class="overflow-x-auto">
        <table id="table-2" class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-6 py-3.5 text-center font-semibold w-12">No</th>
                    <th class="px-6 py-3.5 text-left font-semibold">Keluhan</th>
                    <th class="px-6 py-3.5 text-left font-semibold">Rekam Medis</th>
                    <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                    <th class="px-6 py-3.5 text-center font-semibold w-20">Type</th>
                    <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
        <!-- Struktur Pagination (Sesuai dengan Skeleton Anda) -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-slate-600">Tampilkan</label>
                    <select id="paginationLength" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs font-medium text-slate-600">data per halaman</span>
                </div>
                <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                    <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-1.5 sm:justify-end">
                <button id="paginationPrev" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left text-xs mr-1"></i><span class="hidden sm:inline">Sebelumnya</span>
                </button>
                <div id="paginationNumbers" class="flex items-center gap-1"></div>
                <button id="paginationNext" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="hidden sm:inline">Berikutnya</span><i class="fas fa-chevron-right text-xs ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- MODAL TAMBAH/EDIT RIWAYAT -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-60 items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 transition-opacity">
    <!-- Diperlebar menjadi max-w-6xl karena isi form sangat banyak -->
    <div class="w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        
        <!-- MODAL HEADER -->
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-white z-10">
            <h5 class="text-xl font-bold text-slate-800 modal-title">Tambah Riwayat Pasien</h5>
            <button type="button" data-modal-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- MODAL BODY -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 bg-slate-50/30">
            <form id="save_data" action="<?= site_url('history/store') ?>" method="post" class="space-y-8" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="history_id">
                <input type="hidden" name="patient_id" id="patient_id" value="<?= $patient_id ?>">
                <input type="hidden" name="queue_id" id="queue_id" value="<?= $queue_id ?>">

                <!-- SECTION 1: HEADER INFO PASIEN (Mirip Screenshot) -->
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3 pt-2">
                        <h2 class="text-2xl font-bold text-slate-500 uppercase tracking-widest">BONE HACKER</h2>
                    </div>
                    <div class="w-full md:w-2/3">
                        <div class="grid grid-cols-1 gap-y-3 text-sm text-slate-700">
                            <div class="flex items-center">
                                <label class="w-1/3 font-medium text-slate-500">Tanggal Dibuat</label>
                                <span class="w-4 text-center">:</span>
                                <div class="flex-1">
                                    <input type="date" name="date" id="date" value="<?= esc($current_date) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                                </div>
                            </div>
                            <div class="flex items-center">
                                <label class="w-1/3 font-medium text-slate-500">Nama</label>
                                <span class="w-4 text-center">:</span>
                                <div class="flex-1 font-semibold text-slate-800"><?= esc($patient->name ?? '-') ?></div>
                            </div>
                            <div class="flex items-center">
                                <label class="w-1/3 font-medium text-slate-500">Usia</label>
                                <span class="w-4 text-center">:</span>
                                <div class="flex-1 font-semibold text-slate-800"><?= esc($patient->age ?? '-') ?> Tahun</div>
                            </div>
                            <div class="flex items-start">
                                <label class="w-1/3 font-medium text-slate-500 mt-1">Alamat</label>
                                <span class="w-4 text-center mt-1">:</span>
                                <div class="flex-1 text-slate-800 mt-1">
                                    <?php
                                    $parts = [];
                                    if (!empty($patient->address)) $parts[] = $patient->address;
                                    if (!empty($address->desa_nama)) $parts[] = $address->desa_nama;
                                    if (!empty($address->kecamatan_nama)) $parts[] = $address->kecamatan_nama;
                                    if (!empty($address->kabupaten_nama)) $parts[] = $address->kabupaten_nama;
                                    echo esc(implode(', ', $parts));
                                    ?>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <label class="w-1/3 font-medium text-slate-500">No. WA</label>
                                <span class="w-4 text-center">:</span>
                                <div class="flex-1 text-slate-800"><?= esc($patient->phone ?? '-') ?></div>
                            </div>
                            <div class="flex items-center">
                                <label class="w-1/3 font-medium text-slate-500">Wilayah Periksa</label>
                                <span class="w-4 text-center">:</span>
                                <div class="flex-1">
                                    <select id="region_history" name="history_region" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 bg-white">
                                        <option value="">Pilih Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php
                                            // Prioritas: region_id dari patient (biodata)
                                            $selected = '';
                                            if (isset($patient->region_id) && $value->id == $patient->region_id) {
                                                $selected = 'selected';
                                            }
                                            ?>
                                            <option value="<?= $value->id ?>" <?= $selected ?>><?= esc($value->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- WAKTU TERAPI -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-y border-slate-200 py-6">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-600">Waktu mulai terapi :</label>
                        <input type="text" name="processAt" id="processAt" disabled class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-600">Waktu selesai terapi :</label>
                        <input type="text" name="finishAt" id="finishAt" disabled class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-600">Total Waktu (Menit) :</label>
                        <input type="text" name="timeConsume" id="timeConsume" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-600 cursor-not-allowed" title="Otomatis dihitung dari waktu mulai dan selesai">
                    </div>
                </div>

                <!-- KELUHAN & RIWAYAT -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">Keluhan :</label>
                        <textarea name="complaint" id="complaintTags" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">Riwayat Medis :</label>
                        <textarea name="medhis" id="medhisTags" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                </div>

                <!-- TABLE PEMERIKSAAN UTAMA (Mirip Screenshot) -->
                <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                    <table class="w-full text-sm text-left">
                        <tbody class="divide-y divide-slate-200">
                            <!-- TENSI -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 w-48 border-r border-slate-200 align-top">Tensi</td>
                                <td class="px-4 py-4" colspan="2">
                                    <div class="flex items-center gap-3 max-w-xs">
                                        <input type="text" name="tensi" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                                        <span class="text-slate-500 font-medium">mmHg</span>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- VERTEBRA -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 border-r border-slate-200 align-top">Type Vertebra</td>
                                <td class="px-4 py-4 w-1/2 align-top">
                                    <div class="flex flex-wrap gap-4">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="vertebra[]" value="C" id="vertebra_c" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">C</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="vertebra[]" value="S" id="vertebra_s" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">S</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="vertebra[]" value="FLAT" id="vertebra_flat" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">FLAT</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-4 w-1/2 align-top">
                                    <textarea name="ket_vertebrata" rows="2" placeholder="Masukkan keterangan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition"></textarea>
                                </td>
                            </tr>

                            <!-- THORAX -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 border-r border-slate-200 align-top">Type Thorax</td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-4">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="thorax[]" value="CD" id="thorax_cd" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">CD</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="thorax[]" value="CB" id="thorax_cb" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">CB</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="thorax[]" value="BOTLE" id="thorax_botle" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">BOTLE</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <textarea name="ket_thorax" rows="2" placeholder="Masukkan keterangan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition"></textarea>
                                </td>
                            </tr>

                            <!-- STANDARD INPUTS (Cervical - Sacrum) -->
                            <?php 
                            $simpleFields = [
                                'Cervical' => 'cervical', 
                                'Thoraxal' => 'thoraxal', 
                                'Lumbal' => 'lumbar', 
                                'Sacrum' => 'sacrum'
                            ];
                            foreach($simpleFields as $label => $name): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-3 font-medium text-slate-700 border-r border-slate-200 align-middle"><?= $label ?></td>
                                <td class="px-4 py-3" colspan="2">
                                    <input type="text" name="<?= $name ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <!-- KOMPRESI -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 border-r border-slate-200 align-top">Kompresi</td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-4">
                                        <?php $komp = ['Cervical'=>'cervical', 'Vertebra'=>'vertebra', 'HS'=>'HS', 'Kanan'=>'kanan', 'Kiri'=>'kiri']; 
                                        foreach($komp as $lbl => $val): ?>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="kompresi[]" value="<?= $val ?>" id="kompresi_<?= strtolower($val) ?>" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700"><?= $lbl ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <textarea name="ket_kompresi" rows="2" placeholder="Masukkan keterangan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition"></textarea>
                                </td>
                            </tr>

                            <!-- PELVIS -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-3 font-medium text-slate-700 border-r border-slate-200 align-middle">Pelvis</td>
                                <td class="px-4 py-3" colspan="2">
                                    <input type="text" name="pelvis" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                                </td>
                            </tr>

                            <!-- PLINTIRAN -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 border-r border-slate-200 align-top">Plintiran</td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-4">
                                        <?php $plin = ['Kanan'=>'kanan', 'Kiri'=>'kiri', 'Silang'=>'silang']; 
                                        foreach($plin as $lbl => $val): ?>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="plintiran[]" value="<?= $val ?>" id="plintiran_<?= $val ?>" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700"><?= $lbl ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <textarea name="ket_plintiran" rows="2" placeholder="Masukkan keterangan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition"></textarea>
                                </td>
                            </tr>

                            <!-- VISUAL KAKI -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 border-r border-slate-200 align-top">Visual Kaki</td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-4">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="visual_kaki[]" value="kanan" id="visual_kaki_kanan" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">Kanan</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="visual_kaki[]" value="kiri" id="visual_kaki_kiri" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4">
                                            <span class="text-slate-700">Kiri</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <textarea name="ket_viska" rows="2" placeholder="Masukkan keterangan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition"></textarea>
                                </td>
                            </tr>

                            <!-- PUBIS MATRIX -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-4 font-medium text-slate-700 border-r border-slate-200 align-middle">Pubis</td>
                                <td class="p-0 align-top" colspan="2">
                                    <table class="w-full text-center border-collapse">
                                        <thead>
                                            <tr class="text-slate-500 border-b border-slate-200">
                                                <th class="py-3 font-medium border-r border-slate-200 w-1/6 bg-slate-50/30"></th>
                                                <th class="py-3 font-medium">Atas</th>
                                                <th class="py-3 font-medium">Bawah</th>
                                                <th class="py-3 font-medium">Samping</th>
                                                <th class="py-3 font-medium">Depan</th>
                                                <th class="py-3 font-medium">Dominan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php foreach(['Kanan' => 'kanan', 'Kiri' => 'kiri'] as $label => $val): ?>
                                            <tr>
                                                <td class="py-3 font-medium text-slate-700 border-r border-slate-200 bg-slate-50/30"><?= $label ?></td>
                                                <?php foreach(['atas', 'bawah', 'samping', 'depan', 'dominan'] as $pos): ?>
                                                <td class="py-3">
                                                    <input type="checkbox" name="pubis[]" value="<?= $val.'_'.$pos ?>" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 w-4 h-4 cursor-pointer">
                                                </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- POWER -->
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="bg-slate-50/80 px-4 py-3 font-medium text-slate-700 border-r border-slate-200 align-middle">Power</td>
                                <td class="px-4 py-3" colspan="2">
                                    <input type="text" name="power" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- HASIL, LAIN-LAIN, TINDAKAN, PR, TERAPIS -->
                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">Hasil Pemeriksaan :</label>
                        <textarea name="results" id="resultTags" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">Lain-Lain (Progres Terapi):</label>
                        <textarea name="other" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">Tindakan :</label>
                        <textarea name="measure" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">PR :</label>
                        <input type="text" name="pr" id="pr" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-800">Terapis :</label>
                        <!-- Biarkan select2 menangani style-nya, pastikan class .terapis tetap ada -->
                        <select class="terapis w-full" name="terapis[]" multiple="multiple">
                            <?php foreach ($terapis as $t): ?>
                                <option value="<?= esc($t->id) ?>"><?= esc($t->nama ?? $t->name ?? 'Tanpa Nama') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>


                <!-- Force kejantanan = ya untuk form Kejantanan -->
                <input type="hidden" name="kejantanan" value="ya">

                <hr class="border-slate-200">

                <!-- TERAPI KEJANTANAN (Selalu tampil) -->
                <div id="terapi-kejantanan" class="bg-slate-50/50 border border-slate-200 rounded-xl p-5">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">
                        <i class="fas fa-notes-medical text-teal-600 mr-2"></i>Form Terapi Kejantanan
                    </h3>
                        <div class="space-y-6 text-sm text-slate-700">
                            <!-- Pertanyaan 1 -->
                            <div class="flex flex-col md:flex-row md:items-start border-b border-slate-100 pb-4 gap-2">
                                <div class="w-full md:w-3/5 font-medium">Apakah jika bangun tidur pagi hari masih ereksi?</div>
                                <div class="w-full md:w-2/5 flex gap-4">
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="ereksi" value="ya" class="text-teal-600 focus:ring-teal-500"> Ya</label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="ereksi" value="tidak" class="text-teal-600 focus:ring-teal-500"> Tidak</label>
                                </div>
                            </div>
                            
                            <!-- Pertanyaan 2 -->
                            <div class="flex flex-col md:flex-row md:items-start border-b border-slate-100 pb-4 gap-2">
                                <div class="w-full md:w-3/5 font-medium">Apakah suka melihat film porno?</div>
                                <div class="w-full md:w-2/5">
                                    <div class="flex gap-4 mb-2">
                                        <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="nonton_porno" value="ya" onclick="showFrequency('nonton-porn-frequency', true)" class="text-teal-600 focus:ring-teal-500"> Ya</label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="nonton_porno" value="tidak" onclick="showFrequency('nonton-porn-frequency', false)" class="text-teal-600 focus:ring-teal-500"> Tidak</label>
                                    </div>
                                    <div id="nonton-porn-frequency" class="bg-white p-3 rounded-lg border border-slate-200 mt-2 space-y-2" style="display: none;">
                                        <p class="font-medium text-xs text-slate-500 mb-1">Jika Ya, seberapa sering?</p>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_nonton_porno" value="sehari_sekali" class="text-teal-600 focus:ring-teal-500"> Sehari sekali</label>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_nonton_porno" value="lebih_dari_1x_sehari" class="text-teal-600 focus:ring-teal-500"> Lebih dari 1x sehari</label>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_nonton_porno" value="seminggu_sekali" class="text-teal-600 focus:ring-teal-500"> Seminggu sekali</label>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_nonton_porno" value="lainnya" onclick="toggleLainnyaTextbox('nonton-lainnya-textbox', true)" class="text-teal-600 focus:ring-teal-500"> Lainnya</label>
                                        <input type="text" id="nonton-lainnya-textbox" name="frekuensi_nonton_lainnya" class="w-full rounded border border-slate-300 px-2 py-1 text-sm mt-1 focus:border-teal-500 outline-none" style="display: none;">
                                    </div>
                                </div>
                            </div>

                            <!-- Pertanyaan 3 -->
                            <div class="flex flex-col md:flex-row md:items-start border-b border-slate-100 pb-4 gap-2">
                                <div class="w-full md:w-3/5 font-medium">Apakah sering melakukan onani?</div>
                                <div class="w-full md:w-2/5">
                                    <div class="flex gap-4 mb-2">
                                        <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="sering_onani" value="ya" onclick="showFrequency('onani-frequency', true)" class="text-teal-600 focus:ring-teal-500"> Ya</label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="sering_onani" value="tidak" onclick="showFrequency('onani-frequency', false)" class="text-teal-600 focus:ring-teal-500"> Tidak</label>
                                    </div>
                                    <div id="onani-frequency" class="bg-white p-3 rounded-lg border border-slate-200 mt-2 space-y-2" style="display: none;">
                                        <p class="font-medium text-xs text-slate-500 mb-1">Jika Ya, seberapa sering?</p>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_onani" value="sehari_sekali" class="text-teal-600 focus:ring-teal-500"> Sehari sekali</label>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_onani" value="lebih_dari_1x_sehari" class="text-teal-600 focus:ring-teal-500"> Lebih dari 1x sehari</label>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_onani" value="seminggu_sekali" class="text-teal-600 focus:ring-teal-500"> Seminggu sekali</label>
                                        <label class="flex items-center gap-2"><input type="radio" name="frekuensi_onani" value="lainnya" onclick="toggleLainnyaTextbox('onani-lainnya-textbox', true)" class="text-teal-600 focus:ring-teal-500"> Lainnya</label>
                                        <input type="text" id="onani-lainnya-textbox" name="frekuensi_onani_lainnya" class="w-full rounded border border-slate-300 px-2 py-1 text-sm mt-1 focus:border-teal-500 outline-none" style="display: none;">
                                    </div>
                                </div>
                            </div>

                            <!-- Pertanyaan 4, 5, 6, 7 -->
                            <?php 
                            $q4 = ['menggairahkan'=>'Menggairahkan', 'kurang_menggairahkan'=>'Kurang menggairahkan', 'cuek'=>'Cuek', 'aktif_dominan'=>'Aktif/dominan', 'balance'=>'Balance'];
                            $q5 = ['sehari_sekali'=>'Sehari sekali', 'lebih_dari_1x_sehari'=>'Lebih dari 1x sehari', 'seminggu_sekali'=>'Seminggu sekali'];
                            ?>
                            <div class="flex flex-col md:flex-row md:items-start border-b border-slate-100 pb-4 gap-2">
                                <div class="w-full md:w-3/5 font-medium">Dalam urusan ranjang, istri Anda termasuk yang seperti apa?</div>
                                <div class="w-full md:w-2/5 flex flex-col gap-2">
                                    <?php foreach($q4 as $val => $lbl): ?>
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="ranjang" value="<?= $val ?>" class="text-teal-600 focus:ring-teal-500"> <?= $lbl ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start border-b border-slate-100 pb-4 gap-2">
                                <div class="w-full md:w-3/5 font-medium">Seberapa sering istri Anda berhubungan seksual?</div>
                                <div class="w-full md:w-2/5 flex flex-col gap-2">
                                    <?php foreach($q5 as $val => $lbl): ?>
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="frekuensi_ranjang" value="<?= $val ?>" class="text-teal-600 focus:ring-teal-500"> <?= $lbl ?></label>
                                    <?php endforeach; ?>
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="frekuensi_ranjang" value="lainnya" onclick="toggleLainnyaTextbox('hubungan-lainnya-textbox', true)" class="text-teal-600 focus:ring-teal-500"> Lainnya</label>
                                    <input type="text" id="hubungan-lainnya-textbox" name="frekuensi_ranjang_lainnya" class="w-full rounded border border-slate-300 px-2 py-1 text-sm mt-1 focus:border-teal-500 outline-none" style="display: none;">
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start border-b border-slate-100 pb-4 gap-2">
                                <div class="w-full md:w-3/5 font-medium">Pernah konsumsi obat kuat?</div>
                                <div class="w-full md:w-2/5 flex flex-col gap-2">
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="obat_kuat" value="ya" class="text-teal-600 focus:ring-teal-500"> Ya</label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="obat_kuat" value="tidak" class="text-teal-600 focus:ring-teal-500"> Tidak</label>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start gap-2">
                                <div class="w-full md:w-3/5 font-medium">Menurut Anda, apa faktor penyebab dari masalah disfungsi seksual Anda?</div>
                                <div class="w-full md:w-2/5">
                                    <textarea name="penyebab" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                                </div>
                            </div>
                        </div>
                </div>


                <div id="pemeriksaan" class="border border-slate-200 rounded-xl overflow-hidden mt-6" >
                    <div class="overflow-x-auto">
                        <table class="w-full text-center text-xs whitespace-nowrap">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th colspan="2" class="p-2 border-r border-slate-200"></th>
                                    <th colspan="4" class="p-2 border-r border-slate-200 text-slate-600 font-bold tracking-widest">KANAN</th>
                                    <th colspan="4" class="p-2 text-slate-600 font-bold tracking-widest">KIRI</th>
                                </tr>
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="p-2">NO</th>
                                    <th class="p-2 border-r border-slate-200">AREA MESSAGE</th>
                                    <th class="p-2">SAKIT/TIDAK</th>
                                    <th class="p-2 bg-yellow-50">GRADE 1</th>
                                    <th class="p-2 bg-orange-50">GRADE 2</th>
                                    <th class="p-2 bg-red-50 border-r border-slate-200">GRADE 3</th>
                                    <th class="p-2 border-r border-slate-200">AREA MESSAGE</th>
                                    <th class="p-2">SAKIT/TIDAK</th>
                                    <th class="p-2 bg-yellow-50">GRADE 1</th>
                                    <th class="p-2 bg-orange-50">GRADE 2</th>
                                    <th class="p-2 bg-red-50">GRADE 3</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                                <?php 
                                $matrixRows = [
                                    1 => ['OTOT DADA PERUT', 'odp'],
                                    2 => ['VITAL', 'vital'],
                                    3 => ['KELENJAR', 'kelenjar'],
                                    4 => ['HORMON', 'hormon'],
                                    5 => ['TULANG KERING', 'tk'],
                                    6 => ['FEMUR DALAM', 'fd'],
                                    8 => ['CV4', 'cv4'],
                                    9 => ['CV6', 'cv6'],
                                    10 => ['L1', 'l1'],
                                    11 => ['L3', 'l3'],
                                    12 => ['PIRIFORMIS', 'piriformis'],
                                    13 => ['SENDOK', 'sendok']
                                ];
                                foreach($matrixRows as $no => $r):
                                    $lbl = $r[0]; $nm = $r[1];
                                ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-2 font-medium"><?= $no ?></td>
                                    <td class="p-2 border-r border-slate-200 text-left font-medium"><?= $lbl ?></td>
                                    <td class="p-2"><input type="checkbox" name="<?= $nm ?>_kanan" value="sakit" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-yellow-50/30"><input type="radio" name="<?= $nm ?>_kanan_grade" value="grade1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-orange-50/30"><input type="radio" name="<?= $nm ?>_kanan_grade" value="grade2" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-red-50/30 border-r border-slate-200"><input type="radio" name="<?= $nm ?>_kanan_grade" value="grade3" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 border-r border-slate-200 text-left font-medium"><?= $lbl ?></td>
                                    <td class="p-2"><input type="checkbox" name="<?= $nm ?>_kiri" value="sakit" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-yellow-50/30"><input type="radio" name="<?= $nm ?>_kiri_grade" value="grade1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-orange-50/30"><input type="radio" name="<?= $nm ?>_kiri_grade" value="grade2" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-red-50/30"><input type="radio" name="<?= $nm ?>_kiri_grade" value="grade3" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- (Lingkar Perut) -->
                                <?php $lpRows = ['atas'=>'LINGKAR PERUT ATAS','bawah'=>'LINGKAR PERUT BAWAH','kanan'=>'LINGKAR PERUT KANAN','kiri'=>'LINGKAR PERUT KIRI']; 
                                $first = true; foreach($lpRows as $suf => $lbl): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <?php if($first): ?><td class="p-2 font-medium" rowspan="4">7</td><?php $first=false; endif; ?>
                                    <td class="p-2 border-r border-slate-200 text-left font-medium"><?= $lbl ?></td>
                                    <td class="p-2"><input type="checkbox" name="lp_<?= $suf ?>" value="sakit" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-yellow-50/30"><input type="radio" name="lp_<?= $suf ?>_grade" value="grade1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-orange-50/30"><input type="radio" name="lp_<?= $suf ?>_grade" value="grade2" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 bg-red-50/30 border-r border-slate-200"><input type="radio" name="lp_<?= $suf ?>_grade" value="grade3" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"></td>
                                    <td class="p-2 border-r border-slate-200 bg-slate-50/50"></td><td class="p-2 bg-slate-50/50"></td><td class="p-2 bg-slate-50/50"></td><td class="p-2 bg-slate-50/50"></td><td class="p-2 bg-slate-50/50"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FOOTER INFO & TOGGLE WA -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between border-t border-slate-200 pt-6 mt-6">
                    <div id="history-info" class="text-xs text-slate-500">
                        Data rekam medis ditambah oleh <strong id="created_by" class="text-slate-700"></strong><span id="updated_info" class="hidden">, dan diedit oleh <strong id="updated_by" class="text-slate-700"></strong></span>
                    </div>
                    <div id="notif-wa" class="mt-4 md:mt-0 flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-lg border border-slate-200">
                        <span class="text-sm font-medium text-slate-700">Kirim Notifikasi WhatsApp?</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notifikasi" id="notifikasi-checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-teal-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                        </label>
                    </div>
                </div>

            </form>
        </div>

        <!-- MODAL FOOTER BUTTONS -->
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 bg-slate-50">
            <button type="button" data-modal-close class="rounded-lg border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Batal
            </button>
            <button type="submit" form="save_data" id="save-button" class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-medium text-white hover:bg-teal-700 transition">
                Simpan Data
            </button>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal-wrapper hidden fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative transform transition-all border border-gray-100">
        <button type="button" data-modal-close class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="text-center mb-6 mt-2">
            <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4 text-3xl border border-red-100">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Riwayat?</h3>
            <p class="text-gray-500 text-sm px-2">Data riwayat ini akan dihapus permanen. Apakah Anda yakin melanjutkan?</p>
        </div>
        <div class="flex items-center justify-center gap-3 mt-6">
            <button type="button" data-modal-close class="w-1/2 px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl font-semibold transition-colors border border-gray-200 focus:outline-none">
                Batal
            </button>
            <button type="button" id="confirmDeleteButton" class="w-1/2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-colors shadow-lg shadow-red-600/30 focus:outline-none flex items-center justify-center">
                Ya, Hapus
            </button>
        </div>
        
    </div>
</div>
