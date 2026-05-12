<?php

namespace App\modules\kalender\Models;

use CodeIgniter\Model;

class MKalender extends Model
{
    protected $table         = 'kalender';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'tanggal', 'keterangan', 'tipe', 'hari_rutin',
        'region_id', 'tahun', 'is_active', 'created_by', 'updated_by'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua libur untuk tahun tertentu (global atau per region)
     * Global (superadmin) = region_id IS NULL
     * Cabang = region_id = X (override/tambahan dari global)
     */
    public function getByTahun(int $tahun, $regionId = null): array
    {
        $builder = $this->where('tahun', $tahun)->where('is_active', 1);

        if ($regionId) {
            // Ambil global + cabang ini
            $builder->groupStart()
                ->where('region_id', null)
                ->orWhere('region_id', $regionId)
                ->groupEnd();
        } else {
            // Hanya global
            $builder->where('region_id', null);
        }

        return $builder->orderBy('tanggal', 'ASC')->findAll();
    }

    /**
     * Ambil hanya kalender milik region tertentu (untuk edit owner)
     */
    public function getByRegion(int $tahun, $regionId): array
    {
        return $this->where('tahun', $tahun)
                    ->where('region_id', $regionId)
                    ->where('is_active', 1)
                    ->orderBy('tanggal', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil hanya kalender global (superadmin)
     */
    public function getGlobal(int $tahun): array
    {
        return $this->where('tahun', $tahun)
                    ->where('region_id', null)
                    ->where('is_active', 1)
                    ->orderBy('tanggal', 'ASC')
                    ->findAll();
    }

    /**
     * Cek apakah tanggal tertentu adalah hari libur
     */
    public function isLibur(string $tanggal, $regionId = null): bool
    {
        $tahun = (int) date('Y', strtotime($tanggal));
        $libur = $this->getByTahun($tahun, $regionId);

        foreach ($libur as $item) {
            if ($item['tipe'] === 'libur_khusus' && $item['tanggal'] === $tanggal) {
                return true;
            }
            if ($item['tipe'] === 'libur_rutin') {
                $hariTanggal = (int) date('w', strtotime($tanggal)); // 0=Minggu, 5=Jumat
                if ($item['hari_rutin'] == $hariTanggal) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Copy kalender global ke region tertentu
     */
    public function copyGlobalToRegion(int $tahun, int $regionId, int $userId): bool
    {
        // Hapus kalender lama region ini untuk tahun ini
        $this->where('tahun', $tahun)->where('region_id', $regionId)->delete();

        $global = $this->getGlobal($tahun);
        if (empty($global)) return false;

        $batch = [];
        foreach ($global as $item) {
            $batch[] = [
                'tanggal'     => $item['tanggal'],
                'keterangan'  => $item['keterangan'],
                'tipe'        => $item['tipe'],
                'hari_rutin'  => $item['hari_rutin'],
                'region_id'   => $regionId,
                'tahun'       => $tahun,
                'is_active'   => 1,
                'created_by'  => $userId,
                'updated_by'  => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }

        return $this->insertBatch($batch) !== false;
    }

    /**
     * Generate tanggal libur rutin untuk satu tahun (expand hari_rutin jadi tanggal)
     */
    public function generateLiburRutin(int $tahun, $regionId = null): array
    {
        $liburRutin = $this->where('tahun', $tahun)
                           ->where('tipe', 'libur_rutin')
                           ->where('is_active', 1);

        if ($regionId) {
            $liburRutin->groupStart()
                ->where('region_id', null)
                ->orWhere('region_id', $regionId)
                ->groupEnd();
        } else {
            $liburRutin->where('region_id', null);
        }

        $rules = $liburRutin->findAll();
        $tanggalLibur = [];

        foreach ($rules as $rule) {
            $hariTarget = (int) $rule['hari_rutin'];
            $start = new \DateTime("$tahun-01-01");
            $end   = new \DateTime("$tahun-12-31");

            while ($start <= $end) {
                if ((int) $start->format('w') === $hariTarget) {
                    $tanggalLibur[] = $start->format('Y-m-d');
                }
                $start->modify('+1 day');
            }
        }

        return $tanggalLibur;
    }
}
