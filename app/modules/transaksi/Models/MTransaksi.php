<?php

namespace App\modules\transaksi\Models;

use CodeIgniter\Model;

class MTransaksi extends Model
{
    protected $table            = 'transaksi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'region_id',
        'nominal',
        'type',
        'kategori',
        'keterangan',
        'status',
        'cancel_reason',
        'created_at',
        'created_by',
        'cancelled_by'
    ];

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
            ->select('t.id_transaksi, t.created_at, t.nominal, t.type, t.kategori, t.keterangan, r.name as region_name')
            ->join('regions r', 'r.id = t.region_id', 'left');

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

    public function get_dashboard_stats($filter_region = null)
    {
        $db = \Config\Database::connect();

        // --- Saldo Hari Ini ---
        $todayBuilder = $db->table($this->table)->selectSum('nominal')->where('DATE(created_at)', date('Y-m-d'));
        if ($filter_region) $todayBuilder->where('region_id', $filter_region);
        $today_balance = $todayBuilder->get()->getRow()->nominal ?? 0;

        // --- Total Pemasukan ---
        $incomeBuilder = $db->table($this->table)->selectSum('nominal')->where('type', 'income');
        if ($filter_region) $incomeBuilder->where('region_id', $filter_region);
        $total_income = $incomeBuilder->get()->getRow()->nominal ?? 0;

        // --- Total Pengeluaran ---
        $expenseBuilder = $db->table($this->table)->selectSum('nominal')->where('type', 'expense');
        if ($filter_region) $expenseBuilder->where('region_id', $filter_region);
        $total_expense = $expenseBuilder->get()->getRow()->nominal ?? 0;

        return [
            'today_balance' => $today_balance,
            'total_income'  => $total_income,
            'total_expense' => $total_expense
        ];
    }
}
