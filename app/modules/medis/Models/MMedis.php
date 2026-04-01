<?php

namespace App\modules\medis\Models;

use CodeIgniter\Model;

class MMedis extends Model
{
    protected $table            = 'medhis_tags';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'created_at', 'updated_at'];

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

    public function checkNameExists($name, $id = null)
    {
        $builder = $this->where('name', $name);
        if ($id) {
            $builder->where('id !=', $id);
        }
        return $builder->countAllResults() > 0;
    }

    public function getmedhisTags()
    {
        $subQuery = "(SELECT COUNT(*) FROM histories h WHERE FIND_IN_SET(medhis_tags.id, h.medhis))";

        return $this->builder()
            ->select('id, name as nama, description as deskripsi')
            ->select("$subQuery as jumlah", false);
    }

    // public function get_all_tags()
    // {
    //     return $this->select('id, name')->findAll();
    // }

    // Limit ambil medhis nya
    public function get_all_tags()
    {
        $builder =  $this->select('id, name');

        if (!empty($query)) {
            $builder->like('name', $query);
        }

        return $builder->limit(15)->findAll();
    }

    public function store($data)
    {
        $this->insert($data);
        return $this->db->insertID();
    }

    public function updateTag($id, $data)
    {
        return $this->update($id, $data);
    }

    public function destroy($tagId)
    {
        $this->db->transStart();
        $this->delete($tagId);
        $this->db->table('histories')
            ->set('medhis', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', medhis, ','), ',$tagId,', ','))", false)
            ->where("FIND_IN_SET('$tagId', medhis) >", 0)
            ->update();

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
