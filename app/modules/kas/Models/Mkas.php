<?php

namespace App\modules\kas\Models;

use CodeIgniter\Model;

class Mkas extends Model
{
    protected $table            = 'kas';
    protected $primaryKey       = 'id_harian';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_pengeluaran',
        'nominal_default',
        'status',
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



    private function _get_datatables_query($options)
    {
        $builder = $this->db->table($this->table);
        $builder->select('id_harian, nama_pengeluaran, nominal_default, status, created_at');
        if (!empty($options['where_like'])) {
            $builder->groupStart();
            foreach ($options['where_like'] as $key => $value) {
                if ($key === array_key_first($options['where_like'])) {
                    $builder->like($key, $value);
                } else {
                    $builder->orLike($key, $value);
                }
            }
            $builder->groupEnd();
        }
        return $builder;
    }


    // --- AMBIL DATA ---
    public function get_list_data($options)
    {
        $builder = $this->_get_datatables_query($options);
        return $builder->orderBy($options['order'], $options['mode'])
            ->limit($options['limit'], $options['offset'])
            ->get()->getResult();
    }

    // --- GET DATA UNTUK PAGINATION ---
    public function get_total_data($options)
    {
        $builder = $this->_get_datatables_query($options);
        return $builder->countAllResults();
    }
}
