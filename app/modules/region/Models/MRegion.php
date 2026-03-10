<?php

namespace App\modules\region\Models;

use CodeIgniter\Model;

class MRegion extends Model
{
    protected $table            = 'regions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'created_at', 'updated_at'];

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

    // Query
    public function querySql()
    {
        return 'SELECT r.id, r.name, r.created_at, r.updated_at, 
        (SELECT COUNT(id) FROM patients WHERE region_id = r.id) 
        as jumlah FROM {$this->table} as r';
    }

    // Get data Yang ada di Dashboard
    public function getData(array $column = null)
    {
        $builder = $this->builder();
        if (isset($column)) {
            $builder->select($column);
        }
        return $builder->get()->getResult();
    }

    public function getListData($options = [])
    {
        // Nilai default 
        $limit      = $options['limit'] ?? 10;
        $offset     = $options['offset'] ?? 0;
        $order      = $options['order'] ?? 'id';
        $mode       = $options['mode'] ?? 'ASC';
        $where_like = empty($option['where_like']) ? '' : 'AND (' . implode(' AND ', $options['where_like']) . ')';

        $sql = $this->querySql() . " WHERE 1 = 1 " . $where_like .
            " GROUP BY r.id ORDER BY " . $order . " " . $mode .
            " LIMIT " . $offset . ", " . $limit;

        return $this->db->query($sql)->getResult();
    }

    public function getTotalData($options = [])
    {
        $where_like = empty($options['where_like']) ? '' : 'AND (' . implode(' AND ', $options['where_like']) . ')';

        $sql = "SELECT COUNT(DISTINCT id) AS total FROM ( ";
        $sql .= $this->querySql();
        $sql .= " GROUP BY r.id) AS temp_table WHERE 1 = 1 " . $where_like;

        $query = $this->db->query($sql)->getRow();

        return $query ? $query->total : 0;
    }
}
