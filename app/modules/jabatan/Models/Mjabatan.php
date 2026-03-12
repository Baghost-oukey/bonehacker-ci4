<?php

namespace App\modules\jabatan\Models;

use CodeIgniter\Model;

class Mjabatan extends Model
{
    protected $table            = 'jabatan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_jabatan', 'deskripsi'];

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

    public function getJabatan()
    {
        return $this->builder()->select('id, nama_jabatan, deskripsi');
    }

    public function checkNameExists($name, $id = null)
    {
        $builder = $this->where('nama_jabatan', $name);
        if ($id) {
            $builder->where('id !=', $id);
        }
        
        return $builder->countAllResults() > 0;
    }

    public function store($data)
    {
        $this->insert($data);
        return $this->db->insertID();
    }

    public function edit($data, $where)
    {
        return $this->update($where, $data);
    }

    public function destroy($where)
    {
        return $this->where($where)->delete();
    }

    public function getData(array $column = null)
    {
        if ($column) {
            $this->select($column);
        }

        return $this->findAll();
    }
}
