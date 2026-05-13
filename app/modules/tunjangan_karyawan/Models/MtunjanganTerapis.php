<?php

namespace App\modules\tunjangan_karyawan\Models;

use CodeIgniter\Model;

class MtunjanganTerapis extends Model
{
    protected $table         = 'tunjangan_terapis';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'terapis_id', 'tunjangan_karyawan_id', 'nominal',
        'tipe', 'is_active', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua setting tunjangan untuk satu terapis
     */
    public function getByTerapis(int $terapisId): array
    {
        return $this->select('tunjangan_terapis.*, tunjangan_karyawan.nama_tunjangan, tunjangan_karyawan.kategori')
                    ->join('tunjangan_karyawan', 'tunjangan_karyawan.id = tunjangan_terapis.tunjangan_karyawan_id')
                    ->where('terapis_id', $terapisId)
                    ->where('is_active', 1)
                    ->orderBy('tipe', 'ASC')
                    ->findAll();
    }

    /**
     * Hitung total tunjangan untuk proses gaji
     * @param int $terapisId
     * @param int $jumlahHadir  jumlah hari hadir bulan ini
     * @return array ['total' => int, 'detail' => array]
     */
    public function hitungTunjangan(int $terapisId, int $jumlahHadir): array
    {
        $settings = $this->getByTerapis($terapisId);
        $total    = 0;
        $detail   = [];

        foreach ($settings as $s) {
            if ($s['tipe'] === 'bulanan') {
                $nominal = (float) $s['nominal'];
            } else {
                // harian = nominal × hari hadir
                $nominal = (float) $s['nominal'] * $jumlahHadir;
            }

            $total += $nominal;
            $detail[] = [
                'nama'    => $s['nama_tunjangan'],
                'tipe'    => $s['tipe'],
                'nominal' => $nominal,
            ];
        }

        return ['total' => (int) $total, 'detail' => $detail];
    }

    /**
     * Simpan atau update setting tunjangan terapis
     */
    public function saveSetting(int $terapisId, int $tunjanganId, float $nominal, string $tipe, int $userId): bool
    {
        $existing = $this->where('terapis_id', $terapisId)
                         ->where('tunjangan_karyawan_id', $tunjanganId)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'nominal'   => $nominal,
                'tipe'      => $tipe,
                'is_active' => 1,
            ]);
        }

        return $this->insert([
            'terapis_id'            => $terapisId,
            'tunjangan_karyawan_id' => $tunjanganId,
            'nominal'               => $nominal,
            'tipe'                  => $tipe,
            'is_active'             => 1,
            'created_by'            => $userId,
        ]) !== false;
    }
}
