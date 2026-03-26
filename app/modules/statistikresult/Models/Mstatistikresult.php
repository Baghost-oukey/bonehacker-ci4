<?php

namespace App\modules\statistikresult\Models;

use CodeIgniter\Model;

class Mstatistikresult extends Model
{
    protected $table            = 'result_tags';
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


    public function getResultStatistic(string $startDate, string $endDate, int $regionId = null, $filter = null): array
    {
        $tagNames = $this->builder()
            ->select('name')
            ->get()
            ->getResultArray();
        $tagNames = array_column($tagNames, 'name');
        $builder = $this->db->table('histories h');
        $builder->select('MIN(DATE(h.date_modified)) as date, rt.name as tag_name, COUNT(h.id) as total')
            ->join('result_tags rt', "FIND_IN_SET(rt.id, h.results) > 0", 'inner')
            ->join('patients p', 'p.id = h.patient_id', 'inner')
            ->where('DATE(h.date_modified) >=', $startDate)
            ->where('DATE(h.date_modified) <=', $endDate)
            ->where('h.is_delete', 0);

        if ($regionId) {
            $builder->where('h.history_region', $regionId);
        }
        $data = $builder->groupBy('rt.name')
            ->get()
            ->getResultArray();
        return $this->formatResult($tagNames, $data);
    }

    private function formatResult(array $tagNames, array $data): array
    {
        $result = [];
        foreach ($tagNames as $tag) {
            $result[$tag] = ['total' => 0, 'dates' => []];
        }
        foreach ($data as $row) {
            $name = $row['tag_name'];
            if (isset($result[$name])) {
                $result[$name]['total'] += (int) $row['total'];
                $result[$name]['dates'][$row['date']] = (int) $row['total'];
            }
        }

        uasort($result, fn($a, $b) => $b['total'] <=> $a['total']);

        return $result;
    }

    public function getRegions()
    {
        return $this->db->table('regions')->get()->getResult();
    }
}
