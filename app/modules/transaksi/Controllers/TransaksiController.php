<?php

namespace App\Modules\Transaksi\Controllers;

use App\Controllers\BaseController;
use App\modules\transaksi\Models\MTransaksi;
use CodeIgniter\HTTP\ResponseInterface;

class TransaksiController extends BaseController
{
    protected $model_transaksi;

    public function __construct()
    {
        $this->model_transaksi = new MTransaksi();
    }
    public function index()
    {
        $db = \Config\Database::connect();
        $role = session()->get('role');
        $active_region = session()->get('active_region');
        $region_session = session()->get('region_id');
        $list_regions = $db->table('regions')->select('id, name')->get()->getResultArray();
        if ($role === 'superadmin' || $role === 'owner') {
            $filter_region = ($active_region !== 'all') ? $active_region : null;
        } else {
            $filter_region = $region_session;
        }


        // Hitung Uang Masuk Hari Ini
        $todayIncomeBuilder = $db->table('transaksi')->selectSum('nominal')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('type', 'income');
        if ($filter_region) $todayIncomeBuilder->where('region_id', $filter_region);
        $in_today = $todayIncomeBuilder->get()->getRow()->nominal ?? 0;

        // Hitung Uang Keluar Hari Ini
        $todayExpenseBuilder = $db->table('transaksi')->selectSum('nominal')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('type', 'expense');
        if ($filter_region) $todayExpenseBuilder->where('region_id', $filter_region);
        $out_today = $todayExpenseBuilder->get()->getRow()->nominal ?? 0;

        // Saldo = Pemasukan - Pengeluaran
        $today_balance = $in_today - $out_today;


        // --- 2. Total Income (Akumulasi Selamanya) ---
        $incomeBuilder = $db->table('transaksi')->selectSum('nominal')->where('type', 'income'); 
        if ($filter_region) $incomeBuilder->where('region_id', $filter_region);
        $total_income = $incomeBuilder->get()->getRow()->nominal ?? 0;


        // --- 3. Total Expense (Akumulasi Selamanya) ---
        $total_expense = 0;
        if ($role === 'superadmin' || $role === 'owner') {
            $expenseBuilder = $db->table('transaksi')->selectSum('nominal')->where('type', 'expense');
            if ($filter_region) $expenseBuilder->where('region_id', $filter_region); 
            $total_expense = $expenseBuilder->get()->getRow()->nominal ?? 0;
        }

        $data = [
            'title' => 'Transaksi',
            'realname' => session()->get('realname'),
            'role' => $role,
            'today_balance' => $today_balance,
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'active_region' => $active_region,
            'list_regions' => $list_regions
        ];
        return view('\App\Modules\Transaksi\Views\index', $data);
    }

    public function fetch()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $order = $this->request->getPost('order');
        $columns = $this->request->getPost('columns');

        $options = [
            'order' => (!empty($order) && !empty($columns)) ? ($columns[$order[0]['column']]['name'] ?: $columns[$order[0]['column']]['data']) : 'created_at',
            'mode' => (!empty($order)) ? $order[0]['dir'] : 'desc',
            'offset' => $start ?? 0,
            'limit' => $length ?? 10
        ];

        $dataOutput = $this->model_transaksi->get_list_data($options);
        $totalData = $this->model_transaksi->get_total_data($options);
        $no = ($start ?? 0) + 1;
        foreach ($dataOutput as $value) {
            $value->no = $no++;
            $value->nominal_format = "Rp " . number_format($value->nominal, 0, ',', '.');
            $value->tanggal = date('d/m/Y H:i', strtotime($value->created_at));
            $value->aksi = '
            <button class="btn btn-danger btn-sm btn-delete" data-id="' . $value->id_transaksi . '">
                <i class="fas fa-eye"></i>
            </button>';
        }

        return $this->response->setJSON([
            "draw" => intval($draw),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data" => $dataOutput,
            "csrfHash" => csrf_hash()
        ]);
    }

    public function store()
    {
        $role = session()->get('role');
        $akitf_region = session()->get("active_region");

        if ($role === 'superadmin' || $role === 'owner') {
            $region_id = $this->request->getPost('region_id');
            if (empty($region_id) && $akitf_region !== 'all') {
                $region_id = $akitf_region;
            }
        } else {
            $region_id = session()->get('region_id');
        }

        if (empty($region_id) || $region_id === 'all') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal: Cabang tidak terdeteksi atau belum dipilih!'
            ]);
        }

        $typeInput = $this->request->getPost('type') ?? 'income';
        $kategoriAuto = ($typeInput === 'income') ? 'pemasukan' : 'pengeluaran';

        $data = [
            'region_id' => $region_id,
            'nominal' => $this->request->getPost('nominal'),
            'type' => $this->request->getPost('type') ?? 'income',
            'keterangan' => $this->request->getPost('keterangan'),
            // 'keterangan' => $typeInput,
            'kategori' =>  $kategoriAuto,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('userId')
        ];

        try {
            if ($this->model_transaksi->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data']);
    }

    public function delete()
    {
        $id = $this->request->getPost('id_transaksi');
        if ($this->model_transaksi->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil dihapus']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
    }
}
