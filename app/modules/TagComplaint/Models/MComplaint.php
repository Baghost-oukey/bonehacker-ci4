<?php

namespace App\modules\TagComplaint\Models;

use CodeIgniter\Model;

class MComplaint extends Model
{
    protected $table            = 'complaint_tags';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
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

    public function getComplaintTags()
    {
        $subQuery = "(SELECT COUNT(*) FROM histories h WHERE FIND_IN_SET(complaint_tags.id, h.complaint))";

        return $this->builder()
            ->select('id, name as nama, description as deskripsi')
            ->select("$subQuery as jumlah", false);
    }

    public function get_all_tags()
    {
        return $this->select('id, name')->findAll();
    }

    public function store($data)
    {
        $this->insert($data);
        return $this->db->insertID();
    }

    public function destroy($tagId)
    {
        $this->db->transStart();
        $this->delete($tagId);
        $this->db->table('histories')
            ->set('complaint', "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', complaint, ','), ',$tagId,', ','))", false)
            ->where("FIND_IN_SET('$tagId', complaint) >", 0)
            ->update();

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
