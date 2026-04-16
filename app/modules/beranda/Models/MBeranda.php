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

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

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
        if (!empty($region_patients) && $region_patients !== 'all') {
            $column = $history ? 'history_region' : 'region_id';

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
            case 'thisyear':
                $builder->where('YEAR(created_at)', date('Y'));
                break;
            case 'lastyear':
                $builder->where('YEAR(created_at)', date('Y', strtotime('-1 year')));
                break;
        }
        return $builder->countAllResults();
    }

    public function getVisitCount($type, $role, $region_patients)
    {
        $builder = $this->db->table('histories');
        $builder->where('is_delete', 0);

        $this->applyRegionFilter($builder, $role, $region_patients, true);

        // if (!empty($region_patients) && $region_patients !== 'all') {
        //     $builder->join('patients', 'patients.id = histories.patient_id');
        //     $this->applyRegionFilter($builder, $role, $region_patients, true);
        // }
        // // $this->applyRegionFilter($builder, $role, $region_patients);
        // $builder->where('histories.is_delete', 0);

        switch ($type) {
            case 'today':
                $builder->where('DATE(histories.date)', date('Y-m-d'));
                break;
            case 'yesterday':
                $builder->where('DATE(histories.date)', date('Y-m-d', strtotime('-1 day')));
                break;
            case 'thismonth':
                $builder->where('MONTH(histories.date)', date('m'))
                    ->where('YEAR(histories.date)', date('Y'));
                break;
            case 'lastmonth':
                $lastMonth = date('Y-m-d', strtotime('first day of last month'));
                $builder->where('MONTH(histories.date)', date('m', strtotime($lastMonth)))
                    ->where('YEAR(histories.date)', date('Y', strtotime($lastMonth)));
                break;
            case 'thisyear':
                $builder->where('YEAR(histories.date)', date('Y'));
                break;
            case 'lastyear':
                $builder->where('YEAR(histories.date)', date('Y', strtotime('-1 year')));
                break;
            case 'all':
                break;
        }

        return $builder->countAllResults();
    }
}
