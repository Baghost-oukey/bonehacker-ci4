<div class="rounded-xl border border-slate-200 bg-white shadow-sm font-sans mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 px-6 py-5 gap-4">
        <div>
            <h4 class="text-lg font-semibold text-slate-900 tracking-tight">Riwayat Kunjungan Pasien</h4>
            <p class="text-sm text-slate-500">Daftar rekam medis dan histori terapi yang pernah dilakukan.</p>
        </div>
        <button type="button" class="inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-slate-900/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" onclick="add()">
            <i class="fas fa-plus mr-2"></i> Tambah Riwayat
        </button>
    </div>
    
    <!-- FORM DATA RIWAYAT PASIEN -->
    <div class="p-0">
        <div class="overflow-x-auto w-full">
            <table id="table-2" class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Keluhan</th>
                        <th class="px-6 py-4">Rekam Medis</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>



<!-- FORM MODAL TAMBAH REKAM MEDIS -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm p-4 font-sans transition-all duration-300">
    <div class="w-full max-w-6xl overflow-hidden rounded-xl bg-white shadow-2xl border border-slate-200 flex flex-col" style="max-height: 95vh;">
        
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-white shrink-0">
            <div>
                <h3 class="text-lg font-semibold leading-none tracking-tight text-slate-900 modal-title">Tambah Riwayat Pasien</h3>
            </div>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors" data-modal-close>
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="save_data" action="<?= site_url('history/store') ?>" method="post" class="flex flex-col overflow-hidden h-full">
            <input type="hidden" name="id" id="history_id">
            <input type="hidden" name="patient_id" id="patient_id" value="<?= $patient_id ?>">
            <input type="hidden" name="queue_id" id="queue_id" value="<?= $queue_id ?>">

            <div class="overflow-y-auto p-6 space-y-8 flex-1 custom-scrollbar">
                
                <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col justify-center">
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">BONE HACKER</h4>
                            <p class="text-sm text-slate-500 mt-1">Sistem Rekam Medis Terpadu</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <label class="w-32 shrink-0 text-sm font-medium text-slate-500">Tanggal Dibuat</label>
                                <input type="date" required class="flex h-9 w-full sm:w-2/3 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm text-slate-900 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="date" id="date" value="<?= esc($current_date) ?>">
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="w-32 shrink-0 text-sm font-medium text-slate-500">Nama</label>
                                <span class="text-sm font-semibold text-slate-900"><?= esc($patient->name ?? '-') ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="w-32 shrink-0 text-sm font-medium text-slate-500">Usia</label>
                                <span class="text-sm font-semibold text-slate-900"><?= esc($patient->age ?? '-') ?> Tahun</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <label class="w-32 shrink-0 text-sm font-medium text-slate-500 mt-0.5">Alamat</label>
                                <span class="text-sm font-semibold text-slate-900 leading-snug">
                                    <?php
                                    $parts = [];
                                    if (!empty($patient->address)) $parts[] = $patient->address;
                                    if (!empty($address->desa_nama)) $parts[] = $address->desa_nama;
                                    if (!empty($address->kecamatan_nama)) $parts[] = $address->kecamatan_nama;
                                    if (!empty($address->kabupaten_nama)) $parts[] = $address->kabupaten_nama;
                                    echo esc(implode(', ', $parts));
                                    ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="w-32 shrink-0 text-sm font-medium text-slate-500">No. WA</label>
                                <span class="text-sm font-semibold text-slate-900"><?= esc($patient->phone ?? '-') ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="w-32 shrink-0 text-sm font-medium text-slate-500">Wilayah Periksa</label>
                                <div class="w-full sm:w-2/3">
                                    <select id="region_history" name="history_region" class="w-full">
                                        <option value="">Semua Wilayah</option>
                                        <?php foreach ($wilayah as $value): ?>
                                            <?php
                                            $selected = '';
                                            if (isset($regions_patient[0]) && $value->id == $regions_patient[0]) $selected = 'selected';
                                            elseif (isset($patient->region_id) && $value->id == $patient->region_id) $selected = 'selected';
                                            ?>
                                            <option value="<?= $value->id ?>" <?= $selected ?>><?= esc($value->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Waktu mulai terapi :</label>
                        <input type="text" class="flex h-10 w-full rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed" name="processAt" id="processAt" disabled>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Waktu selesai terapi :</label>
                        <input type="text" class="flex h-10 w-full rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed" name="finishAt" id="finishAt" disabled>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Total Waktu :</label>
                        <input type="text" class="flex h-10 w-full rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-900 font-semibold cursor-not-allowed" name="timeConsume" id="timeConsume" disabled>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Keluhan :</label>
                        <textarea class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="complaint" id="complaintTags"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Riwayat Medis :</label>
                        <textarea class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="medhis" id="medhisTags"></textarea>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <tbody class="divide-y divide-slate-200">
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 w-40 border-r border-slate-200">Tensi</td>
                                <td class="px-4 py-3" colspan="5">
                                    <div class="flex items-center gap-3 max-w-50">
                                        <input type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="tensi">
                                        <span class="text-slate-500 font-medium">mmHg</span>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200">Type Vertebra</td>
                                <td class="px-4 py-3" colspan="3">
                                    <div class="flex flex-wrap gap-4 border-r border-slate-200 pr-4">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="vertebra[]" value="C" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> C</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="vertebra[]" value="S" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> S</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="vertebra[]" value="FLAT" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> FLAT</label>
                                    </div>
                                </td>
                                <td class="px-4 py-3" colspan="2">
                                    <textarea class="flex min-h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="ket_vertebrata" placeholder="Keterangan..." rows="1"></textarea>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200">Type Thorax</td>
                                <td class="px-4 py-3" colspan="3">
                                    <div class="flex flex-wrap gap-4 border-r border-slate-200 pr-4">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="thorax[]" value="CD" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> CD</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="thorax[]" value="CB" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> CB</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="thorax[]" value="BOTLE" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> BOTLE</label>
                                    </div>
                                </td>
                                <td class="px-4 py-3" colspan="2">
                                    <textarea class="flex min-h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="ket_thorax" placeholder="Keterangan..." rows="1"></textarea>
                                </td>
                            </tr>

                            <?php 
                            $simple_inputs = [
                                'Cervical' => 'cervical', 'Thoraxal' => 'thoraxal', 
                                'Lumbal' => 'lumbar', 'Sacrum' => 'sacrum', 'Pelvis' => 'pelvis'
                            ];
                            foreach($simple_inputs as $label => $name): 
                            ?>
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200"><?= $label ?></td>
                                <td class="px-4 py-3" colspan="5">
                                    <input type="text" class="flex h-9 w-full max-w-md rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="<?= $name ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200">Kompresi</td>
                                <td class="px-4 py-3" colspan="3">
                                    <div class="flex flex-wrap gap-4 border-r border-slate-200 pr-4">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="kompresi[]" value="cervical" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Cervical</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="kompresi[]" value="vertebra" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Vertebra</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="kompresi[]" value="HS" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> HS</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="kompresi[]" value="kanan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Kanan</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="kompresi[]" value="kiri" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Kiri</label>
                                    </div>
                                </td>
                                <td class="px-4 py-3" colspan="2">
                                    <textarea class="flex min-h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="ket_kompresi" placeholder="Keterangan..." rows="1"></textarea>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200">Plintiran</td>
                                <td class="px-4 py-3" colspan="3">
                                    <div class="flex flex-wrap gap-4 border-r border-slate-200 pr-4">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="plintiran[]" value="kanan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Kanan</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="plintiran[]" value="kiri" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Kiri</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="plintiran[]" value="silang" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Silang</label>
                                    </div>
                                </td>
                                <td class="px-4 py-3" colspan="2">
                                    <textarea class="flex min-h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="ket_plintiran" placeholder="Keterangan..." rows="1"></textarea>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200">Visual Kaki</td>
                                <td class="px-4 py-3" colspan="3">
                                    <div class="flex flex-wrap gap-4 border-r border-slate-200 pr-4">
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="visual_kaki[]" value="kanan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Kanan</label>
                                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="visual_kaki[]" value="kiri" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"> Kiri</label>
                                    </div>
                                </td>
                                <td class="px-4 py-3" colspan="2">
                                    <textarea class="flex min-h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="ket_viska" placeholder="Keterangan..." rows="1"></textarea>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-slate-700 border-r border-slate-200">Power</td>
                                <td class="px-4 py-3" colspan="5">
                                    <input type="text" class="flex h-9 w-full max-w-md rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="power">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-lg border border-slate-200 overflow-x-auto">
                    <table class="w-full text-sm text-center whitespace-nowrap">
                        <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left border-r border-slate-200">Pubis</th>
                                <th class="px-4 py-3">Atas</th>
                                <th class="px-4 py-3">Bawah</th>
                                <th class="px-4 py-3">Samping</th>
                                <th class="px-4 py-3">Depan</th>
                                <th class="px-4 py-3">Dominan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-left border-r border-slate-200">Kanan</td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kanan_atas" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kanan_bawah" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kanan_samping" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kanan_depan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kanan_dominan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                            </tr>
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 bg-slate-50 font-medium text-left border-r border-slate-200">Kiri</td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kiri_atas" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kiri_bawah" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kiri_samping" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kiri_depan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                                <td class="px-4 py-3"><input type="checkbox" name="pubis[]" value="kiri_dominan" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-900">Hasil Pemeriksaan :</label>
                    <textarea class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="results" id="resultTags"></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-900">Lain-Lain (Progres Terapi):</label>
                    <textarea class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="other"></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-900">Tindakan :</label>
                    <textarea class="flex min-h-20 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="measure"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Pekerjaan Rumah (PR) :</label>
                        <input type="text" class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" name="pr" id="pr">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">Terapis :</label>
                        <select class="terapis w-full" name="terapis[]" multiple="multiple">
                            <?php foreach ($terapis as $t): ?>
                                <option value="<?= esc($t->id) ?>"><?= esc($t->nama ?? $t->name ?? 'Tanpa Nama') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-200">

                <div class="flex flex-row items-center justify-between rounded-lg border border-slate-200 p-4 bg-slate-50/50" id="terapi-kejantanan" style="display: none;">
                    <div class="space-y-0.5">
                        <label class="text-sm font-medium text-slate-900">Aktifkan Terapi Kejantanan</label>
                        <p class="text-[13px] text-slate-500">Buka form pemeriksaan khusus pria.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" id="kejantanan" value="ya" name="kejantanan" class="peer sr-only" onchange="toggleTerapiForm()">
                        <div class="h-6 w-11 shrink-0 rounded-full border-2 border-transparent bg-slate-200 transition-colors peer-checked:bg-slate-900 peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900 after:absolute after:left-2px after:top-2px after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div id="terapi-form" style="display: none;">
                    </div>
                <div id="pemeriksaan" style="display: none;"></div>

                <hr class="border-slate-200">

                <div class="flex flex-col gap-4">
                    <div id="history-info" style="display: none;">
                        <p class="text-[13px] text-slate-500 bg-slate-50 p-3 rounded-md border border-slate-100">
                            Data ditambah oleh <strong class="text-slate-900" id="created_by"></strong>
                            <span id="updated_info">, diedit oleh <strong class="text-slate-900" id="updated_by"></strong></span>
                        </p>
                    </div>

                    <div class="flex flex-row items-center justify-between rounded-lg border border-slate-200 p-4" id="notif-wa">
                        <div class="space-y-0.5">
                            <label class="text-sm font-medium text-slate-900">Notifikasi WhatsApp</label>
                            <p class="text-[13px] text-slate-500">Kirim rekapan ke pasien.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="notifikasi" class="peer sr-only" id="notifikasi-checkbox" checked>
                            <div class="h-6 w-11 shrink-0 rounded-full border-2 border-transparent bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900 after:absolute after:left-2px after:top-2px after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 bg-slate-50 shrink-0">
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-900 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" data-modal-close>
                    Batal
                </button>
                <button type="submit" id="save-button" name="save-button" class="inline-flex h-10 items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-slate-900/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                    <i class="fas fa-save mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal-wrapper hidden fixed inset-0 z-60 items-center justify-center bg-black/60 backdrop-blur-sm p-4 font-sans transition-all duration-300">
    <div class="bg-white rounded-lg border border-slate-200 shadow-lg w-full max-w-md p-6 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-lg font-semibold tracking-tight text-slate-900">Konfirmasi Hapus Data</h2>
                <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus riwayat kunjungan ini? Data tidak dapat dikembalikan.</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-900 transition-colors hover:bg-slate-100" data-modal-close>Batal</button>
                <button id="confirmDeleteButton" class="inline-flex h-10 items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700">Ya, Hapus Data</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.patientId = '<?= $patient->id ?? "" ?>';
    window.queueId = '<?= $queue_id ?? "" ?>';
    window.historyFetchUrl = '<?= site_url("history/fetch/" . $patient->id) ?>';
    window.historyStoreUrl = '<?= site_url("history/store") ?>';
    window.historyDestroyUrl = '<?= site_url("history/destroy") ?>';
    window.complaintTagsUrl = '<?= site_url("complaint/get_tags") ?>';
    window.medisTagsUrl = '<?= site_url("medis/get_tags") ?>';
    window.resultTagsUrl = '<?= site_url("result/get_tags") ?>';
    window.csrfTokenName = '<?= csrf_token() ?>';
    window.csrfHash = '<?= csrf_hash() ?>';
    window.activeTerapis = <?= json_encode($terapis) ?>;
</script>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/pages/patient-history.js') ?>"></script>
<?= $this->endSection() ?>