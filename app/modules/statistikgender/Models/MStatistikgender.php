<?php

namespace App\modules\statistikgender\Models;

use CodeIgniter\Model;

class MStatistikgender extends Model
{
    protected $table            = 'patients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
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

    public function get_statistics(string $startDate, string $endDate, ?int $regionId = null, string $filter = 'daily'): array
    {
        $groupFormat = $this->getDateFormat($filter);
        $this->select("{$groupFormat} as date")
            ->select("COUNT(CASE WHEN gender = 'Man' THEN 1 END) as total_male")
            ->select("COUNT(CASE WHEN gender = 'Woman' THEN 1 END) as total_female")
            ->select("COUNT(*) as total");

        $this->where('DATE(created_at) >=', $startDate)
            ->where('DATE(created_at) <=', $endDate)
            ->where('is_delete', 0);

        if ($regionId) {
            $this->where('region_id', $regionId);
        }

        return $this->groupBy('date')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    private function getDateFormat(string $filter): string
    {
        return match ($filter) {
            'weekly'  => "CONCAT(YEAR(created_at), '-', WEEK(created_at, 1))",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
            'yearly'  => "YEAR(created_at)",
            default   => "DATE(created_at)",
        };
    }

    public function getRegions(): array
    {
        return $this->db->table('regions')->get()->getResult();
    }
}
