<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use App\Modules\history\Models\MHistory;
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
     * Get Tag Names from IDs (Helper)
     */
    private function getTagNames($ids, $table)
    {
        if (empty($ids) || $ids === '-') return '-';
        
        // If it's already names (contains letters), return as is (for legacy data)
        if (preg_match('/[a-zA-Z]/', $ids)) {
            return $ids;
        }

        $idArray = explode(',', $ids);
        $tags = $this->db->table($table)->whereIn('id', $idArray)->get()->getResultArray();
        if (empty($tags)) return $ids; // Fallback to original value if no IDs found
        return implode(', ', array_column($tags, 'name'));
    }

    /**
     * Get Medical History for a Patient
     * GET /api/medical-records/patient/{patient_id}
     */
    public function index($patientId = null)
    {
        if (!$patientId) return $this->fail('Patient ID required');

        $histories = $this->historyModel->where('patient_id', $patientId)
            ->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        // Convert IDs to Names for Mobile Display
        foreach ($histories as &$h) {
            $h->complaint_names = $this->getTagNames($h->complaint, 'complaint_tags');
            $h->medhis_names = $this->getTagNames($h->medhis, 'medhis_tags');
            $h->results_names = $this->getTagNames($h->results, 'result_tags');
            
            // Map for mobile app compatibility
            $h->complaint = $h->complaint_names;
            $h->medhis = $h->medhis_names;
            $h->results = $h->results_names;
        }

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
        $terapis = $this->db->table('terapis')->select('id, nama AS name')->where('is_active', 1)->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'data' => [
                'complaints' => $complaints,
                'medhis' => $medhis,
                'results' => $results,
                'terapis' => $terapis
            ]
        ]);
    }

    /**
     * Process Tags (Complaint, Medhis, Results)
     * Converts names to IDs, creating new tags if they don't exist
     */
    private function processTags($inputTags, $tableName)
    {
        if (empty($inputTags)) return '-';
        
        $tagName = explode(',', $inputTags);
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

    /**
     * Process Therapist Names to IDs
     */
    private function processTherapists($names)
    {
        if (empty($names)) return null;
        $nameList = explode(',', $names);
        $ids = [];
        foreach ($nameList as $name) {
            $name = trim($name);
            $therapist = $this->db->table('terapis')->where('name', $name)->get()->getRow();
            if ($therapist) {
                $ids[] = $therapist->id;
            }
        }
        return !empty($ids) ? implode(',', $ids) : null;
    }

    public function save()
    {
        try {
            $data = $this->request->getJSON(true);
            if (empty($data)) {
                $data = $this->request->getPost();
            }
            
            if (empty($data['patient_id'])) return $this->fail('Patient ID required');

            $complaintIds = $this->processTags($data['complaint'] ?? '', 'complaint_tags');
            $medhisIds = $this->processTags($data['medhis'] ?? '', 'medhis_tags');
            $resultIds = $this->processTags($data['results'] ?? '', 'result_tags');
            $terapisIds = $this->processTherapists($data['terapis_id'] ?? '');

            $saveData = [
                'patient_id' => $data['patient_id'],
                'patient_queue_id' => !empty($data['queue_id']) ? $data['queue_id'] : null,
                'terapis_id' => $terapisIds,
                'complaint' => $complaintIds,
                'medhis' => $medhisIds,
                'checkup' => $data['checkup'] ?? '',
                'results' => $resultIds,
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
                'verteba' => $data['vertebra'] ?? '',
                'keterangan_verteba' => $data['keterangan_verteba'] ?? '',
                'thorax' => $data['thorax'] ?? '',
                'keterangan_thorax' => $data['keterangan_thorax'] ?? '',
                'kompresi' => $data['kompresi'] ?? '',
                'keterangan_kompresi' => $data['keterangan_kompresi'] ?? '',
                'plintiran' => $data['plintiran'] ?? '',
                'keterangan_plintiran' => $data['keterangan_plintiran'] ?? '',
                'visualfoot' => $data['visualfoot'] ?? '',
                'keterangan_visualfoot' => $data['keterangan_visualfoot'] ?? '',
                'pubis' => $data['pubis'] ?? '',
                'date' => date('Y-m-d H:i:s'),
                'finish_at' => date('Y-m-d H:i:s'),
                'type' => 'posted',
                'created_by' => !empty($data['user_id']) ? $data['user_id'] : null,
                'history_region' => !empty($data['region_id']) ? $data['region_id'] : null,
            ];

            // AVOID DUPLICATION: Check if a draft history already exists for this queue
            $existing = null;
            if (!empty($data['queue_id'])) {
                $existing = $this->historyModel->where('patient_queue_id', $data['queue_id'])->first();
            }

            if ($existing) {
                // Update existing record
                $this->historyModel->update($existing->id, $saveData);
                $id = $existing->id;
            } else {
                // Insert new record
                $this->historyModel->insert($saveData);
                $id = $this->historyModel->getInsertID();
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Rekam medis berhasil disimpan',
                'id' => $id
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
