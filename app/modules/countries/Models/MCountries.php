<?php

namespace App\modules\countries\Models;

use CodeIgniter\Model;

class MCountries extends Model
{
    protected $table            = 'countries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['country'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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

    private function _getBaseQuery()
    {
        return $this->db->table($this->table)->select('id, country');
    }

    public function getListData(array $options = [])
    {
        $builder = $this->_getBaseQuery();
        if (!empty($options['where_like'])) {
            foreach ($options['where_like'] as $like) {

                $builder->where($like);
            }
        }
        return $builder->orderBy($options['order'], $options['mode'])
            ->limit($options['limit'], $options['offset'])
            ->get()
            ->getResult();
    }

    public function getTotalData()
    {
        $builder = $this->db->table($this->table);

        if (!empty($options['where_like'])) {
            foreach ($options['where_like'] as $like) {
                $builder->where($like);
            }
        }

        return $builder->countAllResults();
    }

    public function getData()
    {
        $builder = $this->db->table($this->table);
        if (isset($column)) {
            $builder->select($column);
        }
        return $builder->get()->getResult();
    }

    public function show($id)
    {
        return $this->where('id', $id)->first();
    }
}
