<?php

namespace App\Modules\history\Models;

use CodeIgniter\Model;

class MHistory extends Model
{
    protected $table            = 'histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'patient_id',
        'terapis_id',
        'complaint',
        'medhis',
        'checkup',
        'cervical',
        'thoraxal',
        'lumbar',
        'sacrum',
        'sacral',
        'pelvis',
        'plintiran',
        'kompresi',
        'verteba',
        'thorax',
        'visualfoot',
        'other',
        'results',
        'measure',
        'date',
        'pubis',
        'tensi',
        'power',
        'pr',
        'keterangan_verteba',
        'keterangan_thorax',
        'keterangan_kompresi',
        'keterangan_plintiran',
        'keterangan_visualfoot',
        'date_modified',
        'is_delete',
        'created_by',
        'updated_by',
        'kejantanan',
        'history_region',
        'patient_queue_id',
        'process_at',
        'finish_at',
        'type'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getListData($id, array $options)
    {
        $builder = $this->db->table($this->table . ' h');

        $builder->select('h.*');
        $builder->select('GROUP_CONCAT(DISTINCT ct.name ORDER BY ct.name SEPARATOR ", ") AS complaint_names');
        $builder->select('GROUP_CONCAT(DISTINCT mt.name ORDER BY mt.name SEPARATOR ", ") AS medhis_names');
        $builder->join('complaint_tags ct', "FIND_IN_SET(ct.id, h.complaint) > 0", 'left');
        $builder->join('medhis_tags mt', "FIND_IN_SET(mt.id, h.medhis) > 0", 'left');
        $builder->where('h.patient_id', $id);
        $builder->where('h.is_delete', 0);
        if (!empty($options['where_like'])) {
            $builder->groupStart();
            foreach ($options['where_like'] as $like) {
                $builder->orWhere($like);
            }
            $builder->groupEnd();
        }
        $builder->groupBy('h.id');
        $order = $options['order'] ?? 'id';
        $mode  = $options['mode'] ?? 'desc';
        $builder->orderBy($order, $mode);
        $limit  = $options['limit'] ?? 10;
        $offset = $options['offset'] ?? 0;
        return $builder->get($limit, $offset)->getResult();
    }


    public function getTotalData($id, $option = [])
    {
        $builder = $this->db->table($this->table . ' h');
        $builder->select('COUNT(DISTINCT h.id) AS total');
        $builder->join('complaint_tags ct', "FIND_IN_SET(ct.id, h.complaint) > 0", 'left');
        $builder->join('medhis_tags mt', "FIND_IN_SET(mt.id, h.medhis) > 0", 'left');
        $builder->where('h.patient_id', $id);
        $builder->where('h.is_delete', 0);
        if (!empty($option['where_like'])) {
            $builder->groupStart();
            foreach ($option['where_like'] as $like) {
                $builder->orLike($like);
            }
        };
        return $builder->get()->getRow()->total ?? 0;
    }


    public function count_histories_by_patient_id($patient_id)
    {
        return $this->builder()
            ->where('patient_id', $patient_id)
            ->where('is_delete', 0) // Tambahan: agar data yang dihapus tidak ikut terhitung
            ->countAllResults();
    }


    public function getById($id)
    {
        return $this->where('id', $id)->first();
    }


    public function getActiveTerapis()
    {
        return $this->db->table('terapis')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResult();
    }


    public function getSelectedTerapis($history_id)
    {
        return $this->db->table('terapis')
            ->select('terapis.id, terapis.nama')
            ->join('histories', 'FIND_IN_SET(terapis.id, histories.terapis_id) > 0', 'inner')
            ->where('histories.id', $history_id)
            ->get()->getResult();
    }


    public function getKejantananById($id_history)
    {
        return $this->db->table('kejantanan')
            ->where('history_id', $id_history)
            ->get()->getRow();
    }


    public function getKeterangan($patient_id)
    {
        return $this->select('keterangan_verteba, keterangan_thorax, keterangan_kompresi, keterangan_plintiran, keterangan_visualfoot')
            ->where('patient_id', $patient_id)
            ->orderBy('id', 'DESC')
            ->first();
    }


    public function getComplaintTags($ids)
    {
        if (empty($ids)) return [];
        return $this->db->table('complaint_tags')->whereIn('id', $ids)->get()->getResultArray();
    }


    public function getMedhisTags($ids)
    {
        if (empty($ids)) return [];
        return $this->db->table('medhis_tags')->whereIn('id', $ids)->get()->getResultArray();
    }


    public function getResultTags($ids)
    {
        if (empty($ids)) return [];
        return $this->db->table('result_tags')->whereIn('id', $ids)->get()->getResultArray();
    }


    public function updateKejantanan($id_history, $kejantanan_data, $status_kejantanan = 'tidak')
    {
        $this->db->transStart();
        $dbKejantanan = $this->db->table('kejantanan');
        if ($status_kejantanan === 'ya') {
            $existing = $dbKejantanan->where('history_id', $id_history)->get()->getRow();
            if ($existing) {
                $dbKejantanan->where('history_id', $id_history)->update($kejantanan_data);
            } else {
                $kejantanan_data['history_id'] = $id_history;
                $dbKejantanan->insert($kejantanan_data);
            }
        } else {
            $dbKejantanan->where('history_id', $id_history)->delete();
        }
        $this->db->transComplete();
        return $this->db->transStatus();
    }
    
    // Fungsi Lama
    // public function updateKejantanan($id_history, $data, $kejantanan_data)
    // {
    //     $this->db->transStart();

    //     $this->update($id_history, $data);

    //     $dbKejantanan = $this->db->table('kejantanan');
    //     if (isset($data['kejantanan']) && $data['kejantanan'] === 'ya') {
    //         $existing = $dbKejantanan->where('id_history', $id_history)->get()->getRow();

    //         if ($existing) {
    //             $dbKejantanan->where('id_history', $id_history)->update($kejantanan_data);
    //         } else {
    //             $kejantanan_data['id_history'] = $id_history;
    //             $dbKejantanan->insert($kejantanan_data);
    //         }
    //     } else {
    //         $dbKejantanan->where('id_history', $id_history)->delete();
    //     }

    //     $this->db->transComplete();
    //     return $this->db->transStatus();
    // }
}
