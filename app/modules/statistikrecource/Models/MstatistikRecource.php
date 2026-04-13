<?php

namespace App\modules\statistikrecource\Models;

use CodeIgniter\Model;
use PhpOffice\PhpSpreadsheet\Calculation\FunctionArray;

class MstatistikRecource extends Model
{
    protected $table            = 'resources';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

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

    public function get_sumber_marketing($startDate, $endDate, $regionID = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('resources res');
        $builder->select('res.nama as saluran, COUNT(DISTINCT h.patient_id) as total_pasien');

      $builder->join('patients p', 'p.patient_information = res.id', 'left');

        $builder->join('histories h', "h.patient_id = p.id 
                    AND h.date >= '$startDate 00:00:00' 
                    AND h.date <= '$endDate 23:59:59' 
                    AND h.is_delete = 0", 'left');

        $builder->join('patient_queues pq', 'pq.id = h.patient_queue_id', 'left');

        if ($regionID) {
            $builder->where('pq.region_id', $regionID);
        }

        $builder->groupBy('res.id');
        $builder->orderBy('total_pasien', 'DESC');

        return $builder->get()->getResult();
    }
}
