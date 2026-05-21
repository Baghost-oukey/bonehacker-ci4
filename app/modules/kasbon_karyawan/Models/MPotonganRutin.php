<?php

namespace App\modules\kasbon_karyawan\Models;

use CodeIgniter\Model;

class MPotonganRutin extends Model
{
    protected $table         = 'potongan_rutin';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['terapis_id', 'nama_potongan', 'nominal', 'is_active', 'created_by'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByTerapis(int $terapisId): array
    {
        return $this->where('terapis_id', $terapisId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function getTotalByTerapis(int $terapisId): int
    {
        $result = $this->selectSum('nominal', 'total')
                       ->where('terapis_id', $terapisId)
                       ->where('is_active', 1)
                       ->first();
        return (int)($result['total'] ?? 0);
    }

    public function saveSetting(int $terapisId, string $namaPotongan, float $nominal, int $userId): bool
    {
        $existing = $this->where('terapis_id', $terapisId)
                         ->where('nama_potongan', $namaPotongan)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'nominal'   => $nominal,
                'is_active' => 1,
            ]);
        }

        return $this->insert([
            'terapis_id'    => $terapisId,
            'nama_potongan' => $namaPotongan,
            'nominal'       => $nominal,
            'is_active'     => 1,
            'created_by'    => $userId,
        ]) !== false;
    }
}
