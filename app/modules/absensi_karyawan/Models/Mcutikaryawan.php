<?php

namespace App\modules\absensi_karyawan\Models;

use CodeIgniter\Model;

class Mcutikaryawan extends Model
{
    protected $table            = 'cuti_karyawan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'terapis_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'keterangan',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getCutiByRegion($regionId = null, $allowedRegions = null)
    {
        $builder = $this->select('cuti_karyawan.*, terapis.nama as nama_terapis')
            ->join('terapis', 'terapis.id = cuti_karyawan.terapis_id');

        if ($regionId) {
            $builder->where('terapis.region_id', $regionId);
        }

        if ($allowedRegions) {
            $builder->whereIn('terapis.region_id', (array)$allowedRegions);
        }

        return $builder->orderBy('cuti_karyawan.tanggal_mulai', 'DESC')->findAll();
    }

    public function getTotalCutiTerpakai($tahun = null)
    {
        $tahun = $tahun ?? date('Y');
        $result = $this->select('terapis_id, SUM(jumlah_hari) as total')
            ->where('YEAR(tanggal_mulai)', $tahun)
            ->groupBy('terapis_id')
            ->findAll();
            
        $data = [];
        foreach ($result as $r) {
            $data[$r->terapis_id] = (int)$r->total;
        }
        return $data;
    }
}
