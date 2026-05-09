<?php

namespace App\modules\region\Models;

use CodeIgniter\Model;

class MRegion extends Model
{
    protected $table            = 'regions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'created_at', 'updated_at'];

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


    public function querySql()
    {
        // return "SELECT r.id, r.name, r.created_at, r.updated_at, 
        //     (SELECT COUNT(id) FROM patients WHERE region_id = r.id) as jumlah 
        //     FROM " . $this->table . " as r ";
        return "SELECT r.id, r.name, r.created_at, r.updated_at, 
            COUNT(p.id) as jumlah 
            FROM " . $this->table . " as r 
            LEFT JOIN patients as p ON p.region_id = r.id ";
    }

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

        $limit      = $options['limit'] ?? 10;
        $offset     = $options['offset'] ?? 0;
        $order      = $options['order'] ?? 'r.id';
        $mode       = $options['mode'] ?? 'ASC';

        $builder    = $this->db->table('regions as r');
        $builder->select('r.id, r.name, r.created_at, r.updated_at, COUNT(p.id) as jumlah');
        $builder->join('patients as p', 'p.region_id = r.id', 'left');

        // $sql = $this->querySql();
        // $where = " WHERE 1 = 1";

        if (!empty($options['where_like'])) {
            foreach ($options['where_like'] as $like) {
               $builder->where($like);
            }
        }

        if (!empty($options['where_in'])) {
            $builder->whereIn('r.id', $options['where_in']);
        }

        // $sql .= $where;
        // $sql .= " GROUP BY r.id";
        // $sql .= " ORDER BY $order $mode";
        // $sql .= " LIMIT $offset, $limit";
        $builder->groupBy('r.id');
        $builder->orderBy($order, $mode);

        // return $this->db->query($sql)->getResult();
        return $builder->get($limit, $offset)->getResult();
    }

    public function getTotalData($options = [])
    {
        $builder = $this->db->table('regions as r');

        // $where_like = "";
        // if (!empty($options['where_like'])) {
        //     $where_like = ' AND (' . implode(' AND ', $options['where_like']) . ')';
        // }

        if (!empty($options['where_like'])) {
            foreach ($options['where_like'] as $like) {
                // Kita gunakan Query Builder agar lebih aman dan cepat
                $builder->where($like);
            }
        }

        if (!empty($options['where_in'])) {
            $builder->whereIn('r.id', $options['where_in']);
        }
        // $sql = "SELECT COUNT(DISTINCT id) AS total FROM ( ";
        // $sql .= $this->querySql();
        // $sql .= ") AS temp_table WHERE 1 = 1 " . $where_like;
        // $query = $this->db->query($sql)->getRow();
        // return $query ? (int)$query->total : 0;
        return $builder->countAllResults();
    }

    public function getTotal($options = [])
    {
        $builder = $this->builder();
        if (!empty($options['where_in'])) {
            $builder->whereIn('id', $options['where_in']);
        }
        return $builder->countAllResults();
    }
}
