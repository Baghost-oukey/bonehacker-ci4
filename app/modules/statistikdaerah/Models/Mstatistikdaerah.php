<?php

namespace App\modules\statistikdaerah\Models;

use CodeIgniter\Model;

class Mstatistikdaerah extends Model
{
    protected $table            = 'histories';
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

    public function get_statistic($startDate, $endDate, $regionId, $filter = 'daily', $kabupatenId = null, $kecamatanId = null, $desaId = null): array
    {
        $groupFormat = match ($filter) {
            'weekly'  => "CONCAT(YEAR(h.date), '-', WEEK(h.date, 1))",
            'monthly' => "DATE_FORMAT(h.date, '%Y-%m')",
            'yearly'  => "YEAR(h.date)",
            default   => "DATE(h.date)",
        };

        $builder = $this->db->table($this->table . ' h');
        $builder->select("$groupFormat as date, COUNT(h.id) as total");
        $builder->join('patients p', 'p.id = h.patient_id', 'left');
        $builder->join('patient_address pa', 'pa.patient_id = p.id', 'left');

        $builder->where('DATE(h.date) >=', $startDate);
        $builder->where('DATE(h.date) <=', $endDate);
        $builder->where('h.is_delete', 0);
        if ($regionId) {
            $builder->where('h.history_region', $regionId);
        }
        if ($kabupatenId) {
            $builder->where('pa.kabupaten_id', $kabupatenId);
        }
        if ($kecamatanId) {
            $builder->where('pa.kecamatan_id', $kecamatanId);
        }
        if ($desaId) {
            $builder->where('pa.desa_id', $desaId);
        }

        return $builder->groupBy("$groupFormat")
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function get_all_kabupaten(): array
    {
        return $this->db->table('patient_address pa')
            ->distinct()
            ->select('pa.kabupaten_id, pa.kabupaten_nama')
            ->join('histories h', 'pa.patient_id = h.patient_id')
            ->where('h.is_delete', 0)
            ->where('pa.kabupaten_nama IS NOT NULL')
            ->where('pa.kabupaten_nama !=', '')
            ->get()
            ->getResultArray();
    }

    public function get_kecamatan_by_kabupaten($kabupaten_id): array
    {
        return $this->db->table('patient_address pa')
            ->distinct()
            ->select('pa.kecamatan_id, pa.kecamatan_nama')
            ->join('histories h', 'pa.patient_id = h.patient_id')
            ->where('h.is_delete', 0)
            ->where('pa.kabupaten_id', $kabupaten_id)
            ->where('pa.kecamatan_nama IS NOT NULL')
            ->where('pa.kecamatan_nama !=', '')
            ->get()
            ->getResult();
    }

    public function get_desa_by_kecamatan($kecamatan_id): array
    {
        return $this->db->table('patient_address pa')
            ->distinct()
            ->select('pa.desa_id, pa.desa_nama')
            ->join('histories h', 'pa.patient_id = h.patient_id')
            ->where('h.is_delete', 0)
            ->where('pa.kecamatan_id', $kecamatan_id)
            ->where('pa.desa_nama IS NOT NULL')
            ->where('pa.desa_nama !=', '')
            ->get()
            ->getResult();
    }
}
