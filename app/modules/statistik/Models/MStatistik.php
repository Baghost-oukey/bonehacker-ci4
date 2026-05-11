<?php

namespace App\modules\statistik\Models;

use CodeIgniter\Model;

class MStatistik extends Model
{
    protected $table            = 'histories';
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

    public function get_statistics($startDate, $endDate, $regionId, $filter = 'daily')
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' h');

        // 1. Tentukan format Grouping berdasarkan Filter
        switch ($filter) {
            case 'weekly':
                $groupFormat = "CONCAT(YEAR(h.date), '-', WEEK(h.date, 1))";
                break;
            case 'monthly':
                $groupFormat = "DATE_FORMAT(h.date, '%Y-%m')";
                break;
            case 'yearly':
                $groupFormat = "YEAR(h.date)";
                break;
            case 'daily':
            default:
                $groupFormat = "DATE(h.date)";
                break;
        }

        // 2. Subquery untuk menghitung total history per pasien
        $subquery = $db->table('histories')
            ->select('patient_id, COUNT(DISTINCT id) as history_count')
            ->groupBy('patient_id')
            ->getCompiledSelect();

        // 3.  Query
        $builder->select("$groupFormat as date", false);
        $builder->select("COUNT(DISTINCT h.id) as total", false);

        // Pasien Baru/Lama berdasarkan history_count
        $builder->select("SUM(CASE WHEN subq.history_count = 1 THEN 1 ELSE 0 END) as newPatientsCount", false);
        $builder->select("SUM(CASE WHEN subq.history_count > 1 THEN 1 ELSE 0 END) as oldPatientsCount", false);

        $builder->join('patients p', 'p.id = h.patient_id', 'left');
        $builder->join("($subquery) subq", 'subq.patient_id = p.id', 'left');

        // Filter Tanggal & Region
        $builder->where("DATE(h.date) >= '$startDate'");
        $builder->where("DATE(h.date) <= '$endDate'");
        $builder->where('h.is_delete', false);

        if ($regionId) {
            $builder->where('h.history_region', $regionId);
        }

        $builder->groupBy($groupFormat);
        $builder->orderBy('date', 'ASC');

        return $builder->get()->getResult();
    }
    public function get_analisis($startDate, $endDate, $regionId = null)
    {
        $db = \Config\Database::connect();
        $firstVisited = $db->table('histories')
            ->select('patient_id, MIN(date) as first_date')
            ->where('is_delete', 0)
            ->groupBy('patient_id')
            ->getCompiledSelect();
        $builder = $db->table('regions r');
        $builder->select('r.id, r.name as cabang');
        $builder->select("COUNT(DISTINCT h.id) as total_pasien");
        $builder->select("COUNT(DISTINCT CASE WHEN first_v.first_date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59' THEN h.patient_id END) as pasien_baru");
        $builder->select("COUNT(DISTINCT CASE WHEN first_v.first_date < '{$startDate} 00:00:00' THEN h.patient_id END) as pasien_lama");
        $joinCondition = "(h.patient_queue_id = pq.id OR h.history_region = r.id)";
        $builder->join('patient_queues pq', 'pq.region_id = r.id', 'left');
        $builder->join('histories h', $joinCondition .
            " AND h.date >= '{$startDate} 00:00:00'" .
            " AND h.date <= '{$endDate} 23:59:59'" .
            " AND h.is_delete = 0", 'left');

        $builder->join("($firstVisited) first_v", 'first_v.patient_id = h.patient_id', 'left');
        if (!empty($regionId)) {
            $builder->where('r.id', $regionId);
        }

        $builder->groupBy('r.id');
        $builder->orderBy('total_pasien', 'DESC');

        return $builder->get()->getResult();
    }
}
