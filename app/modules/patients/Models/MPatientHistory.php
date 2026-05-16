<?php

namespace App\modules\patients\Models;

use CodeIgniter\Model;

class MPatientHistory extends Model
{
    protected $table            = 'patient_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'patient_id',
        'field_name',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
        'ip_address',
        'user_agent',
    ];

    protected $useTimestamps = false;

    /**
     * Get history for a specific patient
     */
    public function getPatientHistory($patientId, $limit = null)
    {
        $builder = $this->db->table($this->table . ' ph')
            ->select('ph.*, u.realname as changed_by_name')
            ->join('users u', 'u.id = ph.changed_by', 'left')
            ->where('ph.patient_id', $patientId)
            ->orderBy('ph.changed_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->get()->getResult();
    }

    /**
     * Get history grouped by change session (same timestamp)
     */
    public function getGroupedHistory($patientId)
    {
        $history = $this->getPatientHistory($patientId);
        
        $grouped = [];
        foreach ($history as $record) {
            $key = $record->changed_at . '_' . $record->changed_by;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'changed_at' => $record->changed_at,
                    'changed_by' => $record->changed_by,
                    'changed_by_name' => $record->changed_by_name,
                    'ip_address' => $record->ip_address,
                    'user_agent' => $record->user_agent,
                    'changes' => []
                ];
            }
            $grouped[$key]['changes'][] = [
                'field_name' => $record->field_name,
                'old_value' => $record->old_value,
                'new_value' => $record->new_value,
            ];
        }

        return array_values($grouped);
    }

    /**
     * Log a single field change
     */
    public function logChange($patientId, $fieldName, $oldValue, $newValue, $userId = null)
    {
        $request = \Config\Services::request();
        
        return $this->insert([
            'patient_id' => $patientId,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $userId,
            'changed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ]);
    }

    /**
     * Log multiple field changes at once
     */
    public function logMultipleChanges($patientId, array $changes, $userId = null)
    {
        $request = \Config\Services::request();
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent()->getAgentString();

        $data = [];
        foreach ($changes as $fieldName => $values) {
            $data[] = [
                'patient_id' => $patientId,
                'field_name' => $fieldName,
                'old_value' => $values['old'] ?? null,
                'new_value' => $values['new'] ?? null,
                'changed_by' => $userId,
                'changed_at' => $timestamp,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ];
        }

        return $this->insertBatch($data);
    }
}
