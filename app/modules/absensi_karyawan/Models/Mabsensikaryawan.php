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

    public function getRekapHarian($bulan = null, $tahun = null, $allowedRegions = null)
    {
        $builder = $this->select('absensi_karyawan.tanggal')
            ->select('SUM(CASE WHEN absensi_karyawan.status = "Hadir" THEN 1 ELSE 0 END) as total_hadir', false)
            ->select('SUM(CASE WHEN absensi_karyawan.status = "Tidak Hadir" THEN 1 ELSE 0 END) as total_tidak_hadir', false)
            ->join('terapis', 'terapis.id = absensi_karyawan.terapis_id', 'inner')
            ->where('terapis.is_active', 1) // Filter hanya terapis aktif
            ->where('terapis.is_presensi', 1); // Filter hanya terapis yang ikut presensi

        if ($bulan && $tahun) {
            $builder->where('MONTH(absensi_karyawan.tanggal)', $bulan)
                    ->where('YEAR(absensi_karyawan.tanggal)', $tahun);
        }

        // Filter by region
        if (!empty($allowedRegions)) {
            if (is_array($allowedRegions)) {
                $builder->whereIn('terapis.region_id', $allowedRegions);
            } else {
                $builder->where('terapis.region_id', $allowedRegions);
            }
        }

        return $builder->groupBy('absensi_karyawan.tanggal')
            ->orderBy('absensi_karyawan.tanggal', 'DESC')
            ->findAll();
    }

    public function getByTanggal(string $tanggal, $allowedRegions = null)
    {
        $builder = $this->select('absensi_karyawan.*')
            ->join('terapis', 'terapis.id = absensi_karyawan.terapis_id', 'inner')
            ->where('absensi_karyawan.tanggal', $tanggal)
            ->where('terapis.is_active', 1) // Filter hanya terapis aktif
            ->where('terapis.is_presensi', 1); // Filter hanya terapis yang ikut presensi

        // Filter by region
        if (!empty($allowedRegions)) {
            if (is_array($allowedRegions)) {
                $builder->whereIn('terapis.region_id', $allowedRegions);
            } else {
                $builder->where('terapis.region_id', $allowedRegions);
            }
        }

        return $builder->findAll();
    }
}
