<?php

namespace App\modules\rank_terapis\Models;

use CodeIgniter\Model;

class MRankTerapis extends Model
{
    protected $table            = 'rank_terapis';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at'];

    protected $useTimestamps = false;

    public function getData(bool $activeOnly = false): array
    {
        $builder = $this->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->findAll();
    }

    public function existsByName(string $name, ?int $ignoreId = null): bool
    {
        $builder = $this->where('name', $name);

        if ($ignoreId) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
