<?php

namespace App\modules\tunjangan_karyawan\Models;

use CodeIgniter\Model;

class Mtunjangankaryawan extends Model
{
    protected $table            = 'tunjangan_karyawan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_tunjangan',
        'kategori',
        'nominal',
        'tipe',
        'terapis_ids',
        'region_id',
    ];

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

    public function getByKategori(string $kategori): array
    {
        return $this->where('kategori', $kategori)
            ->orderBy('nama_tunjangan', 'ASC')
            ->findAll();
    }

    public function getByRegion($regionId): array
    {
        return $this->where('region_id', $regionId)
                    ->orderBy('kategori', 'ASC')
                    ->orderBy('nama_tunjangan', 'ASC')
                    ->findAll();
    }

    public function getForTerapis(int $terapisId, $regionId): array
    {
        $all = $this->getByRegion($regionId);
        return array_values(array_filter($all, function($item) use ($terapisId) {
            $ids = json_decode($item['terapis_ids'] ?? '[]', true);
            return in_array($terapisId, $ids);
        }));
    }
}
