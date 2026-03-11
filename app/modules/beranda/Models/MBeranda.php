<?php

namespace App\modules\beranda\Models;

use CodeIgniter\Model;

class MBeranda extends Model
{
    protected $table            = 'patients';
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

    public function applyRegionFilter($builder, $role, $region_patients, $history = false)
    {
        if ($role === 'user' && !empty($region_patients)) {
            $column = $history ? 'patients.region_id' : 'region_id';

            if (is_array($region_patients)) {
                $builder->whereIn($column, $region_patients);
            } else {
                $builder->where($column, $region_patients);
            }
        }
    }

    public function getPatientCount($type, $role, $region_patients)
    {
        $builder = $this->db->table('patients');
        $this->applyRegionFilter($builder, $role, $region_patients);
        $builder->where('is_delete', 0);

        switch ($type) {
            case 'today':
                $builder->where('DATE(created_at)', date('Y-m-d'));
                break;
            case 'yesterday':
                $builder->where('DATE(created_at)', date('Y-m-d', strtotime('-1 day')));
                break;
            case 'thismonth':
                $builder->where('MONTH(created_at)', date('m'))->where('YEAR(created_at)', date('Y'));
                break;
            case 'lastmonth':
                $builder->where('MONTH(created_at)', date('m', strtotime('-1 month')))
                    ->where('YEAR(created_at)', date('Y', strtotime('-1 month')));
                break;
            case 'this_year':
                $builder->where('YEAR(created_at)', date('Y'));
                break;
            case 'last_year':
                $builder->where('YEAR(created_at)', date('Y', strtotime('-1 year')));
                break;
        }
        return $builder->countAllResults();
    }

    public function getVisitCount($type, $role, $region_patients)
    {
        $builder = $this->db->table('patient_queues');

        $this->applyRegionFilter($builder, $role, $region_patients);

        switch ($type) {
            case 'today':
                $builder->where('DATE(queue_date)', date('Y-m-d'));
                break;
            case 'yesterday':
                $builder->where('DATE(queue_date)', date('Y-m-d', strtotime('-1 day')));
                break;
            case 'this_month':
                $builder->where('MONTH(queue_date)', date('m'))
                    ->where('YEAR(queue_date)', date('Y'));
                break;
            case 'last_month':
                $lastMonth = date('Y-m-d', strtotime('first day of last month'));
                $builder->where('MONTH(queue_date)', date('m', strtotime($lastMonth)))
                    ->where('YEAR(queue_date)', date('Y', strtotime($lastMonth)));
                break;
            case 'this_year':
                $builder->where('YEAR(queue_date)', date('Y'));
                break;
            case 'last_year':
                $builder->where('YEAR(queue_date)', date('Y', strtotime('-1 year')));
                break;
            case 'all':
                // Tidak ada filter tanggal untuk semua data
                break;
        }

        return $builder->countAllResults();
    }
}
