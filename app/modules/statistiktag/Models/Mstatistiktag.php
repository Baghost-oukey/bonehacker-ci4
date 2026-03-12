<?php

namespace App\modules\statistiktag\Models;

use CodeIgniter\Model;

class Mstatistiktag extends Model
{
    protected $table            = 'complaint_tags';
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

    public function getComplaintStatistic($startDate, $endDate, $region_id = null)
    {
        // 1. Ambil semua nama tag terlebih dahulu
        $tagNames = $this->db->table('complaint_tags')
            ->select('name')
            ->get()
            ->getResultArray();
        $tagNames = array_column($tagNames, 'name');

        // 2. Query utama statistik
        $builder = $this->db->table('histories h');
        $builder->select('MIN(DATE(h.date_modified)) as date, ct.name as tag_name, COUNT(h.id) as total');
        $builder->join('complaint_tags ct', 'FIND_IN_SET(ct.id, h.complaint) > 0', 'inner');
        $builder->join('patients p', 'p.id = h.patient_id', 'inner');

        $builder->where('DATE(h.date_modified) >=', $startDate);
        $builder->where('DATE(h.date_modified) <=', $endDate);
        $builder->where('h.is_delete', 0); // Di CI4 false biasanya disimpan sebagai 0

        if ($region_id) {
            $builder->where('h.history_region', $region_id);
        }

        $builder->groupBy('tag_name');
        $data = $builder->get()->getResultArray();

        return $this->formatResult($tagNames, $data);
    }

    public function getMedhisStatistic($startDate, $endDate, $region_id = null)
    {
        // 1. Ambil semua nama tag
        $tagNames = $this->db->table('medhis_tags')
            ->select('name')
            ->get()
            ->getResultArray();
        $tagNames = array_column($tagNames, 'name');

        // 2. Query utama statistik
        $builder = $this->db->table('histories h');
        $builder->select('MIN(DATE(h.date_modified)) as date, mt.name as tag_name, COUNT(h.id) as total');
        $builder->join('medhis_tags mt', 'FIND_IN_SET(mt.id, h.medhis) > 0', 'inner');
        $builder->join('patients p', 'p.id = h.patient_id', 'inner');

        $builder->where('DATE(h.date_modified) >=', $startDate);
        $builder->where('DATE(h.date_modified) <=', $endDate);
        $builder->where('h.is_delete', 0);

        if ($region_id) {
            $builder->where('h.history_region', $region_id);
        }

        $builder->groupBy('tag_name');
        $data = $builder->get()->getResultArray();

        return $this->formatResult($tagNames, $data);
    }

    private function formatResult($tagNames, $data)
    {
        $result = [];
        foreach ($tagNames as $tag) {
            $result[$tag] = [
                'total' => 0,
                'dates' => []
            ];
        }

        foreach ($data as $row) {
            $tagName = $row['tag_name'];
            if (isset($result[$tagName])) {
                $result[$tagName]['total'] += (int) $row['total'];

                $dateKey = $row['date'];
                if (!isset($result[$tagName]['dates'][$dateKey])) {
                    $result[$tagName]['dates'][$dateKey] = 0;
                }
                $result[$tagName]['dates'][$dateKey] += (int) $row['total'];
            }
        }

        // Urutkan berdasarkan total tertinggi
        uasort($result, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $result;
    }

    public function getRegions()
    {
        return $this->db->table('regions')->get()->getResult();
    }
}
