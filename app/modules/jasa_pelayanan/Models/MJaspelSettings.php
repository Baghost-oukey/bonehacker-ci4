<?php

namespace App\modules\jasa_pelayanan\Models;

use CodeIgniter\Model;

class MJaspelSettings extends Model
{
    protected $table            = 'jaspel_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'region_id',
        'tipe',
        'nominal_per_pasien',
        'terapis_ids',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get setting by region and tipe
     */
    public function getByRegion($regionId, $tipe = 'reguler')
    {
        return $this->where('region_id', $regionId)
                    ->where('tipe', $tipe)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Get all settings with region name, optionally filtered by tipe
     */
    public function getAllWithRegion($tipe = null)
    {
        $query = $this->select('jaspel_settings.*, regions.name as region_name')
                    ->join('regions', 'regions.id = jaspel_settings.region_id')
                    ->where('jaspel_settings.is_active', 1)
                    ->where('regions.is_active', 1);

        if ($tipe) {
            $query->where('jaspel_settings.tipe', $tipe);
        }

        return $query->findAll();
    }

    /**
     * Save or update setting for a region + tipe
     */
    public function saveSettings($regionId, $data, $tipe = 'reguler')
    {
        $existing = $this->where('region_id', $regionId)
                         ->where('tipe', $tipe)
                         ->first();

        if ($existing) {
            return $this->update($existing->id, $data);
        } else {
            $data['region_id'] = $regionId;
            $data['tipe']      = $tipe;
            return $this->insert($data);
        }
    }
}
