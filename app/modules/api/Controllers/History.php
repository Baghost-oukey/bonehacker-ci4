<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use App\modules\history\Models\MHistory;
use CodeIgniter\API\ResponseTrait;

class History extends BaseController
{
    use ResponseTrait;

    protected $historyModel;
    protected $db;

    public function __construct()
    {
        $this->historyModel = new MHistory();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get Medical History for a Patient
     * GET /api/history/{patient_id}
     */
    public function index($patientId = null)
    {
        if (!$patientId) return $this->fail('Patient ID required');

        $histories = $this->historyModel->getListData($patientId, [
            'limit' => 20,
            'offset' => 0,
            'order' => 'date',
            'mode' => 'DESC'
        ]);

        return $this->respond([
            'status' => 'success',
            'data' => $histories
        ]);
    }

    /**
     * Get Tags for Form (Complaints, Medhis, Results)
     * GET /api/history/tags
     */
    public function tags()
    {
        $complaints = $this->db->table('complaint_tags')->select('id, name')->get()->getResult();
        $medhis = $this->db->table('medhis_tags')->select('id, name')->get()->getResult();
        $results = $this->db->table('result_tags')->select('id, name')->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'data' => [
                'complaints' => $complaints,
                'medhis' => $medhis,
                'results' => $results
            ]
        ]);
    }

    /**
     * Save New Medical Record
     * POST /api/history/save
     */
    public function save()
    {
        $data = $this->request->getPost();
        
        // Basic Validation
        if (empty($data['patient_id'])) return $this->fail('Patient ID required');

        $saveData = [
            'patient_id' => $data['patient_id'],
            'patient_queue_id' => $data['queue_id'] ?? null,
            'terapis_id' => $data['terapis_id'] ?? null,
            'complaint' => $data['complaint'] ?? '',
            'medhis' => $data['medhis'] ?? '',
            'checkup' => $data['checkup'] ?? '',
            'results' => $data['results'] ?? '',
            'measure' => $data['measure'] ?? '',
            'tensi' => $data['tensi'] ?? '',
            'power' => $data['power'] ?? '',
            'pr' => $data['pr'] ?? '',
            'cervical' => $data['cervical'] ?? '',
            'thoraxal' => $data['thoraxal'] ?? '',
            'lumbar' => $data['lumbar'] ?? '',
            'sacral' => $data['sacral'] ?? '',
            'sacrum' => $data['sacrum'] ?? '',
            'pelvis' => $data['pelvis'] ?? '',
            'other' => $data['other'] ?? '',
            'date' => date('Y-m-d'),
            'process_at' => date('Y-m-d H:i:s'),
            'finish_at' => date('Y-m-d H:i:s'),
            'created_by' => $data['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'history_region' => $data['region_id'] ?? null,
        ];

        if ($this->historyModel->insert($saveData)) {
            // Also update queue status if queue_id is present
            if (!empty($data['queue_id'])) {
                $this->db->table('patient_queues')
                    ->where('id', $data['queue_id'])
                    ->update(['finish_at' => date('Y-m-d H:i:s')]);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Rekam medis berhasil disimpan',
                'id' => $this->historyModel->getInsertID()
            ]);
        }

        return $this->fail('Gagal menyimpan rekam medis');
    }
}
