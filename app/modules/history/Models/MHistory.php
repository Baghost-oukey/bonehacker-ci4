<?php

namespace App\modules\history\Models;

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

    public function getListData($id, $option = [])
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

    public function getKeterangan($patient_id)
    {
        return $this->select('keterangan_verteba, keterangan_thorax, keterangan_kompresi, keterangan_plintiran, keterangan_visualfoot')
            ->where('patient_id', $patient_id)
            ->orderBy('id', 'DESC')
            ->first();
    }

    // Fungsi Transaksi Kejantanan (Gaya CI4)
    public function updateKejantanan($id_history, $data, $kejantanan_data)
    {
        $this->db->transStart();

        $this->update($id_history, $data);

        $dbKejantanan = $this->db->table('kejantanan');
        if (isset($data['kejantanan']) && $data['kejantanan'] === 'ya') {
            $existing = $dbKejantanan->where('id_history', $id_history)->get()->getRow();

            if ($existing) {
                $dbKejantanan->where('id_history', $id_history)->update($kejantanan_data);
            } else {
                $kejantanan_data['id_history'] = $id_history;
                $dbKejantanan->insert($kejantanan_data);
            }
        } else {
            $dbKejantanan->where('id_history', $id_history)->delete();
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}
