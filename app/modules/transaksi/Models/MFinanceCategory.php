<?php

namespace App\Modules\transaksi\Models;

use CodeIgniter\Model;

class MFinanceCategory extends Model
{
    protected $table            = 'finance_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'type', 'region_id', 'is_default'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getCategories($type = null, $regionId = null)
    {
        $builder = $this->builder();
        
        if ($type) {
            $builder->where('type', $type);
        }

        // Hybrid logic: global (region_id IS NULL) OR specific branch
        $builder->groupStart()
                ->where('region_id', null)
                ->orWhere('is_default', 1);
        
        if ($regionId && $regionId !== 'all') {
            // Handle array of region IDs
            if (is_array($regionId)) {
                $builder->orWhereIn('region_id', $regionId);
            } else {
                $builder->orWhere('region_id', $regionId);
            }
        }
        $builder->groupEnd();

        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }
}
