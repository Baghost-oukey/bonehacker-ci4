<?php

namespace App\modules\history\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;

class History extends BaseController
{
    use ResponseTrait;
    protected $model_history;
    protected $model_patient;
    protected $model_whatsapp;
    protected $model_logwa;
    protected $db;

    public function __construct()
    {
        $this->model_history = new \App\modules\history\Models\MHistory();
        $this->model_patient = new \App\modules\patients\Models\MPatients();
        $this->model_whatsapp = new \App\modules\whatsapp\Models\MWhatsapp();
        $this->model_logwa = new \App\modules\log_whatsapp\Models\MLogWhatsapp();
        $this->db = \Config\Database::connect();
    }


    public function fetch($id)
    {
        $requestData = $this->request->getPost();
        $orderIndex  = $requestData['order'][0]['column'] ?? 0;
        $orderDir    = $requestData['order'][0]['dir'] ?? 'desc';
        $orderField  = $requestData['columns'][$orderIndex]['data'] ?? 'id';

        if ($orderField === 'no' || $orderField === 'action') {
            $orderField = 'id';
        }
        $options = [
            'order'  => $orderField,
            'mode'   => $orderDir,
            'offset' => $this->request->getPost('start') ?? 0,
            'limit'  => $this->request->getPost('length') ?? 10,
        ];

        if (!empty($requestData['search']['value'])) {
            $searchValue = $requestData['search']['value'];
            $options['where_like'] = [
                "ct.name LIKE '%$searchValue%'",
                "mt.name LIKE '%$searchValue%'"
            ];
        }
        $dataOutput    = $this->model_history->getListData($id, $options);
        $totalFiltered = $this->model_history->getTotalData($id, $options);
        $totalData     = $this->model_history->countAllResults(); // Total tanpa filter
        $no = $options['offset'] + 1;
        foreach ($dataOutput as $value) {
            $value->no = $no++;
            $value->date = $value->date ? date('d-m-Y', strtotime($value->date)) : '-';
            $value->complaint = !empty($value->complaint_names) ? $value->complaint_names : '-';
            $value->medhis    = !empty($value->medhis_names) ? $value->medhis_names : '-';
            $value->duration = isset($value->time_consume) ? $value->time_consume . ' mnt' : '-';
            $value->action = '
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="show(\'' . $value->id . '\')"><i class="fas fa-eye"></i></button>
                    <button type="button" class="btn btn-success btn-sm" onclick="duplicate(\'' . $value->id . '\')"><i class="fas fa-copy"></i></button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="destroy(\'' . $value->id . '\')"><i class="fas fa-trash"></i></button>
                </div>';
        }
        return $this->response->setJSON([
            "draw"            => intval($this->request->getPost('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $dataOutput
        ]);
    }


    private function penggabungan($value, $grade)
    {
        $result = [];
        if (!empty($value)) {
            $result[] = $value;
        }
        if (!empty($grade)) {
            $result[] = is_array($grade) ? implode(',', $grade) : $grade;
        }
        return !empty($result) ? implode(',', $result) : "";
    }


    private function processTags($inputTags, $tableName)
    {
        log_message('debug', 'Tagify Input untuk ' . $tableName . ': ' . $inputTags);
        if (empty($inputTags) || $inputTags == '[]' || $inputTags == 'null') return "-";
        $tags = json_decode($inputTags, true);
        if (!is_array($tags)) {
            $tagName = explode(',', trim($inputTags, "[]\" "));
        } else {
            $tagName = array_column($tags, 'value');
        }
        $tagIds = [];
        foreach ($tagName as $name) {
            $name = trim($name);
            if ($name === '' || $name === '-') continue;

            $existing = $this->db->table($tableName)->where('name', $name)->get()->getRow();
            if ($existing) {
                $tagIds[] = $existing->id;
            } else {
                $this->db->table($tableName)->insert(['name' => $name]);
                $tagIds[] = $this->db->insertID();
            }
        }
        return !empty($tagIds) ? implode(',', $tagIds) : '-';
    }






    public function store()
    {
        $patientId = $this->request->getPost('patient_id');
        $queueId   = $this->request->getPost('queue_id');
        $type      = $this->request->getPost('type') ?? 'posted';

        // The patient_queues table does not have is_stored_history. Queue status is determined by histories.process_at and histories.finish_at.

        // Validation for processAt and finishAt
        $processAt = $this->request->getPost('processAt');
        $finishAt  = $this->request->getPost('finishAt');
        if ($processAt && $finishAt && strtotime($finishAt) < strtotime($processAt)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Waktu selesai tidak boleh lebih awal dari waktu mulai.'
            ]);
        }

        // Validation for Terapis (must not be empty if posted)
        $terapis = $this->request->getPost('terapis');
        if ($type === 'posted' && (empty($terapis) || !is_array($terapis) || empty($terapis[0]))) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Terapis harus dipilih sebelum menyimpan data riwayat secara permanen. Jika belum ada terapis, simpan sebagai draft atau perbarui data terapis.'
            ]);
        }

        $complaintValues = $this->processTags($this->request->getPost('complaint'), 'complaint_tags');
        $medhisValues    = $this->processTags($this->request->getPost('medhis'), 'medhis_tags');
        $resultValues    = $this->processTags($this->request->getPost('results'), 'result_tags');
        $data = $this->mapHistoryData($patientId, $complaintValues, $medhisValues, $resultValues);

        $data['tensi']      = $this->request->getPost('tensi') ?? "";
        $data['cervical']   = $this->request->getPost('cervical') ?? "";
        $data['thoraxal']   = $this->request->getPost('thoraxal') ?? "";
        $data['lumbar']     = $this->request->getPost('lumbar') ?? "";

        if (isset($data['phone']) && empty($data['phone'])) {
            $data['phone'] = $data['phone'] ?: '-';
        }

        $kejantananData = $this->mapKejantananData();
        $statusKejantanan = $this->request->getPost('kejantanan') === 'ya' ? 'ya' : 'tidak';

        if ($this->model_history->insert($data)) {
            $historyId = $this->model_history->getInsertID();
            unset($data['phone']);
            $this->model_history->updateKejantanan($historyId, $kejantananData, $statusKejantanan);

            if ($type === 'posted') {
                // Auto-insert ke tabel jasa_pelayanan
                $this->insertJasaPelayanan($historyId, $patientId, $statusKejantanan);

                $patient = $this->model_patient->find($patientId);
                $whatsappData = $this->model_whatsapp->getMessageAndCredentials();
                if ($this->request->getPost('notifikasi') && $whatsappData && $patient) {
                    $rawPhone = $patient->phone ?? "-";
                    $phone = (strpos($rawPhone, '0') === 0) ? '62' . substr($rawPhone, 1) : $rawPhone;
                    $this->sendAndLogWhatsApp($historyId, $patient->name, $phone, $whatsappData);
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => $type === 'draft' ? 'Data riwayat berhasil disimpan sebagai draft' : 'Data riwayat berhasil disimpan'
            ]);
        } else {

            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data riwayat gagal disimpan. Silakan periksa kembali inputan Anda atau hubungi admin.'
            ]);
        }
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $patientId = $this->request->getPost('patient_id');
        $queueId   = $this->request->getPost('queue_id');
        $type      = $this->request->getPost('type') ?? 'posted';

        $processAt = $this->request->getPost('processAt');
        $finishAt  = $this->request->getPost('finishAt');

        if ($processAt && $finishAt && strtotime($finishAt) < strtotime($processAt)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Waktu selesai tidak boleh lebih awal dari waktu mulai.'
            ]);
        }

        // Validation for Terapis (must not be empty if posted)
        $terapis = $this->request->getPost('terapis');
        if ($type === 'posted' && (empty($terapis) || !is_array($terapis) || empty($terapis[0]))) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Terapis harus dipilih sebelum menyimpan data riwayat secara permanen. Jika belum ada terapis, simpan sebagai draft atau perbarui data terapis.'
            ]);
        }

        $complaintValues = $this->processTags($this->request->getPost('complaint'), 'complaint_tags');
        $medhisValues    = $this->processTags($this->request->getPost('medhis'), 'medhis_tags');
        $resultValues    = $this->processTags($this->request->getPost('results'), 'result_tags');

        $data = $this->mapHistoryData($patientId, $complaintValues, $medhisValues, $resultValues);
        unset($data['created_by']);

        $data['updated_by'] = session()->get('userId');
        $data['date_modified'] = date('Y-m-d H:i:s');

        $statusKejantanan = $this->request->getPost('kejantanan') === 'ya' ? 'ya' : 'tidak';
        $kejantananData = $this->mapKejantananData();

        if ($this->model_history->update($id, $data)) {
            $this->model_history->updateKejantanan($id, $kejantananData, $statusKejantanan);

            if ($type === 'posted') {

                // Auto-update/insert ke tabel jasa_pelayanan
                $this->upsertJasaPelayanan($id, $patientId, $statusKejantanan);

                // WhatsApp Notification if requested and not already sent for this history
                $patient = $this->model_patient->find($patientId);
                $whatsappData = $this->model_whatsapp->getMessageAndCredentials();
                $alreadySent = $this->model_logwa->where('history_id', $id)->where('is_sent', 1)->first();

                if ($this->request->getPost('notifikasi') && $whatsappData && $patient && !$alreadySent) {
                    $rawPhone = $patient->phone ?? "-";
                    $phone = (strpos($rawPhone, '0') === 0) ? '62' . substr($rawPhone, 1) : $rawPhone;
                    $this->sendAndLogWhatsApp($id, $patient->name, $phone, $whatsappData);
                }
            }

            return $this->response->setJSON(['status' => true, 'message' => $type === 'draft' ? 'Draft riwayat berhasil diperbarui' : 'Data pasien berhasil diperbarui']);
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Data pasien gagal diperbarui']);
        }
    }


    private function getRealname($userId)
    {
        if (empty($userId)) return '-';
        $user = $this->db->table('users')
            ->select('realname')
            ->where('id', $userId)
            ->get()
            ->getRow();
        return $user ? $user->realname : '-';
    }


    public function show($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(site_url('history'));
        }
        $data = $this->model_history->find($id);
        if (!$data) {
            return $this->response->setJSON(['message' => 'Data tidak ditemukan'])->setStatusCode(404);
        }
        $data->active_terapis = $this->model_history->getActiveTerapis();
        $data->selected_terapis = $this->model_history->getSelectedTerapis($id);
        $kejantananData = $this->model_history->getKejantananById($id);
        if ($kejantananData) {
            foreach ($kejantananData as $key => $value) {
                if ($key !== 'id') {
                    $data->$key = $value;
                }
            }
        }
        $data->complaint = $this->_getTagNameFromIds($data->complaint, 'complaint_tags');
        $data->medhis    = $this->_getTagNameFromIds($data->medhis, 'medhis_tags');
        $data->results   = $this->_getTagNameFromIds($data->results, 'result_tags');
        $data->history_created_by = !empty($data->created_by) ? $this->getRealname($data->created_by) : '-';
        $data->history_updated_by = !empty($data->updated_by) ? $this->getRealname($data->updated_by) : '-';
        
        // Ensure camelCase for JS population
        $data->processAt = $data->process_at;
        $data->finishAt = $data->finish_at;
        $data->timeConsume = $data->time_consume;

        if (!empty($data->finish_at) && !empty($data->process_at)) {
            $diff = strtotime($data->finish_at) - strtotime($data->process_at);
            $data->time_consume_formatted = round($diff / 60, 0) . ' menit';
        } else {
            $data->time_consume_formatted = null;
        }
        return $this->response->setJSON($data);
    }


    private function _getTagNameFromIds($ids, $table)
    {
        if (empty($ids) || $ids === '-') return '-';
        $idArray = explode(',', $ids);
        $db = \Config\Database::connect();
        $builder = $db->table($table);
        $tags = $builder->whereIn('id', $idArray)->get()->getResultArray();
        if (empty($tags)) return '-';
        $tagNames = array_column($tags, 'name');
        return implode(', ', $tagNames);
    }


    private function mapHistoryData($patientId, $complaint, $medhis, $results)
    {
        $processAt = $this->request->getPost('processAt') ? str_replace('T', ' ', $this->request->getPost('processAt')) : null;
        $finishAt = $this->request->getPost('finishAt') ? str_replace('T', ' ', $this->request->getPost('finishAt')) : null;
        $timeConsume = $this->request->getPost('timeConsume');

        // Auto-calculate duration if missing but start/finish are present
        if (empty($timeConsume) && $processAt && $finishAt) {
            try {
                $start = new \DateTime($processAt);
                $end = new \DateTime($finishAt);
                if ($end > $start) {
                    $diff = $start->diff($end);
                    $timeConsume = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                } else {
                    $timeConsume = 0;
                }
            } catch (\Exception $e) {
                $timeConsume = null;
            }
        }

        $mapped = [
            'patient_id'            => $patientId,
            'terapis_id'            => is_array($this->request->getPost('terapis')) ? implode(',', $this->request->getPost('terapis')) : "",

            'history_region'        => $this->request->getPost('history_region') ?: null,
            'complaint'             => $complaint ?? "-",
            'medhis'                => $medhis ?? "-",
            'results'               => $results ?? "-",
            'checkup'               => $this->request->getPost('cekup') ?? "",
            'cervical'              => $this->request->getPost('cervical') ?? "",
            'thoraxal'              => $this->request->getPost('thoraxal') ?? "",
            'lumbar'                => $this->request->getPost('lumbar') ?? "",
            'sacral'                => $this->request->getPost('sacral') ?? "",
            'sacrum'                => $this->request->getPost('sacrum') ?? "",
            'pelvis'                => $this->request->getPost('pelvis') ?? "",
            'other'                 => $this->request->getPost('other') ?? "",
            'measure'               => $this->request->getPost('measure') ?? "",
            'tensi'                 => $this->request->getPost('tensi') ?? "",
            'power'                 => $this->request->getPost('power') ?? "",
            'pr'                    => $this->request->getPost('pr') ?? "",
            'keterangan_verteba'    => $this->request->getPost('ket_vertebrata') ?? "",
            'keterangan_thorax'     => $this->request->getPost('ket_thorax') ?? "",
            'keterangan_kompresi'   => $this->request->getPost('ket_kompresi') ?? "",
            'keterangan_plintiran'  => $this->request->getPost('ket_plintiran') ?? "",
            'keterangan_visualfoot' => $this->request->getPost('ket_viska') ?? "",

            'plintiran'             => is_array($this->request->getPost('plintiran')) ? implode(',', $this->request->getPost('plintiran')) : "",
            'kompresi'              => is_array($this->request->getPost('kompresi')) ? implode(',', $this->request->getPost('kompresi')) : "",
            'verteba'               => is_array($this->request->getPost('vertebra')) ? implode(',', $this->request->getPost('vertebra')) : "",
            'thorax'                => is_array($this->request->getPost('thorax')) ? implode(',', $this->request->getPost('thorax')) : "",
            'visualfoot'            => is_array($this->request->getPost('visual_kaki')) ? implode(',', $this->request->getPost('visual_kaki')) : "",
            'pubis'                 => is_array($this->request->getPost('pubis')) ? implode(',', $this->request->getPost('pubis')) : "",


            'date'                  => $this->request->getPost('date') ? $this->request->getPost('date') . ' ' . date('H:i:s') : date('Y-m-d H:i:s'),
            'process_at'            => $processAt,
            'finish_at'             => $finishAt,
            'time_consume'          => $timeConsume ?: null,
            'kejantanan'            => $this->request->getPost('kejantanan') === 'ya' ? 'ya' : 'tidak',
            'type'                  => $this->request->getPost('type') ?? 'posted',
            'created_by'            => session()->get('userId'),
        ];
        
        $queueId = $this->request->getPost('queue_id');
        if (!empty($queueId)) {
            $mapped['patient_queue_id'] = $queueId;
        }

        return $mapped;
    }


    private function mapKejantananData()
    {
        return [
            'ereksi'                 => $this->request->getPost('ereksi') ?? "",
            'porno'                  => $this->request->getPost('nonton_porno') ?? "",
            'frekuensi_porno'        => $this->request->getPost('frekuensi_nonton_porno') ?? "",
            'frekuensi_porno_lain'   => $this->request->getPost('frekuensi_nonton_lainnya') ?: "",
            'onani'                  => $this->request->getPost('sering_onani') ?? "",
            'frekuensi_onani'        => $this->request->getPost('frekuensi_onani') ?? "",
            'frekuensi_onani_lain'   => $this->request->getPost('frekuensi_onani_lainnya') ?: "",
            'ranjang'                => $this->request->getPost('ranjang') ?? "",
            'frekuensi_ranjang'      => $this->request->getPost('frekuensi_ranjang') ?? "",
            'frekuensi_ranjang_lain' => $this->request->getPost('frekuensi_ranjang_lainnya') ?: "",
            'obat_kuat'              => $this->request->getPost('obat_kuat') ?? "",
            'penyebab'               => $this->request->getPost('penyebab') ?? "",
            'otot_dada_perut_kanan' => $this->penggabungan($this->request->getPost('odp_kanan'), $this->request->getPost('odp_kanan_grade')),
            'otot_dada_perut_kiri'  => $this->penggabungan($this->request->getPost('odp_kiri'), $this->request->getPost('odp_kiri_grade')),
            'vital_kanan'           => $this->penggabungan($this->request->getPost('vital_kanan'), $this->request->getPost('vital_kanan_grade')),
            'vital_kiri'            => $this->penggabungan($this->request->getPost('vital_kiri'), $this->request->getPost('vital_kiri_grade')),
            'kelenjar_kanan'        => $this->penggabungan($this->request->getPost('kelenjar_kanan'), $this->request->getPost('kelenjar_kanan_grade')),
            'kelenjar_kiri'         => $this->penggabungan($this->request->getPost('kelenjar_kiri'), $this->request->getPost('kelenjar_kiri_grade')),
            'hormon_kanan'          => $this->penggabungan($this->request->getPost('hormon_kanan'), $this->request->getPost('hormon_kanan_grade')),
            'hormon_kiri'           => $this->penggabungan($this->request->getPost('hormon_kiri'), $this->request->getPost('hormon_kiri_grade')),
            'tulang_kering_kanan'   => $this->penggabungan($this->request->getPost('tk_kanan'), $this->request->getPost('tk_kanan_grade')),
            'tulang_kering_kiri'    => $this->penggabungan($this->request->getPost('tk_kiri'), $this->request->getPost('tk_kiri_grade')),
            'femur_dalam_kanan'     => $this->penggabungan($this->request->getPost('fd_kanan'), $this->request->getPost('fd_kanan_grade')),
            'femur_dalam_kiri'      => $this->penggabungan($this->request->getPost('fd_kiri'), $this->request->getPost('fd_kiri_grade')),
            'lingkar_perut_atas'    => $this->penggabungan($this->request->getPost('lp_atas'), $this->request->getPost('lp_atas_grade')),
            'lingkar_perut_bawah'   => $this->penggabungan($this->request->getPost('lp_bawah'), $this->request->getPost('lp_bawah_grade')),
            'lingkar_perut_kanan'   => $this->penggabungan($this->request->getPost('lp_kanan'), $this->request->getPost('lp_kanan_grade')),
            'lingkar_perut_kiri'    => $this->penggabungan($this->request->getPost('lp_kiri'), $this->request->getPost('lp_kiri_grade')),
            'cv4_kanan'             => $this->penggabungan($this->request->getPost('cv4_kanan'), $this->request->getPost('cv4_kanan_grade')),
            'cv4_kiri'              => $this->penggabungan($this->request->getPost('cv4_kiri'), $this->request->getPost('cv4_kiri_grade')),
            'cv6_kanan'             => $this->penggabungan($this->request->getPost('cv6_kanan'), $this->request->getPost('cv6_kanan_grade')),
            'cv6_kiri'              => $this->penggabungan($this->request->getPost('cv6_kiri'), $this->request->getPost('cv6_kiri_grade')),
            'l1_kanan'              => $this->penggabungan($this->request->getPost('l1_kanan'), $this->request->getPost('l1_kanan_grade')),
            'l1_kiri'               => $this->penggabungan($this->request->getPost('l1_kiri'), $this->request->getPost('l1_kiri_grade')),
            'l3_kanan'              => $this->penggabungan($this->request->getPost('l3_kanan'), $this->request->getPost('l3_kanan_grade')),
            'l3_kiri'               => $this->penggabungan($this->request->getPost('l3_kiri'), $this->request->getPost('l3_kiri_grade')),
            'piriformis_kanan'      => $this->penggabungan($this->request->getPost('piriformis_kanan'), $this->request->getPost('piriformis_kanan_grade')),
            'piriformis_kiri'       => $this->penggabungan($this->request->getPost('piriformis_kiri'), $this->request->getPost('piriformis_kiri_grade')),
            'sendok_kanan'          => $this->penggabungan($this->request->getPost('sendok_kanan'), $this->request->getPost('sendok_kanan_grade')),
            'sendok_kiri'           => $this->penggabungan($this->request->getPost('sendok_kiri'), $this->request->getPost('sendok_kiri_grade')),
        ];
    }


    private function sendWhatsAppMessage($phone, $message, $instance_id, $token)
    {
        $url = "https://app.meoblaster.com/api/send?number={$phone}&type=text&message=" . urlencode($message) . "&instance_id={$instance_id}&access_token={$token}";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET', // Use GET since parameters are in the URL
        ));
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);
        log_message('debug', 'MeoBlaster Response: ' . $response);
        log_message('debug', 'HTTP Code: ' . $http_code);
        log_message('debug', 'cURL Error: ' . $curl_error);
        return ($http_code === 200 && !$curl_error) ? $response : false;
    }


    private function sendAndLogWhatsApp($historyId, $name, $phone, $whatsappData)
    {
        $currentDatetime = date('Y-m-d H:i:s');
        $response = $this->sendWhatsAppMessage(
            $phone,
            $whatsappData->message,
            $whatsappData->instance_id,
            $whatsappData->token
        );
        $resArr = json_decode($response, true);
        $isSent = (isset($resArr['status']) && $resArr['status'] === 'success') ? 1 : 0;
        $logData = [
            'history_id' => $historyId,
            'name'       => $name,
            'phone'      => $phone,
            'message'    => $whatsappData->message,
            'is_sent'    => $isSent,
            'time_sent'  => $isSent ? $currentDatetime : null,
            'created_at' => $currentDatetime,
            'updated_at' => $currentDatetime
        ];
        return $this->model_logwa->insert($logData);
    }


    public function copy()
    {
        $id = $this->request->getPost('id');
        $patientId = $this->request->getPost('patient_id');
        $patient = $this->model_patient->getById($patientId);
        $phone   = $patient ? $patient->phone : null;
        $name    = $patient ? $patient->name : null;
        if ($phone && strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }
        $whatsappData = $this->model_whatsapp->getMessageAndCredentials();
        $complaintValues = $this->processTags($this->request->getPost('complaint'), 'complaint_tags');
        $medhisValues    = $this->processTags($this->request->getPost('medhis'), 'medhis_tags');
        $resultValues    = $this->processTags($this->request->getPost('results'), 'result_tags');
        $existingData = $this->model_history->getById($id);

        if ($existingData) {
            $data = $this->mapHistoryData($patientId, $complaintValues, $medhisValues, $resultValues);
            $dateInput = $this->request->getPost('date');
            $data['date'] = !empty($dateInput) ? $dateInput . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
            $data['created_by'] = session()->get('userId');

            $statusKejantanan = $this->request->getPost('kejantanan') === 'ya' ? 'ya' : 'tidak';
            $kejantananData = $this->mapKejantananData();

            if ($this->model_history->insert($data)) {
                $newId = $this->model_history->getInsertID();
                $this->model_history->updateKejantanan($newId, $kejantananData, $statusKejantanan);

                if ($this->request->getPost('notifikasi') && $whatsappData) {
                    $this->sendAndLogWhatsApp($newId, $name, $phone, $whatsappData);
                }
                session()->setFlashdata('message', ['success', 'Data riwayat berhasil disalin']);
            } else {
                session()->setFlashdata('message', ['danger', 'Data riwayat gagal disalin']);
            }
        } else {
            session()->setFlashdata('message', ['danger', 'Data lama tidak ditemukan']);
        }
        return redirect()->to('patient/show/' . $patientId);
    }


    public function destroy($id)
    {
        $destroy = $this->model_history->delete($id);
        if ($destroy) {
            // Soft delete jasa_pelayanan yang terkait
            $this->db->table('jasa_pelayanan')
                ->where('history_id', $id)
                ->update(['is_delete' => 1]);

            session()->setFlashdata('message', ['success', 'Data riwayat berhasil dihapus']);
            $response = ["status" => true];
        } else {
            session()->setFlashdata('message', ['danger', 'Data riwayat gagal dihapus']);
            $response = ["status" => false];
        }
        return $this->response->setJSON($response);
    }


    /**
     * Auto-insert record jasa_pelayanan saat history baru disimpan
     */
    private function insertJasaPelayanan($historyId, $patientId, $statusKejantanan)
    {
        $terapisPost = $this->request->getPost('terapis');
        $terapisId = null;
        if (is_array($terapisPost) && !empty($terapisPost)) {
            $terapisId = $terapisPost[0];
        }

        $dateInput = $this->request->getPost('date');
        $tanggalLayanan = !empty($dateInput) ? $dateInput : date('Y-m-d');

        $this->db->table('jasa_pelayanan')->insert([
            'history_id'       => $historyId,
            'patient_id'       => $patientId,
            'terapis_id'       => $terapisId,
            'kategori_layanan' => ($statusKejantanan === 'ya') ? 'Kejantanan' : 'Reguler',
            'tanggal_layanan'  => $tanggalLayanan,
            'is_delete'        => 0,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
    }


    /**
     * Auto-update/insert record jasa_pelayanan saat history diupdate
     */
    private function upsertJasaPelayanan($historyId, $patientId, $statusKejantanan)
    {
        $terapisPost = $this->request->getPost('terapis');
        $terapisId = null;
        if (is_array($terapisPost) && !empty($terapisPost)) {
            $terapisId = $terapisPost[0];
        }

        $existing = $this->db->table('jasa_pelayanan')
            ->where('history_id', $historyId)
            ->get()
            ->getRow();

        $jpData = [
            'patient_id'       => $patientId,
            'terapis_id'       => $terapisId,
            'kategori_layanan' => ($statusKejantanan === 'ya') ? 'Kejantanan' : 'Reguler',
        ];

        if ($existing) {
            $this->db->table('jasa_pelayanan')
                ->where('id', $existing->id)
                ->update($jpData);
        } else {
            $dateInput = $this->request->getPost('date');
            $jpData['history_id']      = $historyId;
            $jpData['tanggal_layanan'] = !empty($dateInput) ? $dateInput : date('Y-m-d');
            $jpData['is_delete']       = 0;
            $jpData['created_at']      = date('Y-m-d H:i:s');
            $this->db->table('jasa_pelayanan')->insert($jpData);
        }
    }

    /**
     * Get terapis by region - untuk filter dropdown terapis
     */
    public function getTerapisByRegion()
    {
        $regionId = $this->request->getGet('region_id') ?? $this->request->getPost('region_id');
        
        if (empty($regionId)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Region ID is required',
                'data' => []
            ]);
        }

        $terapis = $this->db->table('terapis')
            ->select('id, nama, is_active')
            ->where('region_id', $regionId)
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON([
            'status' => true,
            'data' => $terapis
        ]);
    }
}
