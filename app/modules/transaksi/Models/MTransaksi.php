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
    protected $allowedFields    = [
        'region_id', 'category_id', 'nominal', 'type', 'kas_type', 'kategori', 'keterangan', 
        'status', 'cancel_reason', 'created_at', 'created_by', 'cancelled_by'
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

    public function get_list_data($options, $kategori = null, $region_id = null)
    {
        $builder = $this->db->table('transaksi t')
            ->select('t.id_transaksi, t.created_at, t.nominal, t.type, t.kas_type, t.kategori, t.keterangan, r.name as region_name, t.status, t.cancel_reason, u_created.username as nama_pembuat, u_cancelled.realname as cancelled_by_name, c.name as category_name')
            ->join('regions r', 'r.id = t.region_id', 'left')
            ->join('users u_created', 'u_created.id = t.created_by', 'left')
            ->join('users u_cancelled', 'u_cancelled.id = t.cancelled_by', 'left')
            ->join('finance_categories c', 'c.id = t.category_id', 'left');

        if (!empty($options['where'])) {
            foreach ($options['where'] as $key => $value) {
                $builder->where($key, $value);
            }
        }

        if ($kategori) {
            $builder->where('t.kategori', $kategori);
        }

        $region_patient = session()->get('region_patient');

        if ($region_patient !== 'all' && !empty($region_patient)) {
            if (is_array($region_patient)) {
                $builder->whereIn('t.region_id', $region_patient);
            } else {
                $builder->where('t.region_id', $region_patient);
            }
        }

        return $builder->orderBy($options['order'], $options['mode'])
            ->limit($options['limit'], $options['offset'])
            ->get()->getResult();
    }
    public function get_total_data($options, $kategori = null, $region_id = null)
    {
        $builder = $this->db->table('transaksi t');

        if (!empty($options['where'])) {
            foreach ($options['where'] as $key => $value) {
                $builder->where($key, $value);
            }
        }

        if ($kategori) {
            $builder->where('t.kategori', $kategori);
        }
        $region_patient = session()->get('region_patient');

        if ($region_patient !== 'all' && !empty($region_patient)) {
            if (is_array($region_patient)) {
                $builder->whereIn('t.region_id', $region_patient);
            } else {
                $builder->where('t.region_id', $region_patient);
            }
        }

        if (!empty($options['where_like'])) {
            foreach ($options['where_like'] as $key => $value) {
                $builder->like($key, $value);
            }
        }

        return $builder->countAllResults();
    }

    public function get_saldo_kas($kas_type, $filter_region = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table($this->table)->select('type, SUM(nominal) as total');
        $builder->where('status', 'active');
        $builder->where('kas_type', $kas_type);
        
        if ($filter_region && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $builder->whereIn('region_id', $filter_region);
            } else {
                $builder->where('region_id', $filter_region);
            }
        }
        
        $result = $builder->groupBy('type')->get()->getResultArray();
        
        $totals = ['income' => 0, 'expense' => 0, 'mutasi_in' => 0, 'mutasi_out' => 0];
        foreach ($result as $row) {
            $totals[$row['type']] = (float) $row['total'];
        }
        
        // Saldo = (Pemasukan + Setoran Masuk) - (Pengeluaran + Setoran Keluar)
        $saldo = ($totals['income'] + $totals['mutasi_in']) - ($totals['expense'] + $totals['mutasi_out']);
        
        return $saldo;
    }

    public function get_dashboard_stats($filter_region = null)
    {
        $db = \Config\Database::connect();
        $hari_ini = date('Y-m-d');

        // --- 1. Hitung Uang Masuk Hari Ini ---
        $todayIncomeBuilder = $db->table($this->table)->selectSum('nominal')
            ->where('DATE(created_at)', $hari_ini)
            ->where('type', 'income');
        if ($filter_region && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $todayIncomeBuilder->whereIn('region_id', $filter_region);
            } else {
                $todayIncomeBuilder->where('region_id', $filter_region);
            }
        }
        $in_today = $todayIncomeBuilder->get()->getRow()->nominal ?? 0;

        // --- 2. Hitung Uang Keluar Hari Ini ---
        $todayExpenseBuilder = $db->table($this->table)->selectSum('nominal')
            ->where('DATE(created_at)', $hari_ini)
            ->where('type', 'expense');
        if ($filter_region && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $todayExpenseBuilder->whereIn('region_id', $filter_region);
            } else {
                $todayExpenseBuilder->where('region_id', $filter_region);
            }
        }
        $out_today = $todayExpenseBuilder->get()->getRow()->nominal ?? 0;

        // --- 3. SALDO HARI INI (Pemasukan - Pengeluaran) ---
        $today_balance = $in_today - $out_today;

        // --- 4. TOTAL PENDAPATAN (Akumulasi Selamanya) ---
        $incomeBuilder = $db->table($this->table)->selectSum('nominal')->where('type', 'income');
        if ($filter_region && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $incomeBuilder->whereIn('region_id', $filter_region);
            } else {
                $incomeBuilder->where('region_id', $filter_region);
            }
        }
        $total_income = $incomeBuilder->get()->getRow()->nominal ?? 0;

        // --- 5. TOTAL PENGELUARAN (Akumulasi Selamanya) ---
        $expenseBuilder = $db->table($this->table)->selectSum('nominal')->where('type', 'expense');
        if ($filter_region && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $expenseBuilder->whereIn('region_id', $filter_region);
            } else {
                $expenseBuilder->where('region_id', $filter_region);
            }
        }
        $total_expense = $expenseBuilder->get()->getRow()->nominal ?? 0;

        return [
            'today_balance' => $today_balance,
            'today_income'  => $in_today,
            'today_expense' => $out_today,
            'total_income'  => $total_income,
            'total_expense' => $total_expense
        ];
    }

    public function getFinanceTrend($days = 7, $regionId = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select("DATE(created_at) as tanggal");
        $builder->selectSum("CASE WHEN type = 'income' THEN nominal ELSE 0 END", "pemasukan", false);
        $builder->selectSum("CASE WHEN type = 'expense' THEN nominal ELSE 0 END", "pengeluaran", false);
        $startDate = date('Y-m-d 00:00:00', strtotime("-" . ($days - 1) . " days"));
        $builder->where('created_at >=', $startDate);

        if ($regionId && $regionId !== 'all') {
            if (is_array($regionId)) {
                $builder->whereIn('region_id', $regionId);
            } else {
                $builder->where('region_id', $regionId);
            }
        }
        $builder->groupBy("DATE(created_at)");
        $builder->orderBy("tanggal", "ASC");

        return $builder->get()->getResultArray();
    }

    public function getExpenseStructure($days = 30, $filter_region = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select("kategori, SUM(nominal) as total");
        $builder->where('type', 'expense');
        $builder->where('created_at >=', date('Y-m-d 00:00:00', strtotime("-$days days")));
        if ($filter_region && $filter_region !== 'all') {
            if (is_array($filter_region)) {
                $builder->whereIn('region_id', $filter_region);
            } else {
                $builder->where('region_id', $filter_region);
            }
        }
        $builder->groupBy("kategori");
        $builder->orderBy("total", "DESC");

        return $builder->get()->getResultArray();
    }
}
