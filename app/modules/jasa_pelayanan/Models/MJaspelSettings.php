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
     * Get setting by region
     */
    public function getByRegion($regionId)
    {
        return $this->where('region_id', $regionId)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Get all settings with region name
     */
    public function getAllWithRegion()
    {
        return $this->select('jaspel_settings.*, regions.name as region_name')
                    ->join('regions', 'regions.id = jaspel_settings.region_id')
                    ->where('jaspel_settings.is_active', 1)
                    ->where('regions.is_active', 1)
                    ->findAll();
    }

    /**
     * Save or update setting for a region
     */
    public function saveSettings($regionId, $data)
    {
        $existing = $this->where('region_id', $regionId)->first();
        
        if ($existing) {
            // Update existing
            return $this->update($existing->id, $data);
        } else {
            // Insert new
            $data['region_id'] = $regionId;
            return $this->insert($data);
        }
    }
}
