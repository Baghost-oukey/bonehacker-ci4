<?php

namespace App\modules\absensi_karyawan\Models;

use CodeIgniter\Model;

class Mabsensikaryawan extends Model
{
    protected $table            = 'absensi_karyawan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['terapis_id', 'tanggal', 'status', 'keterangan'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
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


    public function getTotalHadir(int $terapisId, int $bulan, int $tahun): int
    {
        $bulanBagus = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);
        $tanggalAwal = "$tahun-$bulanBagus-01";
        $tanggalAkhir = date('Y-m-t', strtotime($tanggalAwal));
        return $this->where('terapis_id', $terapisId)
            ->where('status', 'Hadir')
            ->where('tanggal >=', $tanggalAwal)
            ->where('tanggal <=', $tanggalAkhir)
            ->countAllResults();
    }

    public function getRekapHarian($bulan = null, $tahun = null)
    {
        $builder = $this->select('tanggal')
            ->select('SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as total_hadir', false)
            ->select('SUM(CASE WHEN status = "Tidak Hadir" THEN 1 ELSE 0 END) as total_tidak_hadir', false);

        if ($bulan && $tahun) {
            $builder->where('MONTH(tanggal)', $bulan)
                    ->where('YEAR(tanggal)', $tahun);
        }

        return $builder->groupBy('tanggal')
            ->orderBy('tanggal', 'DESC')
            ->findAll();
    }

    public function getByTanggal(string $tanggal)
    {
        return $this->where('tanggal', $tanggal)->findAll();
    }
}
