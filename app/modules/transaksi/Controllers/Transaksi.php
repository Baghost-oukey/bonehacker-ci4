<?php

namespace App\modules\transaksi\Controllers;

use App\Controllers\BaseController;
use App\modules\transaksi\Models\MTransaksi;
use CodeIgniter\HTTP\ResponseInterface;

class Transaksi extends BaseController
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


        // --- 1. Saldo Hari Ini (Reset Tiap Hari) ---
        $todayBuilder = $db->table('transaksi')->selectSum('nominal');
        $todayBuilder->where('DATE(created_at)', date('Y-m-d'));
        if ($filter_region) $todayBuilder->where('region_id', $filter_region);
        $today_balance = $todayBuilder->get()->getRow()->nominal ?? 0;

        // --- 2. Total Income (Akumulasi Selamanya) ---
        $incomeBuilder = $db->table('transaksi')->selectSum('nominal');
        if ($filter_region) $incomeBuilder->where('region_id', $filter_region);
        $total_income = $incomeBuilder->get()->getRow()->nominal ?? 0;

        // --- 3. Total Expense (Global - Hanya Owner/Superadmin) ---
        $total_expense = 0;
        if ($role === 'superadmin' || $role === 'owner') {
            // Contoh: Ambil dari tabel pengeluaran jika ada, atau filter type='expense'
            $total_expense = $db->table('transaksi')->selectSum('nominal')->where('type', 'expense')->get()->getRow()->nominal ?? 0;
        }

        $data = [
            'title'          => 'Dashboard Keuangan',
            'realname'       => session()->get('realname'),
            'role'           => $role,
            'today_balance'  => $today_balance,
            'total_income'   => $total_income,
            'total_expense'  => $total_expense,
            'active_region'  => $active_region,
            'list_regions'   => $list_regions
        ];
        return view('\App\modules\transaksi\Views\views_transaksi', $data);
    }

    public function fetch()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        // Ganti 'other' menjadi 'order'
        $order = $this->request->getPost('order');
        $columns = $this->request->getPost('columns');

        $options = [
            // Pastikan variabel $order digunakan di sini
            'order'  => (!empty($order) && !empty($columns)) ? ($columns[$order[0]['column']]['name'] ?: $columns[$order[0]['column']]['data']) : 'created_at',
            'mode'   => (!empty($order)) ? $order[0]['dir'] : 'desc',
            'offset' => $start ?? 0,
            'limit'  => $length ?? 10
        ];

        $dataOutput = $this->model_transaksi->get_list_data($options);
        $totalData  = $this->model_transaksi->get_total_data($options);

        $no = ($start ?? 0) + 1;
        foreach ($dataOutput as $value) {
            $value->no = $no++;
            // Gunakan nominal_format sesuai yang dipanggil di View (data: 'nominal_format')
            $value->nominal_format = "Rp " . number_format($value->nominal, 0, ',', '.');
            $value->tanggal = date('d/m/Y H:i', strtotime($value->created_at));

            $value->aksi = '
            <button class="btn btn-danger btn-sm btn-delete" data-id="' . $value->id_transaksi . '">
                <i class="fas fa-trash"></i>
            </button>';
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data"            => $dataOutput,
            "csrfHash"        => csrf_hash()
        ]);
    }

    public function store()
    {
        $role = session()->get('role');
        $akitf_region = session()->get("active_region");


        // Proteksi Region
        if ($role === 'superadmin' || $role === 'owner') {
            $region_id = $this->request->getPost('region_id');

            if (empty($region_id) && $akitf_region !== 'all') {
                $region_id = $akitf_region;
            }
        } else {
            // Admin Cabang terkunci ke wilayah aktif mereka
            $region_id = session()->get('region_id');
        }

        // Cek jika masih dalam mode 'all'
        if (empty($region_id) || $region_id === 'all') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal: Cabang tidak terdeteksi atau belum dipilih!'
            ]);
        }

        $data = [
            'region_id'         => $region_id,
            'nominal'           => $this->request->getPost('nominal'),
            'type'              => $this->request->getPost('type') ?? 'income',
            'keterangan'        => $this->request->getPost('keterangan'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'rentang_usia'      => $this->request->getPost('rentang_usia'),
            'created_at'        => date('Y-m-d H:i:s'),
            'created_by'        => session()->get('userId')
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

        // Opsi: Tambahkan pengecekan role di sini jika hanya Superadmin yang boleh hapus
        if ($this->model_transaksi->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
    }
}
