<div id="exampleModal" class="modal fade">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 80vw; width: 80vw; margin:auto;">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Tambah Riwayat Pasien</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="save_data" action="<?= site_url('history/store') ?>" method="post" class="needs-validation" novalidate="">
                <input type="hidden" name="id" id="history_id">
                <input type="hidden" name="patient_id" id="patient_id" value="<?= $patient_id ?>">
                <input type="hidden" name="queue_id" id="queue_id" value="<?= $queue_id ?>">
                <div class="modal-body">
                    <!-- Header -->
                    <div class="row mb-2">
                        <div class="col-6">
                            <h4>BONE HACKER</h4>
                        </div>
                        <div class="col-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-2">
                                    <label class="d-flex" style="width: 30%;">
                                        <span style="flex: 1;">Tanggal Dibuat</span>
                                        <span>:</span>
                                    </label>
                                    <div style="width: 70%; padding-left: 10px;">
                                        <input type="date" required class="form-control" name="date"
                                            value="<?= esc($current_date) ?>">
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-2">
                                    <label class="d-flex" style="width: 34.5%;">
                                        <span style="flex: 1;">Nama</span>
                                        <span>:</span>
                                    </label>
                                    <div style="width: 80%; padding-left: 10px;">
                                        <?= esc($patient->name ?? '') ?>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-2">
                                    <label class="d-flex" style="width: 34.5%;">
                                        <span style="flex: 1;">Usia</span>
                                        <span>:</span>
                                    </label>
                                    <div style="width: 80%; padding-left: 10px;">
                                        <?= esc($patient->age ?? '') ?> Tahun
                                    </div>
                                </div>

                                <div class="d-flex align-items-start mb-2">
                                    <label class="d-flex align-items-center" style="width: 30%;">
                                        <span style="flex: 1;">Alamat</span>
                                        <span>:</span>
                                    </label>
                                    <div style="width: 70%; padding-left: 10px;">
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

                                <div class="d-flex align-items-center mb-2">
                                    <label class="d-flex" style="width: 34.5%;">
                                        <span style="flex: 1;">No. WA</span>
                                        <span>:</span>
                                    </label>
                                    <div style="width: 80%; padding-left: 10px;">
                                        <?= esc($patient->phone ?? '-') ?>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-2">
                                    <label class="d-flex" style="width: 30%;">
                                        <span style="flex: 1;">Wilayah Periksa</span>
                                        <span>:</span>
                                    </label>
                                    <div style="width: 70%; padding-left: 10px;">
                                        <select id="region_history" name="history_region" class="form-control" style="width: 100%">
                                            <option value="">Semua Wilayah</option>

                                            <?php foreach ($wilayah as $value): ?>
                                                <?php
                                                // Logika Selected: Cek dari session regions_patient atau dari data pasien
                                                $selected = '';
                                                if (isset($regions_patient[0]) && $value->id == $regions_patient[0]) {
                                                    $selected = 'selected';
                                                } elseif (isset($patient->region_id) && $value->id == $patient->region_id) {
                                                    $selected = 'selected';
                                                }
                                                ?>
                                                <option value="<?= $value->id ?>" <?= $selected ?>>
                                                    <?= esc($value->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-4">
                            <label>Waktu mulai terapi :</label><br>
                            <input type="text" class="w-100" name="processAt" id="processAt" disabled></input>
                        </div>
                        <div class="col-4">
                            <label>Waktu selesai terapi :</label><br>
                            <input type="text" class="w-100" name="finishAt" id="finishAt" disabled></input>
                        </div>
                        <div class="col-4">
                            <label>Total Waktu :</label><br>
                            <input type="text" class="w-100" name="timeConsume" id="timeConsume" disabled></input>
                        </div>
                    </div>
                    <br>

                    <!-- Keluhan dan Riwayat Medis -->
                    <div class="row mb-2">
                        <div class="col-6">
                            <label>Keluhan :</label><br>
                            <textarea class="w-100 h-100" name="complaint" rows="3" id="complaintTags" autofocus></textarea>
                        </div>
                        <div class="col-6">
                            <label>Riwayat Medis :</label><br>
                            <textarea class="w-100 h-100" name="medhis" rows="3" id="medhisTags" autofocus></textarea>
                        </div>
                    </div>
                    <br>
                    <br>

                    <div class="table-responsive">
                        <!-- Pemeriksaan -->
                        <table class="table table-bordered">
                            <tr>
                                <td>Tensi</td>
                                <td colspan="5">
                                    <div class="d-flex align-items-center">
                                        <input type="text" class="form-control mr-3" name="tensi">
                                        <span>mmHg</span>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>Type Vertebra</td>
                                <td colspan="3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="vertebra[]"
                                                value="C" id="vertebra_c">
                                            <label class="form-check-label" for="vertebra_c">C</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="vertebra[]"
                                                value="S" id="vertebra_s">
                                            <label class="form-check-label" for="vertebra_s">S</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="vertebra[]"
                                                value="FLAT" id="vertebra_flat">
                                            <label class="form-check-label mr-3" for="vertebra_flat">FLAT</label>
                                        </div>
                                    </div>
                                </td>
                                <td colspan="2">
                                    <textarea class="form-control my-2" name="ket_vertebrata" placeholder="Masukkan keterangan"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td>Type Thorax</td>
                                <td colspan="3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="thorax[]"
                                                id="thorax_cd" value="CD">
                                            <label class="form-check-label" for="thorax_cd">CD</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="thorax[]"
                                                id="thorax_cb" value="CB">
                                            <label class="form-check-label" for="thorax_cb">CB</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="thorax[]"
                                                id="thorax_botle" value="BOTLE">
                                            <label class="form-check-label" for="thorax_botle">BOTLE</label>
                                        </div>
                                    </div>
                                </td>
                                <td colspan="2">
                                    <textarea class="form-control my-2" name="ket_thorax" placeholder="Masukkan keterangan"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td>Cervical</td>
                                <td colspan="5"><input type="text" class="form-control" name="cervical"></td>
                            </tr>

                            <tr>
                                <td>Thoraxal</td>
                                <td colspan="5"><input type="text" class="form-control" name="thoraxal"></td>
                            </tr>

                            <tr>
                                <td>Lumbal</td>
                                <td colspan="5"><input type="text" class="form-control" name="lumbar"></td>
                            </tr>
                            <tr>
                                <td>Sacrum</td>
                                <td colspan="5"><input type="text" class="form-control" name="sacrum"></td>
                            </tr>

                            <tr>
                                <td>Kompresi</td>
                                <td colspan="3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="kompresi[]"
                                                id="kompresi_cervical" value="cervical">
                                            <label class="form-check-label mr-2"
                                                for="kompresi_cervical">Cervical</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="kompresi[]"
                                                id="kompresi_vertebra" value="vertebra">
                                            <label class="form-check-label mr-2"
                                                for="kompresi_vertebra">Vertebra</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="kompresi[]"
                                                id="kompresi_hs" value="HS">
                                            <label class="form-check-label mr-2" for="kompresi_hs">HS</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="kompresi[]"
                                                id="kompresi_kanan" value="kanan">
                                            <label class="form-check-label mr-2" for="kompresi_kanan">Kanan</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="kompresi[]"
                                                id="kompresi_kiri" value="kiri">
                                            <label class="form-check-label mr-2" for="kompresi_kiri">Kiri</label>
                                        </div>
                                    </div>
                                </td>
                                <td colspan="2">
                                    <textarea class="form-control my-2" name="ket_kompresi" placeholder="Masukkan keterangan"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td>Pelvis</td>
                                <td colspan="5"><input type="text" class="form-control" name="pelvis"></td>
                            </tr>

                            <tr>
                                <td>Plintiran</td>
                                <td colspan="3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="plintiran[]"
                                                id="plintiran_kanan" value="kanan">
                                            <label class="form-check-label" for="plintiran_kanan">Kanan</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="plintiran[]"
                                                id="plintiran_kiri" value="kiri">
                                            <label class="form-check-label" for="plintiran_kiri">Kiri</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="plintiran[]"
                                                id="plintiran_silang" value="silang">
                                            <label class="form-check-label" for="plintiran_silang">Silang</label>
                                        </div>
                                    </div>
                                </td>
                                <td colspan="2">
                                    <textarea class="form-control my-2" name="ket_plintiran" placeholder="Masukkan keterangan"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td>Visual Kaki</td>
                                <td colspan="3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="visual_kaki[]"
                                                id="visual_kaki_kanan" value="kanan">
                                            <label class="form-check-label" for="visual_kaki_kanan">Kanan</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="visual_kaki[]"
                                                id="visual_kaki_kiri" value="kiri">
                                            <label class="form-check-label" for="visual_kaki_kiri">Kiri</label>
                                        </div>
                                    </div>
                                </td>
                                <td colspan="2">
                                    <textarea class="form-control my-2" name="ket_viska" placeholder="Masukkan keterangan"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td width="16.66%">
                                    <span>Pubis</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <span>Atas</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <span>Bawah</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <span>Samping</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <span>Depan</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <span>Dominan</span>
                                </td>
                            </tr>
                            <tr>
                                <td width="16.66%">
                                    <span>Kanan</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kanan_atas">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kanan_bawah">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kanan_samping">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kanan_depan">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kanan_dominan">
                                </td>
                            </tr>
                            <tr>
                                <td width="16.66%">
                                    <span>Kiri</span>
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kiri_atas">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kiri_bawah">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kiri_samping">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kiri_depan">
                                </td>
                                <td width="16.66%" style="text-align: center;">
                                    <input type="checkbox" name="pubis[]" value="kiri_dominan">
                                </td>
                            </tr>

                            <tr>
                                <td>Power</td>
                                <td colspan="5"><input type="text" class="form-control" name="power"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Hasil Pemeriksaan -->
                    <div class="row mb-2">
                        <div class="col-12 mb-3">
                            <label>Hasil Pemeriksaan :</label><br>
                            <textarea class="w-100 h-100" name="results" rows="3" id="resultTags" autofocus></textarea>
                        </div>
                    </div>
                    <br>

                    <div class="row mb-2">
                        <div class="col-12">
                            <label>Lain-Lain (Progres Terapi):</label>
                            <textarea class="form-control" name="other" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Tindakan -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <label>Tindakan :</label>
                            <textarea class="form-control" name="measure" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <label for="pr" class="col-form-label mr-3">PR :</label>
                            <input type="text" id="pr" class="form-control" name="pr">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <label for="pr" class="col-form-label" style="display: block; margin-bottom: 5px;">Terapis :</label>
                            <select class="terapis" name="terapis[]" id="pr" multiple="multiple" style="width: 100%;">
                                <?php foreach ($terapis as $t): ?>
                                    <option value="<?= esc($t->id) ?>">
                                        <?= esc($t->nama ?? $t->name ?? 'Tanpa Nama') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2" id="terapi-kejantanan" style="display: none;">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="kejantanan" value="ya"
                                    name="kejantanan" onchange="toggleTerapiForm()">
                                <label class="form-check-label" for="kejantanan">Aktifkan Terapi Kejantanan</label>
                            </div>
                        </div>
                    </div>

                    <div id="terapi-form" style="display: none;">
                        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
                            <tbody>
                                <!-- Pertanyaan 1: Apakah jika bangun tidur pagi hari masih ereksi? -->
                                <tr>
                                    <td
                                        style="padding: 10px; border-bottom: 1px solid #ddd;  border-top: 1px solid #ddd; width: 60%;">
                                        Apakah jika bangun tidur pagi hari masih ereksi?</td>
                                    <td
                                        style="padding: 10px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; width: 40%;">
                                        <label style="margin-right: 15px;"><input type="radio" name="ereksi"
                                                value="ya"> Ya</label>
                                        <label style="margin-right: 15px;"><input type="radio" name="ereksi"
                                                value="tidak"> Tidak</label>
                                    </td>
                                </tr>

                                <!-- Pertanyaan 2: Apakah suka melihat film porno? -->
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;">Apakah suka
                                        melihat film porno?</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 40%;">
                                        <label style="margin-right: 15px;"><input type="radio" name="nonton_porno"
                                                value="ya" onclick="showFrequency('nonton-porn-frequency', true)">
                                            Ya</label>
                                        <label style="margin-right: 15px;"><input type="radio" name="nonton_porno"
                                                value="tidak"
                                                onclick="showFrequency('nonton-porn-frequency', false)"> Tidak</label>

                                        <div id="nonton-porn-frequency" style="display: none; margin-top: 10px;">
                                            <label>Jika Ya, seberapa sering?</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_nonton_porno" value="sehari_sekali"> Sehari
                                                sekali</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_nonton_porno" value="lebih_dari_1x_sehari"> Lebih
                                                dari 1x sehari</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_nonton_porno" value="seminggu_sekali"> Seminggu
                                                sekali</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_nonton_porno" value="lainnya"
                                                    onclick="toggleLainnyaTextbox('nonton-lainnya-textbox', true)">
                                                Lainnya</label>
                                            <input type="text" id="nonton-lainnya-textbox"
                                                name="frekuensi_nonton_lainnya"
                                                style="display: none; width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;">
                                        </div>
                                    </td>
                                </tr>

                                <!-- Pertanyaan 3: Apakah sering melakukan onani? -->
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;">Apakah sering
                                        melakukan onani?</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 40%;">
                                        <label style="margin-right: 15px;"><input type="radio" name="sering_onani"
                                                value="ya" onclick="showFrequency('onani-frequency', true)">
                                            Ya</label>
                                        <label style="margin-right: 15px;"><input type="radio" name="sering_onani"
                                                value="tidak" onclick="showFrequency('onani-frequency', false)">
                                            Tidak</label>

                                        <div id="onani-frequency" style="display: none; margin-top: 10px;">
                                            <label>Jika Ya, seberapa sering?</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_onani" value="sehari_sekali"> Sehari
                                                sekali</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_onani" value="lebih_dari_1x_sehari"> Lebih dari 1x
                                                sehari</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_onani" value="seminggu_sekali"> Seminggu
                                                sekali</label><br>
                                            <label style="margin-right: 15px;"><input type="radio"
                                                    name="frekuensi_onani" value="lainnya"
                                                    onclick="toggleLainnyaTextbox('onani-lainnya-textbox', true)">
                                                Lainnya</label>
                                            <input type="text" id="onani-lainnya-textbox"
                                                name="frekuensi_onani_lainnya"
                                                style="display: none; width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;">
                                        </div>
                                    </td>
                                </tr>

                                <!-- Pertanyaan 4: Dalam urusan ranjang, istri Anda termasuk yang seperti apa? -->
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;">Dalam urusan
                                        ranjang, istri Anda termasuk yang seperti apa?</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 40%;">
                                        <label style="margin-right: 15px;"><input type="radio" name="ranjang"
                                                value="menggairahkan"> Menggairahkan</label><br>
                                        <label style="margin-right: 15px;"><input type="radio" name="ranjang"
                                                value="kurang_menggairahkan"> Kurang menggairahkan</label><br>
                                        <label style="margin-right: 15px;"><input type="radio" name="ranjang"
                                                value="cuek"> Cuek</label><br>
                                        <label style="margin-right: 15px;"><input type="radio" name="ranjang"
                                                value="aktif_dominan"> Aktif/dominan</label><br>
                                        <label style="margin-right: 15px;"><input type="radio" name="ranjang"
                                                value="balance"> Balance</label>
                                    </td>
                                </tr>

                                <!-- Pertanyaan 5: Seberapa sering istri Anda berhubungan seksual? -->
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;">Seberapa
                                        sering istri Anda berhubungan seksual?</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 40%;">
                                        <label style="margin-right: 15px;"><input type="radio"
                                                name="frekuensi_ranjang" value="sehari_sekali"> Sehari
                                            sekali</label><br>
                                        <label style="margin-right: 15px;"><input type="radio"
                                                name="frekuensi_ranjang" value="lebih_dari_1x_sehari"> Lebih dari 1x
                                            sehari</label><br>
                                        <label style="margin-right: 15px;"><input type="radio"
                                                name="frekuensi_ranjang" value="seminggu_sekali"> Seminggu
                                            sekali</label><br>
                                        <label style="margin-right: 15px;"><input type="radio"
                                                name="frekuensi_ranjang" value="lainnya"
                                                onclick="toggleLainnyaTextbox('hubungan-lainnya-textbox', true)">
                                            Lainnya</label>
                                        <input type="text" id="hubungan-lainnya-textbox"
                                            name="frekuensi_ranjang_lainnya"
                                            style="display: none; width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;">
                                    </td>
                                </tr>

                                <!-- Pertanyaan 6: Pernah konsumsi obat kuat? -->
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;">Pernah
                                        konsumsi obat kuat?</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 40%;">
                                        <label style="margin-right: 15px;"><input type="radio" name="obat_kuat"
                                                value="ya"> Ya</label><br>
                                        <label style="margin-right: 15px;"><input type="radio" name="obat_kuat"
                                                value="tidak"> Tidak</label>
                                    </td>
                                </tr>

                                <!-- Pertanyaan 7: Menurut Anda, apa faktor penyebab dari masalah disfungsi seksual Anda? -->
                                <tr>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 60%;">Menurut Anda,
                                        apa faktor penyebab dari masalah disfungsi seksual Anda?</td>
                                    <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 40%;">
                                        <textarea name="penyebab" style="width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;" rows="3"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pemeriksaan" style="display: none;">
                        <div class="table-responsive">
                            <table class="table" style="text-align: center; font-size: 12px;">
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td colspan="4">KANAN</td>
                                    <td></td>
                                    <td colspan="4">KIRI</td>
                                </tr>
                                <tr>
                                    <th>NO</th>
                                    <th>AREA MESSAGE</th>
                                    <th>SAKIT/TIDAK</th>
                                    <th>GRADE 1</th>
                                    <th>GRADE 2</th>
                                    <th>GRADE 3</th>
                                    <th>AREA MESSAGE</th>
                                    <th>SAKIT/TIDAK</th>
                                    <th>GRADE 1</th>
                                    <th>GRADE 2</th>
                                    <th>GRADE 3</th>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>OTOT DADA PERUT</td>
                                    <td><input type="checkbox" name="odp_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="odp_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="odp_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="odp_kanan_grade" value="grade3"></td>
                                    <td>OTOT DADA PERUT</td>
                                    <td><input type="checkbox" name="odp_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="odp_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="odp_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="odp_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>VITAL</td>
                                    <td><input type="checkbox" name="vital_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="vital_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="vital_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="vital_kanan_grade" value="grade3"></td>
                                    <td>VITAL</td>
                                    <td><input type="checkbox" name="vital_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="vital_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="vital_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="vital_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>KELENJAR</td>
                                    <td><input type="checkbox" name="kelenjar_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="kelenjar_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="kelenjar_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="kelenjar_kanan_grade" value="grade3"></td>
                                    <td>KELENJAR</td>
                                    <td><input type="checkbox" name="kelenjar_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="kelenjar_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="kelenjar_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="kelenjar_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>HORMON</td>
                                    <td><input type="checkbox" name="hormon_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="hormon_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="hormon_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="hormon_kanan_grade" value="grade3"></td>
                                    <td>HORMON</td>
                                    <td><input type="checkbox" name="hormon_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="hormon_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="hormon_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="hormon_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>TULANG KERING</td>
                                    <td><input type="checkbox" name="tk_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="tk_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="tk_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="tk_kanan_grade" value="grade3"></td>
                                    <td>TULANG KERING</td>
                                    <td><input type="checkbox" name="tk_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="tk_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="tk_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="tk_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>FEMUR DALAM</td>
                                    <td><input type="checkbox" name="fd_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="fd_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="fd_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="fd_kanan_grade" value="grade3"></td>
                                    <td>FEMUR DALAM</td>
                                    <td><input type="checkbox" name="fd_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="fd_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="fd_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="fd_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td rowspan="4">7</td>
                                    <td>LINGKAR PERUT ATAS</td>
                                    <td><input type="checkbox" name="lp_atas" value="sakit"></td>
                                    <td><input type="checkbox" name="lp_atas_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="lp_atas_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="lp_atas_grade" value="grade3"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>LINGKAR PERUT BAWAH</td>
                                    <td><input type="checkbox" name="lp_bawah" value="sakit"></td>
                                    <td><input type="checkbox" name="lp_bawah_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="lp_bawah_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="lp_bawah_grade" value="grade3"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>LINGKAR PERUT KANAN</td>
                                    <td><input type="checkbox" name="lp_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="lp_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="lp_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="lp_kanan_grade" value="grade3"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>LINGKAR PERUT KIRI</td>
                                    <td><input type="checkbox" name="lp_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="lp_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="lp_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="lp_kiri_grade" value="grade3"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>CV4</td>
                                    <td><input type="checkbox" name="cv4_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="cv4_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="cv4_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="cv4_kanan_grade" value="grade3"></td>
                                    <td>CV4</td>
                                    <td><input type="checkbox" name="cv4_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="cv4_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="cv4_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="cv4_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>CV6</td>
                                    <td><input type="checkbox" name="cv6_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="cv6_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="cv6_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="cv6_kanan_grade" value="grade3"></td>
                                    <td>CV6</td>
                                    <td><input type="checkbox" name="cv6_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="cv6_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="cv6_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="cv6_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>L1</td>
                                    <td><input type="checkbox" name="l1_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="l1_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="l1_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="l1_kanan_grade" value="grade3"></td>
                                    <td>L1</td>
                                    <td><input type="checkbox" name="l1_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="l1_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="l1_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="l1_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>11</td>
                                    <td>L3</td>
                                    <td><input type="checkbox" name="l3_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="l3_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="l3_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="l3_kanan_grade" value="grade3"></td>
                                    <td>L3</td>
                                    <td><input type="checkbox" name="l3_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="l3_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="l3_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="l3_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>12</td>
                                    <td>PIRIFORMIS</td>
                                    <td><input type="checkbox" name="piriformis_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="piriformis_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="piriformis_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="piriformis_kanan_grade" value="grade3"></td>
                                    <td>PIRIFORMIS</td>
                                    <td><input type="checkbox" name="piriformis_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="piriformis_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="piriformis_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="piriformis_kiri_grade" value="grade3"></td>
                                </tr>
                                <tr>
                                    <td>13</td>
                                    <td>SENDOK</td>
                                    <td><input type="checkbox" name="sendok_kanan" value="sakit"></td>
                                    <td><input type="checkbox" name="sendok_kanan_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="sendok_kanan_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="sendok_kanan_grade" value="grade3"></td>
                                    <td>SENDOK</td>
                                    <td><input type="checkbox" name="sendok_kiri" value="sakit"></td>
                                    <td><input type="checkbox" name="sendok_kiri_grade" value="grade1"></td>
                                    <td><input type="checkbox" name="sendok_kiri_grade" value="grade2"></td>
                                    <td><input type="checkbox" name="sendok_kiri_grade" value="grade3"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group mt-3" id="history-info">
                        <label for="history_created">
                            Data rekam medis ditambah oleh <strong id="created_by"></strong>
                            <span id="updated_info">, dan diedit oleh <strong id="updated_by"></strong></span>
                        </label>
                    </div>
                    <div class="form-group" id="notif-wa">
                        <label for="address"> Kirim Notifikasi WhatsApp ? </label> <br>
                        <div class="custom-switch">
                            <label class="custom-switch mt-2">
                                <input type="checkbox" name="notifikasi" class="custom-switch-input"
                                    id="notifikasi-checkbox" checked>
                                <span class="custom-switch-indicator"></span>
                                <span class="custom-switch-description">Ya, Kirim Notifikasi WhatsApp</span>
                            </label>
                        </div>
                    </div>


                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="save-button" name="save-button"
                        class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Riwayat Kunjungan Pasien</h4>
                <div class="card-header-action">
                    <button type="button" class="btn btn-primary" onclick="add()">
                        <i class="fas fa-plus"></i> Tambah Riwayat
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Keluhan</th>
                                <th>Rekam Medis</th>
                                <th>Tanggal</th>
                                <th>Type</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    // JS Histories
    $("#table-2").dataTable({
        "processing": true,
        "serverSide": true,
        columns: [{
                "data": "no",
                "class": "",
                "width": "7%",
                'sortable': true
            },
            {
                "data": "complaint",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "medhis",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "date",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "type",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "action",
                "class": "text-center",
                "width": "30%",
                'sortable': false
            },
        ],
        "order": [],
        "ajax": {
            "url": "<?= site_url('history/fetch/' . $patient->id) ?>",
            "type": "POST",
            "data": function(d) {
                d["<?= csrf_token() ?>"] = "<?= csrf_hash() ?>";
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "rowCallback": function(row, data) {
            if (data.is_delete === "1") {
                $(row).css('color', 'red').css('text-decoration', 'line-through');
            }
            if (data.kejantanan === "ya") {
                $(row).css('color', 'black');
            }
        }
    });

    // Ini di nyalakan kalo mau pakai tag 

    // var complaintTagify, medhisTagify, resultTagify;
    // $(document).ready(function() {
    //     // Inisialisasi
    //     complaintTagify = new Tagify(document.querySelector('textarea[name="complaint"]'), {
    //         originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(',')
    //     });

    //     medhisTagify = new Tagify(document.querySelector('textarea[name="medhis"]'), {
    //         originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(',')
    //     });

    //     resultTagify = new Tagify(document.querySelector('textarea[name="result"]'), {
    //         originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(',')
    //     });
    // });

    function formatDate(dateTime) {
        const date = new Date(dateTime);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function formatDateForInput(dateTime) {
        const date = new Date(dateTime);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    var activeTerapis = <?= json_encode($terapis) ?>;


    function add() {
        var modal = $('#exampleModal');
        var form = $('#save_data'); // Pakai ID form lu yang spesifik

        modal.appendTo('body').modal('show');
        modal.find('.modal-title').text('Tambah Riwayat Pasien');

        // 2. Reset Form & Set Action (Anti-Error Reset)
        if (form.length > 0) {
            form.attr('action', '<?= site_url('history/store') ?>');
            form[0].reset();

            // Isi ID tersembunyi
            form.find('input[name="patient_id"]').val('<?= $patient->id ?? "" ?>');
            form.find('input[name="queue_id"]').val('<?= $queue_id ?? "" ?>');

            // Reset element manual
            form.find(':input').prop('readonly', false);
            form.find(':checkbox').prop('disabled', false);
        }

        // 3. Reset Tagify (Hanya jika variabelnya ada)
        const tagifyLists = [{
                obj: typeof complaintTagify !== 'undefined' ? complaintTagify : null
            },
            {
                obj: typeof medhisTagify !== 'undefined' ? medhisTagify : null
            },
            {
                obj: typeof resultTagify !== 'undefined' ? resultTagify : null
            }
        ];

        tagifyLists.forEach(item => {
            if (item.obj) {
                item.obj.removeAllTags();
                item.obj.setReadonly(false);
            }
        });

        // 4. UI Reset
        $('#terapi-kejantanan').show();
        $('#kejantanan').prop('checked', false);
        $('#history-info').hide();
        $('#notif-wa').show();
        $('#date_modified_group').addClass('d-none');
        $('#region_history').prop('disabled', false);

        // Set Tanggal Hari Ini
        if (typeof formatDateForInput === 'function') {
            $('#date').val(formatDateForInput(new Date()));
        }

        // 5. Reset Select2 Terapis
        var terapisSelect = $('.terapis');
        terapisSelect.prop('disabled', false);
        terapisSelect.empty();

        // Pastikan activeTerapis ada isinya
        if (typeof activeTerapis !== 'undefined') {
            activeTerapis.forEach(function(t) {
                terapisSelect.append(new Option(t.nama, t.id));
            });
        }

        terapisSelect.select2({
            placeholder: "-- Pilih Terapis --",
            allowClear: true,
            dropdownParent: modal // Penting biar Select2 gak macet di dalem modal
        }).val([]).trigger('change');

        $('#save-button').show();
    }
    // function add() {
    //     $('#exampleModal').appendTo('body');
    //     $('#exampleModal').modal('show');
    //     $('#exampleModal .modal-title').text('Tambah Riwayat Pasien');
    //     // $('#exampleModal form').attr('action', '{{ site_url('history/store') }}');
    //     $('#exampleModal form').attr('action', '<?= site_url('history/store') ?>');
    //     $('#exampleModal form')[0].reset();

    //     $('#exampleModal form').find('input[name="patient_id"]').val('<?= $patient->id ?>');
    //     $('#exampleModal form').find('input[name="queue_id"]').val('<?= $queue_id ?>');
    //     document.getElementById("terapi-kejantanan").style.display = "block";
    //     document.getElementById("kejantanan").checked = false;

    //     if (typeof complaintTagify !== 'undefined') {
    //         complaintTagify.removeAllTags();
    //         complaintTagify.setReadonly(false);
    //     }

    //     if (typeof medhisTagify !== 'undefined') {
    //         medhisTagify.removeAllTags();
    //         medhisTagify.setReadonly(false);
    //     }

    //     if (typeof resultTagify !== 'undefined') {
    //         resultTagify.removeAllTags();
    //         resultTagify.setReadonly(false);
    //     }


    //     $('#history-info').hide();

    //     $('#notif-wa').show();

    //     var today = formatDate(new Date());
    //     $('#date').val(formatDateForInput(new Date()));
    //     $('#date_modified_group').addClass('d-none');
    //     $('#exampleModal form :input').prop('readonly', false);
    //     $('#exampleModal form :checkbox').prop('disabled', false);
    //     $('#region_history').prop('disabled', false);

    //     // complaintTagify.removeAllTags();
    //     // medhisTagify.removeAllTags();
    //     // resultTagify.removeAllTags();

    //     complaintTagify.setReadonly(false);
    //     medhisTagify.setReadonly(false);
    //     resultTagify.setReadonly(false);

    //     $('.terapis').prop('disabled', false);
    //     $('.terapis').select2({
    //         placeholder: "-- Pilih Terapis --",
    //         allowClear: true,
    //         minimumResultsForSearch: Infinity
    //     });

    //     $('.terapis').empty();

    //     activeTerapis.forEach(function(t) {
    //         let option = $('<option>', {
    //             value: t.id,
    //             text: t.nama
    //         });
    //         $('.terapis').append(option);
    //     });

    //     $('.terapis').val([]).trigger('change');

    //     $('#exampleModal #save-button').show();
    // }


    $(document).on('click', '#save-button', function(e) {
        e.preventDefault();
        const btn = $(this);

        let formData = new FormData();
        $('.modal.show').find('input[name], textarea[name], select[name]').each(function() {
            let input = $(this);
            let name = input.attr('name');
            let value = input.val();

            if (['complaint', 'medhis', 'results', 'result'].includes(name)) return;

            if (input.is(':checkbox')) {
                if (input.is(':checked')) {
                    formData.append(name, value);
                }
            } else if (input.is(':radio')) {
                if (input.is(':checked')) {
                    formData.set(name, value);
                }
            } else {
                formData.set(name, value);
            }
        });

        if (typeof complaintTagify !== 'undefined') {
            formData.set('complaint', JSON.stringify(complaintTagify.value || []));
        }

        if (typeof medhisTagify !== 'undefined') {
            formData.set('medhis', JSON.stringify(medhisTagify.value || []));
        }

        if (typeof resultTagify !== 'undefined') {
            // Gunakan 'results' (pakai S) sesuai atribut name di HTML lu
            formData.set('results', JSON.stringify(resultTagify.value || []));
        }

        // console.log("=== SCANNING COMPLETED ===");
        // for (let [key, val] of formData.entries()) {
        //     console.log(key + ": " + val);
        // }

        $.ajax({
            url: "<?= site_url('history/store') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token-hash"]').attr('content')
            },
            beforeSend: function() {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                if (response.status) {
                    $('.modal.show').modal('hide');
                    Swal.fire('Berhasil!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                    console.log("Server Error Detail:", response);
                    btn.prop('disabled', false).text('Simpan');
                }
            },
            error: function(xhr) {
                console.error("Fatal Error:", xhr.responseText);
                btn.prop('disabled', false).text('Simpan');
            }
        });
    });

    function updateHistoryInfo(data) {
        $('#created_by').text(data.history_created_by);
        if (data.history_updated_by && data.history_updated_by.trim() !== '-') {
            $('#updated_by').text(data.history_updated_by);
            $('#updated_info').show();
        } else {
            $('#updated_info').hide();
        }
    }


    function show(id) {

        var form = $('#save_data');
        if (form.length > 0) {
            form[0].reset();
        }

        // $('#exampleModal form')[0].reset();
        $('input[type="checkbox"]').prop('checked', false);
        $('input[type="radio"]').prop('checked', false);


        $.ajax({
            url: "<?= site_url('history/show/') ?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                console.log("Data received from server:", data);
                $('#exampleModal').appendTo('body').modal('show');
                $('#exampleModal form').attr('action', '<?= site_url('history/update') ?>');
                $('#exampleModal .modal-title').text('Detil Riwayat Pasien');

                // Hide the WhatsApp notification section
                $('#notif-wa').hide();

                $('#exampleModal').modal('show');
                document.getElementById("terapi-kejantanan").style.display = "block";

                if (data.kejantanan === 'ya') {
                    document.getElementById('kejantanan').checked = true;
                } else {
                    document.getElementById('kejantanan').checked = false;
                }
                toggleTerapiForm();

                if (data.ereksi) {
                    $(`input[name="ereksi"][value="${data.ereksi}"]`).prop('checked', true);
                }
                if (data.porno) {
                    $(`input[name="nonton_porno"][value="${data.porno}"]`).prop('checked', true);
                }
                $('textarea[name="complaint"]').val(data.complaint && data.complaint !== '-' ? data.complaint : '');
                $('textarea[name="medhis"]').val(data.medhis && data.medhis !== '-' ? data.medhis : '');

                if (typeof resultTagify !== 'undefined') {
                    resultTagify.removeAllTags();
                    if (data.results && data.results !== '-') {
                        resultTagify.addTags(data.results.split(', '));
                    }
                } else {
                    $('textarea[name="results"]').val(data.results);
                }


                $('#history-info').show();
                updateHistoryInfo(data);

                if (data.history_region) {
                    $('#region_history').val(data.history_region).trigger('change');
                } else {
                    $('#region_history').val('');
                }

                $('.terapis').empty();

                let selectedTerapisIds = data.selected_terapis.map(t => t.id.toString());

                data.active_terapis.forEach(function(t) {
                    let isSelected = selectedTerapisIds.includes(t.id.toString());

                    let option = $('<option>', {
                        value: t.id,
                        text: t.nama,
                        selected: isSelected
                    });
                    $('.terapis').append(option);
                });

                data.selected_terapis.forEach(function(t) {
                    let isSelected = selectedTerapisIds.includes(t.id.toString());

                    let option = $('<option>', {
                        value: t.id,
                        text: t.nama + ' (Non-Aktif)',
                        disabled: true,
                        selected: isSelected
                    });

                    if (!$('.terapis option[value="' + t.id + '"]').length) {
                        $('.terapis').append(option);
                    }
                });

                $('.terapis').trigger('change');

                $('input[name="processAt"]').val(data.process_at);
                $('input[name="finishAt"]').val(data.finish_at);
                $('input[name="timeConsume"]').val(data.time_consume);
                $('input[name="id"]').val(data.id);
                $('input[name="patient_id"]').val(data.patient_id);
                $('input[name="checkup"]').val(data.checkup);
                $('input[name="cervical"]').val(data.cervical);
                $('input[name="thoraxal"]').val(data.thoraxal);
                $('input[name="lumbar"]').val(data.lumbar);
                $('input[name="sacrum"]').val(data.sacrum);
                $('input[name="sacral"]').val(data.sacral);
                $('input[name="pelvis"]').val(data.pelvis);
                $('input[name="plintiran"]').val(data.plintiran);
                $('input[name="kompresi"]').val(data.kompresi);
                $('input[name="verteba"]').val(data.verteba);
                $('input[name="thorax"]').val(data.thorax);
                $('input[name="date"]').val(formatDateForInput(data.date));
                $('input[name="date_modified"]').val(formatDateForInput(data.date_modified));
                $('input[name="visualfoot"]').val(data.visualfoot);
                $('textarea[name="other"]').val(data.other);
                $('textarea[name="results"]').val(data.results);
                $('textarea[name="measure"]').val(data.measure);
                $('textarea[name="pubis"]').val(data.pubis);
                $('input[name="tensi"]').val(data.tensi);
                $('input[name="power"]').val(data.power);
                $('input[name="pr"]').val(data.pr);
                $('textarea[name="ket_vertebrata"]').val(data.keterangan_verteba);
                $('textarea[name="ket_thorax"]').val(data.keterangan_thorax);
                $('textarea[name="ket_kompresi"]').val(data.keterangan_kompresi);
                $('textarea[name="ket_plintiran"]').val(data.keterangan_plintiran);
                $('textarea[name="ket_viska"]').val(data.keterangan_visualfoot);
                $('input[name="ereksi"][value="' + data.ereksi + '"]').prop('checked', true);
                $('input[name="nonton_porno"][value="' + data.porno + '"]').prop('checked', true);
                $('input[name="sering_onani"][value="' + data.onani + '"]').prop('checked', true);
                $('input[name="ranjang"][value="' + data.ranjang + '"]').prop('checked', true);
                $('input[name="frekuensi_ranjang"][value="' + data.frekuensi_ranjang + '"]').prop('checked',
                    true);
                $('input[name="obat_kuat"][value="' + data.obat_kuat + '"]').prop('checked', true);
                $('textarea[name="penyebab"]').val(data.penyebab);

                // Reset checkboxes
                $('input[type="checkbox"]').prop('checked', false);

                // Set checkboxes based on data
                var vertebraArray = data.verteba ? data.verteba.split(',') : [];
                vertebraArray.forEach(function(value) {
                    $('input[name="vertebra[]"][value="' + value + '"]').prop('checked', true);
                });

                var thoraxArray = data.thorax ? data.thorax.split(',') : [];
                thoraxArray.forEach(function(value) {
                    $('input[name="thorax[]"][value="' + value + '"]').prop('checked', true);
                });

                var kompresiArray = data.kompresi ? data.kompresi.split(',') : [];
                kompresiArray.forEach(function(value) {
                    $('input[name="kompresi[]"][value="' + value + '"]').prop('checked', true);
                });

                var plintiranArray = data.plintiran ? data.plintiran.split(',') : [];
                plintiranArray.forEach(function(value) {
                    $('input[name="plintiran[]"][value="' + value + '"]').prop('checked', true);
                });

                var visualFootArray = data.visualfoot ? data.visualfoot.split(',') : [];
                visualFootArray.forEach(function(value) {
                    $('input[name="visual_kaki[]"][value="' + value + '"]').prop('checked', true);
                });

                var pubisArray = data.pubis ? data.pubis.split(',') : [];
                pubisArray.forEach(function(value) {
                    $('input[name="pubis[]"][value="' + value + '"]').prop('checked', true);
                });

                var terapiForm = document.getElementById("terapi-form");
                var kesForm = document.getElementById("pemeriksaan");
                if (data.kejantanan == 'ya') {
                    $('#kejantanan').prop('checked', true);
                    terapiForm.style.display = "block";
                    kesForm.style.display = "block";
                } else {
                    $('#kejantanan').prop('checked', false);
                    terapiForm.style.display = "none";
                    kesForm.style.display = "none";
                }

                //Handle odp kiri
                var otot_dada_perut_kiri = data.otot_dada_perut_kiri ? data.otot_dada_perut_kiri.split(
                    ',') : [];
                if (otot_dada_perut_kiri[0] === 'sakit') {
                    $('input[name="odp_kiri"]').prop('checked', true);
                }
                if (otot_dada_perut_kiri[1]) {
                    $('input[name="odp_kiri_grade"][value="' + otot_dada_perut_kiri[1] + '"]').prop(
                        'checked', true);
                }

                // Handle odp kanan
                var otot_dada_perut_kanan = data.otot_dada_perut_kanan ? data.otot_dada_perut_kanan.split(
                    ',') : [];
                if (otot_dada_perut_kanan[0] === 'sakit') {
                    $('input[name="odp_kanan"]').prop('checked', true);
                }
                if (otot_dada_perut_kanan[1]) {
                    $('input[name="odp_kanan_grade"][value="' + otot_dada_perut_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle vital kiri
                var vital_kiri = data.vital_kiri ? data.vital_kiri.split(',') : [];
                if (vital_kiri[0] === 'sakit') {
                    $('input[name="vital_kiri"]').prop('checked', true);
                }
                if (vital_kiri[1]) {
                    $('input[name="vital_kiri_grade"][value="' + vital_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle vital kanan
                var vital_kanan = data.vital_kanan ? data.vital_kanan.split(',') : [];
                if (vital_kanan[0] === 'sakit') {
                    $('input[name="vital_kanan"]').prop('checked', true);
                }
                if (vital_kanan[1]) {
                    $('input[name="vital_kanan_grade"][value="' + vital_kanan[1] + '"]').prop('checked',
                        true);
                }

                // Handle kelenjar kiri
                var kelenjar_kiri = data.kelenjar_kiri ? data.kelenjar_kiri.split(',') : [];
                if (kelenjar_kiri[0] === 'sakit') {
                    $('input[name="kelenjar_kiri"]').prop('checked', true);
                }
                if (kelenjar_kiri[1]) {
                    $('input[name="kelenjar_kiri_grade"][value="' + kelenjar_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle kelenjar kanan
                var kelenjar_kanan = data.kelenjar_kanan ? data.kelenjar_kanan.split(',') : [];
                if (kelenjar_kanan[0] === 'sakit') {
                    $('input[name="kelenjar_kanan"]').prop('checked', true);
                }
                if (kelenjar_kanan[1]) {
                    $('input[name="kelenjar_kanan_grade"][value="' + kelenjar_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle hormon kiri
                var hormon_kiri = data.hormon_kiri ? data.hormon_kiri.split(',') : [];
                if (hormon_kiri[0] === 'sakit') {
                    $('input[name="hormon_kiri"]').prop('checked', true);
                }
                if (hormon_kiri[1]) {
                    $('input[name="hormon_kiri_grade"][value="' + hormon_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle hormon kanan
                var hormon_kanan = data.hormon_kanan ? data.hormon_kanan.split(',') : [];
                if (hormon_kanan[0] === 'sakit') {
                    $('input[name="hormon_kanan"]').prop('checked', true);
                }
                if (hormon_kanan[1]) {
                    $('input[name="hormon_kanan_grade"][value="' + hormon_kanan[1] + '"]').prop('checked',
                        true);
                }

                //Handle tulang kering kiri
                var tulang_kering_kiri = data.tulang_kering_kiri ? data.tulang_kering_kiri.split(',') : [];
                if (tulang_kering_kiri[0] === 'sakit') {
                    $('input[name="tk_kiri"]').prop('checked', true);
                }
                if (tulang_kering_kiri[1]) {
                    $('input[name="tk_kiri_grade"][value="' + tulang_kering_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle tulang kering kanan
                var tulang_kering_kanan = data.tulang_kering_kanan ? data.tulang_kering_kanan.split(',') : [];
                if (tulang_kering_kanan[0] === 'sakit') {
                    $('input[name="tk_kanan"]').prop('checked', true);
                }
                if (tulang_kering_kanan[1]) {
                    $('input[name="tk_kanan_grade"][value="' + tulang_kering_kanan[1] + '"]').prop(
                        'checked', true);
                }

                //Handle femur dalam kiri
                var femur_dalam_kiri = data.femur_dalam_kiri ? data.femur_dalam_kiri.split(',') : [];
                if (femur_dalam_kiri[0] === 'sakit') {
                    $('input[name="fd_kiri"]').prop('checked', true);
                }
                if (femur_dalam_kiri[1]) {
                    $('input[name="fd_kiri_grade"][value="' + femur_dalam_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle femur dalam kanan
                var femur_dalam_kanan = data.femur_dalam_kanan ? data.femur_dalam_kanan.split(',') : [];
                if (femur_dalam_kanan[0] === 'sakit') {
                    $('input[name="fd_kanan"]').prop('checked', true);
                }
                if (femur_dalam_kanan[1]) {
                    $('input[name="fd_kanan_grade"][value="' + femur_dalam_kanan[1] + '"]').prop('checked',
                        true);
                }

                //Handle lingkar perut atas
                var lingkar_perut_atas = data.lingkar_perut_atas ? data.lingkar_perut_atas.split(',') : [];
                if (lingkar_perut_atas[0] === 'sakit') {
                    $('input[name="lp_atas"]').prop('checked', true);
                }
                if (lingkar_perut_atas[1]) {
                    $('input[name="lp_atas_grade"][value="' + lingkar_perut_atas[1] + '"]').prop('checked',
                        true);
                }

                // Handle lingkar perut bawah
                var lingkar_perut_bawah = data.lingkar_perut_bawah ? data.lingkar_perut_bawah.split(',') : [];
                if (lingkar_perut_bawah[0] === 'sakit') {
                    $('input[name="lp_bawah"]').prop('checked', true);
                }
                if (lingkar_perut_bawah[1]) {
                    $('input[name="lp_bawah_grade"][value="' + lingkar_perut_bawah[1] + '"]').prop(
                        'checked', true);
                }

                // Handle lingkar perut kiri
                var lingkar_perut_kiri = data.lingkar_perut_kiri ? data.lingkar_perut_kiri.split(',') : [];
                if (lingkar_perut_kiri[0] === 'sakit') {
                    $('input[name="lp_kiri"]').prop('checked', true);
                }
                if (lingkar_perut_kiri[1]) {
                    $('input[name="lp_kiri_grade"][value="' + lingkar_perut_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle lingkar perut kanan
                var lingkar_perut_kanan = data.lingkar_perut_kanan ? data.lingkar_perut_kanan.split(',') : [];
                if (lingkar_perut_kanan[0] === 'sakit') {
                    $('input[name="lp_kanan"]').prop('checked', true);
                }
                if (lingkar_perut_kanan[1]) {
                    $('input[name="lp_kanan_grade"][value="' + lingkar_perut_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle cv4 kiri
                var cv4_kiri = data.cv4_kiri ? data.cv4_kiri.split(',') : [];
                if (cv4_kiri[0] === 'sakit') {
                    $('input[name="cv4_kiri"]').prop('checked', true);
                }
                if (cv4_kiri[1]) {
                    $('input[name="cv4_kiri_grade"][value="' + cv4_kiri[1] + '"]').prop('checked', true);
                }

                // Handle cv4 kanan
                var cv4_kanan = data.cv4_kanan ? data.cv4_kanan.split(',') : [];
                if (cv4_kanan[0] === 'sakit') {
                    $('input[name="cv4_kanan"]').prop('checked', true);
                }
                if (cv4_kanan[1]) {
                    $('input[name="cv4_kanan_grade"][value="' + cv4_kanan[1] + '"]').prop('checked', true);
                }

                // Handle cv6 kiri
                var cv6_kiri = data.cv6_kiri ? data.cv6_kiri.split(',') : [];
                if (cv6_kiri[0] === 'sakit') {
                    $('input[name="cv6_kiri"]').prop('checked', true);
                }
                if (cv6_kiri[1]) {
                    $('input[name="cv6_kiri_grade"][value="' + cv6_kiri[1] + '"]').prop('checked', true);
                }

                // Handle cv4 kanan
                var cv6_kanan = data.cv6_kanan ? data.cv6_kanan.split(',') : [];
                if (cv6_kanan[0] === 'sakit') {
                    $('input[name="cv6_kanan"]').prop('checked', true);
                }
                if (cv6_kanan[1]) {
                    $('input[name="cv6_kanan_grade"][value="' + cv6_kanan[1] + '"]').prop('checked', true);
                }

                // Handle l1 kiri
                var l1_kiri = data.l1_kiri ? data.l1_kiri.split(',') : [];
                if (l1_kiri[0] === 'sakit') {
                    $('input[name="l1_kiri"]').prop('checked', true);
                }
                if (l1_kiri[1]) {
                    $('input[name="l1_kiri_grade"][value="' + l1_kiri[1] + '"]').prop('checked', true);
                }

                // Handle cv4 kanan
                var l1_kanan = data.l1_kanan ? data.l1_kanan.split(',') : [];
                if (l1_kanan[0] === 'sakit') {
                    $('input[name="l1_kanan"]').prop('checked', true);
                }
                if (l1_kanan[1]) {
                    $('input[name="l1_kanan_grade"][value="' + l1_kanan[1] + '"]').prop('checked', true);
                }

                // Handle l3 kiri
                var l3_kiri = data.l3_kiri ? data.l3_kiri.split(',') : [];
                if (l3_kiri[0] === 'sakit') {
                    $('input[name="l3_kiri"]').prop('checked', true);
                }
                if (l3_kiri[1]) {
                    $('input[name="l3_kiri_grade"][value="' + l3_kiri[1] + '"]').prop('checked', true);
                }

                // Handle l3 kanan
                var l3_kanan = data.l3_kanan ? data.l3_kanan.split(',') : [];
                if (l3_kanan[0] === 'sakit') {
                    $('input[name="l3_kanan"]').prop('checked', true);
                }
                if (l3_kanan[1]) {
                    $('input[name="l3_kanan_grade"][value="' + l3_kanan[1] + '"]').prop('checked', true);
                }

                // Handle piriformis kiri
                var piriformis_kiri = data.piriformis_kiri ? data.piriformis_kiri.split(',') : [];
                if (piriformis_kiri[0] === 'sakit') {
                    $('input[name="piriformis_kiri"]').prop('checked', true);
                }
                if (piriformis_kiri[1]) {
                    $('input[name="piriformis_kiri_grade"][value="' + piriformis_kiri[1] + '"]').prop(
                        'checked', true);
                }

                // Handle piriformis kanan
                var piriformis_kanan = data.piriformis_kanan ? data.piriformis_kanan.split(',') : [];
                if (piriformis_kanan[0] === 'sakit') {
                    $('input[name="piriformis_kanan"]').prop('checked', true);
                }
                if (piriformis_kanan[1]) {
                    $('input[name="piriformis_kanan_grade"][value="' + piriformis_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle sendok kiri
                var sendok_kiri = data.sendok_kiri ? data.sendok_kiri.split(',') : [];
                if (sendok_kiri[0] === 'sakit') {
                    $('input[name="sendok_kiri"]').prop('checked', true);
                }
                if (sendok_kiri[1]) {
                    $('input[name="sendok_kiri_grade"][value="' + sendok_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle sendok kanan
                var sendok_kanan = data.sendok_kanan ? data.sendok_kanan.split(',') : [];
                if (sendok_kanan[0] === 'sakit') {
                    $('input[name="sendok_kanan"]').prop('checked', true);
                }
                if (sendok_kanan[1]) {
                    $('input[name="sendok_kanan_grade"][value="' + sendok_kanan[1] + '"]').prop('checked',
                        true);
                }

                function updateFormWithData(data) {
                    if (!$('input[name="ereksi"]').prop('disabled')) {
                        if (data.ereksi === 'ya') {
                            $('input[name="ereksi"][value="ya"]').prop('checked', true);
                        } else if (data.ereksi === 'tidak') {
                            $('input[name="ereksi"][value="tidak"]').prop('checked', true);
                        } else {
                            $('input[name="ereksi"]').prop('checked', false);
                        }
                    }

                    if (!$('input[name="obat_kuat"]').prop('disabled')) {
                        if (data.obat_kuat === 'ya') {
                            $('input[name="obat_kuat"][value="ya"]').prop('checked', true);
                        } else if (data.obat_kuat === 'tidak') {
                            $('input[name="obat_kuat"][value="tidak"]').prop('checked', true);
                        } else {
                            $('input[name="obat_kuat"]').prop('checked', false);
                        }
                    }

                    if (!$('input[name="nonton_porno"]').prop('disabled')) {
                        if (data.porno === 'ya') {
                            showFrequency('nonton-porn-frequency', true);
                            $('input[name="nonton_porno"][value="ya"]').prop('checked', true);
                            $('input[name="frekuensi_nonton_porno"][value="' + data.frekuensi_porno + '"]')
                                .prop('checked', true);
                        } else if (data.porno === 'tidak') {
                            $('input[name="nonton_porno"][value="tidak"]').prop('checked', true);
                            showFrequency('nonton-porn-frequency', false);
                        } else {
                            $('input[name="nonton_porno"]').prop('checked', false);
                            showFrequency('nonton-porn-frequency', false);
                        }
                    }

                    if (!$('input[name="sering_onani"]').prop('disabled')) {
                        if (data.onani === 'ya') {
                            showFrequency('onani-frequency', true);
                            $('input[name="sering_onani"][value="ya"]').prop('checked', true);
                            $('input[name="frekuensi_onani"][value="' + data.frekuensi_onani + '"]').prop(
                                'checked', true);
                        } else if (data.onani === 'tidak') {
                            $('input[name="sering_onani"][value="tidak"]').prop('checked', true);
                            showFrequency('onani-frequency', false);
                        } else {
                            $('input[name="sering_onani"]').prop('checked', false);
                            showFrequency('onani-frequency', false);
                        }
                    }

                    if (!$('input[name="frekuensi_nonton_porno"]').prop('disabled')) {
                        toggleLainnyaTextbox('nonton-lainnya-textbox', data.frekuensi_porno === 'lainnya');
                        $('#nonton-lainnya-textbox').val(data.frekuensi_porno_lain || '');
                    }

                    if (!$('input[name="frekuensi_onani"]').prop('disabled')) {
                        toggleLainnyaTextbox('onani-lainnya-textbox', data.frekuensi_onani === 'lainnya');
                        $('#onani-lainnya-textbox').val(data.frekuensi_onani_lain || '');
                    }

                    if (!$('input[name="frekuensi_ranjang"]').prop('disabled')) {
                        toggleLainnyaTextbox('hubungan-lainnya-textbox', data.frekuensi_ranjang ===
                            'lainnya');
                        $('#hubungan-lainnya-textbox').val(data.frekuensi_ranjang_lain || '');
                    }
                }

                function updateFormStatus(data) {
                    var currentDate = new Date();
                    var recordDate = new Date(data.date_modified);
                    var timeDifference = Math.abs(currentDate - recordDate);
                    var dayDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));
                    // console.log('testt:' + data.type);


                    if (dayDifference > 1 && data.type !== 'draft') {
                        $('#exampleModal form :input').prop('readonly', true);
                        $('#exampleModal form :checkbox').prop('disabled', true);
                        $('#exampleModal form :radio').prop('disabled', true);

                        if (typeof complaintTagify !== 'undefined') complaintTagify.setReadonly(true);
                        if (typeof medhisTagify !== 'undefined') medhisTagify.setReadonly(true);
                        if (typeof resultTagify !== 'undefined') resultTagify.setReadonly(true);

                        $('.terapis').prop('disabled', true);
                        $('.terapis').select2();

                        $('#region_history').prop('disabled', true);

                        $('#exampleModal #save-button').hide();
                    } else {
                        $('#exampleModal form :input').prop('readonly', false);
                        $('#exampleModal form :checkbox').prop('disabled', false);

                        if (typeof complaintTagify !== 'undefined') complaintTagify.setReadonly(false);
                        if (typeof medhisTagify !== 'undefined') medhisTagify.setReadonly(false);
                        if (typeof resultTagify !== 'undefined') resultTagify.setReadonly(false);

                        $('.terapis').prop('disabled', false);
                        $('.terapis').select2();

                        $('#exampleModal #save-button').show();
                    }
                }
                updateFormStatus(data);
                updateFormWithData(data);

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                alert('Terjadi kesalahan, silahkan coba lagi...');
            }
        });
    }

    function duplicate(id) {
        // $('#exampleModal form')[0].reset();
        var form = $('#save_data');
        if (form.length > 0) {
            form[0].reset();
        }



        // complaintTagify = new Tagify($('textarea[name="complaint"]')[0]);
        // medhisTagify = new Tagify($('textarea[name="medhis"]')[0]);
        // resultTagify = new Tagify($('textarea[name="result"]')[0]);
        $('input[type="checkbox"]').prop('checked', false);
        $('input[type="radio"]').prop('checked', false);
        $('#region_history').prop('disabled', false);


        $.ajax({
            url: "<?= site_url('history/show/') ?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                // console.log("Data received from server:", data);
                var modal = $('#exampleModal');
                var form = $('#save_data');

                $('.modal-backdrop').remove();
                $('#exampleModal').modal('show');
                $('#exampleModal').on('shown.bs.modal', function() {
                    $(this).css('z-index', '1060');
                    $('.modal-backdrop').css('z-index', '1050');
                });

                $('#exampleModal form').attr('action', '<?= site_url('history/copy') ?>');
                $('#exampleModal .modal-title').text('Detil Riwayat Pasien');

                // Hide the WhatsApp notification section
                $('#notif-wa').show();
                $('#history-info').hide();

                if (typeof complaintTagify !== 'undefined') {
                    complaintTagify.removeAllTags();
                    if (data.complaint && data.complaint !== '-') {
                        complaintTagify.addTags(data.complaint.split(', '));
                    }
                }

                if (typeof medhisTagify !== 'undefined') {
                    medhisTagify.removeAllTags();
                    if (data.medhis && data.medhis !== '-') {
                        medhisTagify.addTags(data.medhis.split(', '));
                    }
                }

                if (typeof resultTagify !== 'undefined') {
                    resultTagify.removeAllTags();
                    if (data.results && data.results !== '-') {
                        resultTagify.addTags(data.results.split(', '));
                    }
                }

                if (data.history_region) {
                    $('#region_history').val(data.history_region).trigger('change');
                } else {
                    $('#region_history').val('').trigger('change');
                }

                // $('#history-info').hide();

                $('.terapis').empty();

                let selectedTerapisIds = data.selected_terapis.map(t => t.id.toString());

                data.active_terapis.forEach(function(t) {
                    let isSelected = selectedTerapisIds.includes(t.id.toString());

                    let option = $('<option>', {
                        value: t.id,
                        text: t.nama,
                        selected: isSelected
                    });
                    $('.terapis').append(option);
                });

                data.selected_terapis.forEach(function(t) {
                    let isSelected = selectedTerapisIds.includes(t.id.toString());

                    let option = $('<option>', {
                        value: t.id,
                        text: t.nama + ' (Non-Aktif)',
                        selected: isSelected
                    });

                    if (!$('.terapis option[value="' + t.id + '"]').length) {
                        $('.terapis').append(option);
                    }
                });

                $('.terapis').trigger('change');

                // Fill form fields with data
                $('input[name="id"]').val(data.id);
                $('input[name="patient_id"]').val(data.patient_id);
                $('input[name="checkup"]').val(data.checkup);
                $('input[name="cervical"]').val(data.cervical);
                $('input[name="thoraxal"]').val(data.thoraxal);
                $('input[name="lumbar"]').val(data.lumbar);
                $('input[name="sacrum"]').val(data.sacrum);
                $('input[name="sacral"]').val(data.sacral);
                $('input[name="pelvis"]').val(data.pelvis);
                $('input[name="plintiran"]').val(data.plintiran);
                $('input[name="kompresi"]').val(data.kompresi);
                $('input[name="verteba"]').val(data.verteba);
                $('input[name="thorax"]').val(data.thorax);
                $('input[name="date"]').val(formatDateForInput(data.date));
                $('input[name="date_modified"]').val(formatDateForInput(data.date_modified));
                $('input[name="visualfoot"]').val(data.visualfoot);
                $('textarea[name="other"]').val(data.other);
                $('textarea[name="results"]').val(data.results);
                $('textarea[name="measure"]').val(data.measure);
                $('textarea[name="pubis"]').val(data.pubis);
                $('input[name="tensi"]').val(data.tensi);
                $('input[name="power"]').val(data.power);
                $('input[name="pr"]').val(data.pr);
                $('textarea[name="ket_vertebrata"]').val(data.keterangan_verteba);
                $('textarea[name="ket_thorax"]').val(data.keterangan_thorax);
                $('textarea[name="ket_kompresi"]').val(data.keterangan_kompresi);
                $('textarea[name="ket_plintiran"]').val(data.keterangan_plintiran);
                $('textarea[name="ket_viska"]').val(data.keterangan_visualfoot);
                $('input[name="ereksi"][value="' + data.ereksi + '"]').prop('checked', true);
                $('input[name="nonton_porno"][value="' + data.porno + '"]').prop('checked', true);
                $('input[name="sering_onani"][value="' + data.onani + '"]').prop('checked', true);
                $('input[name="ranjang"][value="' + data.ranjang + '"]').prop('checked', true);
                $('input[name="frekuensi_ranjang"][value="' + data.frekuensi_ranjang + '"]').prop('checked',
                    true);
                $('input[name="obat_kuat"][value="' + data.obat_kuat + '"]').prop('checked', true);
                $('textarea[name="penyebab"]').val(data.penyebab);

                // Reset checkboxes
                $('input[type="checkbox"]').prop('checked', false);

                // Set checkboxes based on data
                var vertebraArray = data.verteba ? data.verteba.split(',') : [];
                vertebraArray.forEach(function(value) {
                    $('input[name="vertebra[]"][value="' + value + '"]').prop('checked', true);
                });

                var thoraxArray = data.thorax ? data.thorax.split(',') : [];
                thoraxArray.forEach(function(value) {
                    $('input[name="thorax[]"][value="' + value + '"]').prop('checked', true);
                });

                var kompresiArray = data.kompresi ? data.kompresi.split(',') : [];
                kompresiArray.forEach(function(value) {
                    $('input[name="kompresi[]"][value="' + value + '"]').prop('checked', true);
                });

                var plintiranArray = data.plintiran ? data.plintiran.split(',') : [];
                plintiranArray.forEach(function(value) {
                    $('input[name="plintiran[]"][value="' + value + '"]').prop('checked', true);
                });

                var visualFootArray = data.visualfoot ? data.visualfoot.split(',') : [];
                visualFootArray.forEach(function(value) {
                    $('input[name="visual_kaki[]"][value="' + value + '"]').prop('checked', true);
                });

                var pubisArray = data.pubis ? data.pubis.split(',') : [];
                pubisArray.forEach(function(value) {
                    $('input[name="pubis[]"][value="' + value + '"]').prop('checked', true);
                });

                //Handle odp kiri
                var otot_dada_perut_kiri = data.otot_dada_perut_kiri ? data.otot_dada_perut_kiri.split(
                    ',') : [];
                if (otot_dada_perut_kiri[0] === 'sakit') {
                    $('input[name="odp_kiri"]').prop('checked', true);
                }
                if (otot_dada_perut_kiri[1]) {
                    $('input[name="odp_kiri_grade"][value="' + otot_dada_perut_kiri[1] + '"]').prop(
                        'checked', true);
                }

                // Handle odp kanan
                var otot_dada_perut_kanan = data.otot_dada_perut_kanan ? data.otot_dada_perut_kanan.split(
                    ',') : [];
                if (otot_dada_perut_kanan[0] === 'sakit') {
                    $('input[name="odp_kanan"]').prop('checked', true);
                }
                if (otot_dada_perut_kanan[1]) {
                    $('input[name="odp_kanan_grade"][value="' + otot_dada_perut_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle vital kiri
                var vital_kiri = data.vital_kiri ? data.vital_kiri.split(',') : [];
                if (vital_kiri[0] === 'sakit') {
                    $('input[name="vital_kiri"]').prop('checked', true);
                }
                if (vital_kiri[1]) {
                    $('input[name="vital_kiri_grade"][value="' + vital_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle vital kanan
                var vital_kanan = data.vital_kanan ? data.vital_kanan.split(',') : [];
                if (vital_kanan[0] === 'sakit') {
                    $('input[name="vital_kanan"]').prop('checked', true);
                }
                if (vital_kanan[1]) {
                    $('input[name="vital_kanan_grade"][value="' + vital_kanan[1] + '"]').prop('checked',
                        true);
                }

                // Handle kelenjar kiri
                var kelenjar_kiri = data.kelenjar_kiri ? data.kelenjar_kiri.split(',') : [];
                if (kelenjar_kiri[0] === 'sakit') {
                    $('input[name="kelenjar_kiri"]').prop('checked', true);
                }
                if (kelenjar_kiri[1]) {
                    $('input[name="kelenjar_kiri_grade"][value="' + kelenjar_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle kelenjar kanan
                var kelenjar_kanan = data.kelenjar_kanan ? data.kelenjar_kanan.split(',') : [];
                if (kelenjar_kanan[0] === 'sakit') {
                    $('input[name="kelenjar_kanan"]').prop('checked', true);
                }
                if (kelenjar_kanan[1]) {
                    $('input[name="kelenjar_kanan_grade"][value="' + kelenjar_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle hormon kiri
                var hormon_kiri = data.hormon_kiri ? data.hormon_kiri.split(',') : [];
                if (hormon_kiri[0] === 'sakit') {
                    $('input[name="hormon_kiri"]').prop('checked', true);
                }
                if (hormon_kiri[1]) {
                    $('input[name="hormon_kiri_grade"][value="' + hormon_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle hormon kanan
                var hormon_kanan = data.hormon_kanan ? data.hormon_kanan.split(',') : [];
                if (hormon_kanan[0] === 'sakit') {
                    $('input[name="hormon_kanan"]').prop('checked', true);
                }
                if (hormon_kanan[1]) {
                    $('input[name="hormon_kanan_grade"][value="' + hormon_kanan[1] + '"]').prop('checked',
                        true);
                }

                //Handle tulang kering kiri
                var tulang_kering_kiri = data.tulang_kering_kiri ? data.tulang_kering_kiri.split(',') : [];
                if (tulang_kering_kiri[0] === 'sakit') {
                    $('input[name="tk_kiri"]').prop('checked', true);
                }
                if (tulang_kering_kiri[1]) {
                    $('input[name="tk_kiri_grade"][value="' + tulang_kering_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle tulang kering kanan
                var tulang_kering_kanan = data.tulang_kering_kanan ? data.tulang_kering_kanan.split(',') : [];
                if (tulang_kering_kanan[0] === 'sakit') {
                    $('input[name="tk_kanan"]').prop('checked', true);
                }
                if (tulang_kering_kanan[1]) {
                    $('input[name="tk_kanan_grade"][value="' + tulang_kering_kanan[1] + '"]').prop(
                        'checked', true);
                }

                //Handle femur dalam kiri
                var femur_dalam_kiri = data.femur_dalam_kiri ? data.femur_dalam_kiri.split(',') : [];
                if (femur_dalam_kiri[0] === 'sakit') {
                    $('input[name="fd_kiri"]').prop('checked', true);
                }
                if (femur_dalam_kiri[1]) {
                    $('input[name="fd_kiri_grade"][value="' + femur_dalam_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle femur dalam kanan
                var femur_dalam_kanan = data.femur_dalam_kanan ? data.femur_dalam_kanan.split(',') : [];
                if (femur_dalam_kanan[0] === 'sakit') {
                    $('input[name="fd_kanan"]').prop('checked', true);
                }
                if (femur_dalam_kanan[1]) {
                    $('input[name="fd_kanan_grade"][value="' + femur_dalam_kanan[1] + '"]').prop('checked',
                        true);
                }

                //Handle lingkar perut atas
                var lingkar_perut_atas = data.lingkar_perut_atas ? data.lingkar_perut_atas.split(',') : [];
                if (lingkar_perut_atas[0] === 'sakit') {
                    $('input[name="lp_atas"]').prop('checked', true);
                }
                if (lingkar_perut_atas[1]) {
                    $('input[name="lp_atas_grade"][value="' + lingkar_perut_atas[1] + '"]').prop('checked',
                        true);
                }

                // Handle lingkar perut bawah
                var lingkar_perut_bawah = data.lingkar_perut_bawah ? data.lingkar_perut_bawah.split(',') : [];
                if (lingkar_perut_bawah[0] === 'sakit') {
                    $('input[name="lp_bawah"]').prop('checked', true);
                }
                if (lingkar_perut_bawah[1]) {
                    $('input[name="lp_bawah_grade"][value="' + lingkar_perut_bawah[1] + '"]').prop(
                        'checked', true);
                }

                // Handle lingkar perut kiri
                var lingkar_perut_kiri = data.lingkar_perut_kiri ? data.lingkar_perut_kiri.split(',') : [];
                if (lingkar_perut_kiri[0] === 'sakit') {
                    $('input[name="lp_kiri"]').prop('checked', true);
                }
                if (lingkar_perut_kiri[1]) {
                    $('input[name="lp_kiri_grade"][value="' + lingkar_perut_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle lingkar perut kanan
                var lingkar_perut_kanan = data.lingkar_perut_kanan ? data.lingkar_perut_kanan.split(',') : [];
                if (lingkar_perut_kanan[0] === 'sakit') {
                    $('input[name="lp_kanan"]').prop('checked', true);
                }
                if (lingkar_perut_kanan[1]) {
                    $('input[name="lp_kanan_grade"][value="' + lingkar_perut_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle cv4 kiri
                var cv4_kiri = data.cv4_kiri ? data.cv4_kiri.split(',') : [];
                if (cv4_kiri[0] === 'sakit') {
                    $('input[name="cv4_kiri"]').prop('checked', true);
                }
                if (cv4_kiri[1]) {
                    $('input[name="cv4_kiri_grade"][value="' + cv4_kiri[1] + '"]').prop('checked', true);
                }

                // Handle cv4 kanan
                var cv4_kanan = data.cv4_kanan ? data.cv4_kanan.split(',') : [];
                if (cv4_kanan[0] === 'sakit') {
                    $('input[name="cv4_kanan"]').prop('checked', true);
                }
                if (cv4_kanan[1]) {
                    $('input[name="cv4_kanan_grade"][value="' + cv4_kanan[1] + '"]').prop('checked', true);
                }

                var terapiForm = document.getElementById("terapi-form");
                var kesForm = document.getElementById("pemeriksaan");
                if (data.kejantanan == 'ya') {
                    $('#kejantanan').prop('checked', true);
                    terapiForm.style.display = "block";
                    kesForm.style.display = "block";
                } else {
                    $('#kejantanan').prop('checked', false);
                    terapiForm.style.display = "none";
                    kesForm.style.display = "none";
                }

                // Handle cv6 kiri
                var cv6_kiri = data.cv6_kiri ? data.cv6_kiri.split(',') : [];
                if (cv6_kiri[0] === 'sakit') {
                    $('input[name="cv6_kiri"]').prop('checked', true);
                }
                if (cv6_kiri[1]) {
                    $('input[name="cv6_kiri_grade"][value="' + cv6_kiri[1] + '"]').prop('checked', true);
                }

                // Handle cv4 kanan
                var cv6_kanan = data.cv6_kanan ? data.cv6_kanan.split(',') : [];
                if (cv6_kanan[0] === 'sakit') {
                    $('input[name="cv6_kanan"]').prop('checked', true);
                }
                if (cv6_kanan[1]) {
                    $('input[name="cv6_kanan_grade"][value="' + cv6_kanan[1] + '"]').prop('checked', true);
                }

                // Handle l1 kiri
                var l1_kiri = data.l1_kiri ? data.l1_kiri.split(',') : [];
                if (l1_kiri[0] === 'sakit') {
                    $('input[name="l1_kiri"]').prop('checked', true);
                }
                if (l1_kiri[1]) {
                    $('input[name="l1_kiri_grade"][value="' + l1_kiri[1] + '"]').prop('checked', true);
                }

                // Handle cv4 kanan
                var l1_kanan = data.l1_kanan ? data.l1_kanan.split(',') : [];
                if (l1_kanan[0] === 'sakit') {
                    $('input[name="l1_kanan"]').prop('checked', true);
                }
                if (l1_kanan[1]) {
                    $('input[name="l1_kanan_grade"][value="' + l1_kanan[1] + '"]').prop('checked', true);
                }

                // Handle l3 kiri
                var l3_kiri = data.l3_kiri ? data.l3_kiri.split(',') : [];
                if (l3_kiri[0] === 'sakit') {
                    $('input[name="l3_kiri"]').prop('checked', true);
                }
                if (l3_kiri[1]) {
                    $('input[name="l3_kiri_grade"][value="' + l3_kiri[1] + '"]').prop('checked', true);
                }

                // Handle l3 kanan
                var l3_kanan = data.l3_kanan ? data.l3_kanan.split(',') : [];
                if (l3_kanan[0] === 'sakit') {
                    $('input[name="l3_kanan"]').prop('checked', true);
                }
                if (l3_kanan[1]) {
                    $('input[name="l3_kanan_grade"][value="' + l3_kanan[1] + '"]').prop('checked', true);
                }

                // Handle piriformis kiri
                var piriformis_kiri = data.piriformis_kiri ? data.piriformis_kiri.split(',') : [];
                if (piriformis_kiri[0] === 'sakit') {
                    $('input[name="piriformis_kiri"]').prop('checked', true);
                }
                if (piriformis_kiri[1]) {
                    $('input[name="piriformis_kiri_grade"][value="' + piriformis_kiri[1] + '"]').prop(
                        'checked', true);
                }

                // Handle piriformis kanan
                var piriformis_kanan = data.piriformis_kanan ? data.piriformis_kanan.split(',') : [];
                if (piriformis_kanan[0] === 'sakit') {
                    $('input[name="piriformis_kanan"]').prop('checked', true);
                }
                if (piriformis_kanan[1]) {
                    $('input[name="piriformis_kanan_grade"][value="' + piriformis_kanan[1] + '"]').prop(
                        'checked', true);
                }

                // Handle sendok kiri
                var sendok_kiri = data.sendok_kiri ? data.sendok_kiri.split(',') : [];
                if (sendok_kiri[0] === 'sakit') {
                    $('input[name="sendok_kiri"]').prop('checked', true);
                }
                if (sendok_kiri[1]) {
                    $('input[name="sendok_kiri_grade"][value="' + sendok_kiri[1] + '"]').prop('checked',
                        true);
                }

                // Handle sendok kanan
                var sendok_kanan = data.sendok_kanan ? data.sendok_kanan.split(',') : [];
                if (sendok_kanan[0] === 'sakit') {
                    $('input[name="sendok_kanan"]').prop('checked', true);
                }
                if (sendok_kanan[1]) {
                    $('input[name="sendok_kanan_grade"][value="' + sendok_kanan[1] + '"]').prop('checked',
                        true);
                }

                if (data.ereksi === 'ya') {
                    $('input[name="ereksi"][value="ya"]').prop('checked', true);
                    $('input[name="ereksi"][value="tidak"]').prop('checked', false);
                } else if (data.ereksi === 'tidak') {
                    $('input[name="ereksi"][value="tidak"]').prop('checked', true);
                    $('input[name="ereksi"][value="ya"]').prop('checked', false);
                } else {
                    $('input[name="ereksi"]').prop('checked', false);
                }

                if (data.obat_kuat === 'ya') {
                    $('input[name="obat_kuat"][value="ya"]').prop('checked', true);
                    $('input[name="obat_kuat"][value="tidak"]').prop('checked', false);
                } else if (data.obat_kuat === 'tidak') {
                    $('input[name="obat_kuat"][value="tidak"]').prop('checked', true);
                    $('input[name="obat_kuat"][value="ya"]').prop('checked', false);
                } else {
                    $('input[name="obat_kuat"]').prop('checked', false);
                }

                if (data.porno === 'ya') {
                    showFrequency('nonton-porn-frequency', true);
                    $('input[name="nonton_porno"][value="ya"]').prop('checked', true);
                    $('input[name="nonton_porno"][value="tidak"]').prop('checked', false);
                    $('input[name="frekuensi_nonton_porno"][value="' + data.frekuensi_porno + '"]').prop(
                        'checked', true);
                } else if (data.porno === 'tidak') {
                    $('input[name="nonton_porno"][value="tidak"]').prop('checked', true);
                    $('input[name="nonton_porno"][value="ya"]').prop('checked', false);
                    showFrequency('nonton-porn-frequency', false);
                } else {
                    $('input[name="nonton_porno"]').prop('checked', false);
                    showFrequency('nonton-porn-frequency', false);
                }

                if (data.onani === 'ya') {
                    showFrequency('onani-frequency', true);
                    $('input[name="sering_onani"][value="ya"]').prop('checked', true);
                    $('input[name="sering_onani"][value="tidak"]').prop('checked', false);
                    $('input[name="frekuensi_onani"][value="' + data.frekuensi_onani + '"]').prop('checked',
                        true);
                } else if (data.onani === 'tidak') {
                    $('input[name="sering_onani"][value="tidak"]').prop('checked', true);
                    $('input[name="sering_onani"][value="ya"]').prop('checked', false);
                    showFrequency('onani-frequency', false);
                } else {
                    $('input[name="sering_onani"]').prop('checked', false);
                    showFrequency('onani-frequency', false);
                }

                // Handling the input for "lainnya" in both sections
                if (data.frekuensi_porno === 'lainnya') {
                    toggleLainnyaTextbox('nonton-lainnya-textbox', true);
                    $('#nonton-lainnya-textbox').val(data.frekuensi_porno_lain);
                }

                if (data.frekuensi_onani === 'lainnya') {
                    toggleLainnyaTextbox('onani-lainnya-textbox', true);
                    $('#onani-lainnya-textbox').val(data.frekuensi_onani_lain);
                }

                if (data.frekuensi_ranjang === 'lainnya') {
                    toggleLainnyaTextbox('hubungan-lainnya-textbox', true);
                    $('#hubungan-lainnya-textbox').val(data.frekuensi_ranjang_lain);
                }

                $('#exampleModal form :input').prop('readonly', false);
                $('#exampleModal form :checkbox').prop('disabled', false);

                if (typeof complaintTagify !== 'undefined') {
                    complaintTagify.setReadonly(false);
                }
                if (typeof medhisTagify !== 'undefined') {
                    medhisTagify.setReadonly(false);
                }
                if (typeof resultTagify !== 'undefined') {
                    resultTagify.setReadonly(false);
                }

                $('.terapis').prop('disabled', false);

                $('#exampleModal #save-button').show();

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                alert('Terjadi kesalahan, silahkan coba lagi...');
            }
        });
    }

    var deleteId = null;

    function destroy(id) {
        deleteId = id; // Set the ID to delete
        $('#deleteModal').appendTo('body').modal('show'); // Show the modal
    }

    $(document).ready(function() {
        $('#confirmDeleteButton').off('click').on('click', function() {
            var $btn = $(this);

            if (deleteId !== null) {
                $.ajax({
                    url: '<?= site_url('history/destroy') ?>/' + deleteId,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                    },
                    beforeSend: function() {
                        // Matikan tombol agar user tidak klik berkali-kali
                        $btn.prop('disabled', true).text('Memproses...');
                    },
                    success: function(response) {
                        if (response.status) {
                            // Handle success
                            $('#deleteModal').modal('hide');

                            if ($.fn.DataTable.isDataTable('#table-2')) {
                                $('#table-2').DataTable().ajax.reload(null, false);
                            } else {
                                location.reload();
                            }

                        } else {
                            // Handle failure
                            alert('Data gagal dihapus: ' + (response.message || 'Error server'));
                        }
                    },
                    error: function() {
                        // Handle error
                        alert('Terjadi kesalahan');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Ya, Hapus');
                        deleteId = null;
                    }
                });

                // $('#deleteModal').modal('hide'); // Hide the modal
                // deleteId = null; // Clear the ID
            }
        });

    })

    var complaintTagify, medhisTagify, resultTagify;
    document.addEventListener('DOMContentLoaded', function() {
        var complaintTextarea = document.querySelector('textarea[name="complaint"]');
        if (complaintTextarea) {
            complaintTagify = new Tagify(complaintTextarea, {
                whitelist: []
            });
            var controllerC; // Untuk mengontrol fetch call dan bisa membatalkannya

            complaintTagify.on('input', function(e) {
                var value = e.detail.value; // Nilai input dari Tagify
                complaintTagify.whitelist = null; // Reset whitelist
                if (controllerC) controllerC.abort();
                controllerC = new AbortController();
                complaintTagify.loading(true);
                fetch("<?= site_url('complaint/get_tags') ?>?query=" + encodeURIComponent(value), {
                        signal: controllerC.signal
                    })
                    .then(res => res.json())
                    .then(function(list) {
                        complaintTagify.whitelist = list;
                        complaintTagify.loading(false).dropdown.show(value);
                    }).catch(err => complaintTagify.loading(false));
            });
        }


    });

    document.addEventListener('DOMContentLoaded', function() {
        var medhisTextarea = document.querySelector('textarea[name="medhis"]');
        if (medhisTextarea) {
            medhisTagify = new Tagify(medhisTextarea, {
                whitelist: []
            });

            var controllerM; // Untuk mengontrol fetch call dan bisa membatalkannya

            medhisTagify.on('input', onInput);

            function onInput(e) {
                var value = e.detail.value; // Nilai input dari Tagify
                medhisTagify.whitelist = null; // Reset whitelist
                if (controllerM) controllerM.abort();
                controllerM = new AbortController();
                medhisTagify.loading(true);
                fetch("<?= site_url('medis/get_tags') ?>?query=" + encodeURIComponent(value), {
                        signal: controllerM.signal
                    })
                    .then(res => res.json())
                    .then(function(list) {
                        medhisTagify.whitelist = list;
                        medhisTagify.loading(false).dropdown.show(value);
                    }).catch(err => medhisTagify.loading(false));
            }
        }


    });
    document.addEventListener('DOMContentLoaded', function() {
        var resultTextarea = document.querySelector('textarea[name="result"]');
        if (resultTextarea) {
            resultTagify = new Tagify(resultTextarea, {
                whitelist: []
            });
            var controllerR; // Untuk mengontrol fetch call dan bisa membatalkannya

            resultTagify.on('input', onInput);

            function onInput(e) {
                var value = e.detail.value; // Nilai input dari Tagify
                resultTagify.whitelist = null; // Reset whitelist

                // Membatalkan fetch sebelumnya jika ada
                if (controllerR) controllerR.abort();
                controllerR = new AbortController();

                resultTagify.loading(true);

                // Mengambil suggestions dari server menggunakan fetch
                fetch("<?= site_url('result/get_tags') ?>?query=" + encodeURIComponent(value), {
                        signal: controllerR.signal
                    })
                    .then(res => res.json())
                    .then(function(list) {
                        resultTagify.whitelist = list;
                        resultTagify.loading(false).dropdown.show(value);
                    }).catch(err => resultTagify.loading(false));
            }
        }

    });

    function checkGender() {
        var gender = document.getElementById('gender').value;
        var terapiKejantanan = document.getElementById('terapi-kejantanan');

        if (gender === 'Man') {
            terapiKejantanan.style.display = 'block';
        } else {
            terapiKejantanan.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        checkGender();
    });

    document.getElementById('gender').addEventListener('change', checkGender);

    document.addEventListener('DOMContentLoaded', function() {
        toggleTerapiForm();
    });

    function toggleTerapiForm() {
        var checkbox = document.getElementById("kejantanan");
        var terapiForm = document.getElementById("terapi-form");
        var pemeriksaan = document.getElementById("pemeriksaan");

        if (checkbox.checked) {
            terapiForm.style.display = "block";
            pemeriksaan.style.display = "block";
        } else {
            terapiForm.style.display = "none";
            pemeriksaan.style.display = "none";
        }
    }

    function showFrequency(elementId, show) {
        const element = document.getElementById(elementId);
        if (show) {
            element.style.display = 'block';
        } else {
            element.style.display = 'none';
            const radios = element.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => (radio.checked = false));
            const textbox = element.querySelector('input[type="text"]');
            if (textbox) {
                textbox.value = '';
                textbox.style.display = 'none';
            }
        }
    }

    function toggleLainnyaTextbox(textboxId, show, retainValue = false) {
        const textbox = document.getElementById(textboxId);
        if (textbox) {
            if (show) {
                textbox.style.display = 'block';
            } else {
                textbox.style.display = 'none';
                if (!retainValue) {
                    textbox.value = '';
                }
            }
        }
    }

    document.addEventListener('change', function(e) {
        if (e.target.name === 'frekuensi_nonton_porno' && e.target.value !== 'lainnya') {
            toggleLainnyaTextbox('nonton-lainnya-textbox', false, true);
        } else if (e.target.name === 'frekuensi_nonton_porno' && e.target.value === 'lainnya') {
            toggleLainnyaTextbox('nonton-lainnya-textbox', true);
        }

        if (e.target.name === 'frekuensi_onani' && e.target.value !== 'lainnya') {
            toggleLainnyaTextbox('onani-lainnya-textbox', false, true);
        } else if (e.target.name === 'frekuensi_onani' && e.target.value === 'lainnya') {
            toggleLainnyaTextbox('onani-lainnya-textbox', true);
        }

        if (e.target.name === 'frekuensi_ranjang' && e.target.value !== 'lainnya') {
            toggleLainnyaTextbox('hubungan-lainnya-textbox', false, true);
        } else if (e.target.name === 'frekuensi_ranjang' && e.target.value === 'lainnya') {
            toggleLainnyaTextbox('hubungan-lainnya-textbox', true);
        }
    });

    $(document).ready(function() {
        function toggleGradeCheckbox(areaPrefix) {
            var sakitCheckboxKanan = $('input[name="' + areaPrefix + '_kanan"]');
            var sakitCheckboxKiri = $('input[name="' + areaPrefix + '_kiri"]');
        }

        function limitOneGrade(areaPrefix) {
            $('input[name="' + areaPrefix + '_kanan_grade"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('input[name="' + areaPrefix + '_kanan_grade"]').not(this).prop('checked', false);
                }
            });

            $('input[name="' + areaPrefix + '_kiri_grade"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('input[name="' + areaPrefix + '_kiri_grade"]').not(this).prop('checked', false);
                }
            });
        }

        var areas = ['odp', 'vital', 'kelenjar', 'hormon', 'tk', 'fd', 'lp_atas', 'lp_bawah', 'lp_kanan',
            'lp_kiri', 'cv4', 'cv6', 'l1', 'l3', 'piriformis', 'sendok'
        ];

        areas.forEach(function(area) {
            toggleGradeCheckbox(area);
            limitOneGrade(area);

            $('input[name="' + area + '_kanan"], input[name="' + area + '_kiri"]').on('change',
                function() {
                    toggleGradeCheckbox(area);
                });
        });
    });

    // Auto open modal when query param ?openModalRiwayat=true
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openModalRiwayat') === 'true') {
            const hId = urlParams.get('history_id');

            if (hId && hId !== 'undefined' && hId !== '') {
                setTimeout(function() {
                    show(hId); 

                    $('.modal-backdrop').not(':last').remove();
                    $('.modal').appendTo("body");
                }, 500);
            } else {
                console.warn("History ID tidak ditemukan di URL. Modal tidak otomatis dibuka.");
            }
        }
    });
</script>
<?= $this->endSection() ?>