<?php

namespace App\modules\transaksi\Models;

use CodeIgniter\Model;

class MTransaksi extends Model
{
    protected $table            = 'transaksi';
    protected $primaryKey       = 'id_transaksi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['region_id', 'nominal','type', 'keterangan', 'metode_pembayaran', 'rentang_usia', 'created_at', 'created_by', 'status', 'cancel_reason', 'cancelled_by'];

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

    public function get_list_data($options)
    {
        $builder = $this->db->table('transaksi t')
            ->select('t.id_transaksi, t.created_at, t.nominal, t.type, t.metode_pembayaran, t.rentang_usia, r.name as region_name, t.status, t.cancel_reason, u.realname as cancelled_by_name')
            ->join('regions r', 'r.id = t.region_id', 'left')
            ->join('users u', 'u.id = t.cancelled_by', 'left');

        if (!empty($options['where'])) {
            foreach ($options['where'] as $key => $value) {
                $builder->where($key, $value);
            }
        }

        $role = session()->get('role');
        $aktif_region = session()->get('active_region');

        if ($role !== 'superadmin' && $role !== 'owner') {
            $builder->where('t.region_id', session()->get('region_id'));
        } else {
            if ($aktif_region && $aktif_region !== 'all') {
                $builder->where('t.region_id', $aktif_region);
            }
        }

        return $builder->orderBy($options['order'], $options['mode'])
            ->limit($options['limit'], $options['offset'])
            ->get()->getResult();
    }
    public function get_total_data($options)
    {
        $builder = $this->db->table('transaksi t');

        $aktif_region = session()->get('active_region');
        if ($aktif_region && $aktif_region !== 'all') {
            $builder->where('t.region_id', $aktif_region);
        }

        if (!empty($options['where_like'])) {
            foreach ($options['where_like'] as $key => $value) {
                $builder->like($key, $value);
            }
        }

        return $builder->countAllResults();
    }
}
