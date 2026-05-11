<?php

namespace App\modules\kas\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\kas\Models\Mkas;
use App\modules\transaksi\Models\MTransaksi;

class Kas extends BaseController
{

    protected $mTransaksiKas;
    protected $mkasHarian;


    public function __construct()
    {
        $this->mTransaksiKas = new MTransaksi();
        $this->mkasHarian = new Mkas();
    }


    public function index()
    {
        $db = \Config\Database::connect();
        $role = session()->get('role');
        if ($role !== 'owner' && $role !== 'superadmin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Fitur khusus manajemen.');
        }

        $region_patient = session()->get('region_patient');
        $filter_region = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        $stats = $this->mTransaksiKas->get_dashboard_stats($filter_region);

        $db = \Config\Database::connect();
        $regionsBuilder = $db->table('regions')->select('id, name')->where('is_active', 1);
        if ($filter_region) {
            if (is_array($filter_region)) { $regionsBuilder->whereIn('id', $filter_region); }
            else { $regionsBuilder->where('id', $filter_region); }
        }

        $mCategory = new \App\Modules\transaksi\Models\MFinanceCategory();
        $categories_income  = $mCategory->getCategories('income', $filter_region);
        $categories_expense = $mCategory->getCategories('expense', $filter_region);

        $data = [
            'title'              => 'Manajemen Arus Kas',
            'role'               => $role,
            'list_regions'       => $regionsBuilder->get()->getResultArray(),
            'categories_income'  => $categories_income,
            'categories_expense' => $categories_expense,
            'today_balance'      => $stats['today_balance'] ?? 0,
            'today_income'       => $stats['today_income'] ?? 0,
            'today_expense'      => $stats['today_expense'] ?? 0,
            'total_income'       => $stats['total_income'] ?? 0,
            'total_expense'      => $stats['total_expense'] ?? 0,
        ];
        return view('\App\modules\kas\Views\index', $data);
    }


    public function get_data_pemasukan()
    {
        $options = $this->_get_datatables_options();
        $region_id = session()->get('active_region') !== 'all' ? session()->get('active_region') : session()->get('region_id');


        $list_data  = $this->mTransaksiKas->get_list_data($options, 'pemasukan', $region_id);
        $total_data = $this->mTransaksiKas->get_total_data($options, 'pemasukan', $region_id);
        return $this->_format_datatables_response($list_data, $total_data);
    }


    public function get_data_pengeluaran()
    {
        $options = $this->_get_datatables_options();
        $region_id = session()->get('active_region') !== 'all' ? session()->get('active_region') : session()->get('region_id');

        $list_data  = $this->mTransaksiKas->get_list_data($options, 'pengeluaran', $region_id);
        $total_data = $this->mTransaksiKas->get_total_data($options, 'pengeluaran', $region_id);

        return $this->_format_datatables_response($list_data, $total_data);
    }




    public function get_data_pengeluaran_harian()
    {
        $options = $this->_get_datatables_options();

        $region_id = session()->get('active_region') !== 'all' ? session()->get('active_region') : session()->get('region_id');
        $list_data  = $this->mTransaksiKas->get_list_data($options, 'pengeluaran_harian', $region_id);
        $total_data = $this->mTransaksiKas->get_total_data($options, 'pengeluaran_harian', $region_id);
        return $this->_format_datatables_response($list_data, $total_data);
    }


    public function get_master_harian()
    {
        $data = $this->mkasHarian->orderBy('nama_pengeluaran', 'ASC')->findAll();
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }


    public function simpan_transaksi()
    {
        $kategori   = $this->request->getPost('kategori');
        $nominal    = $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');
        $region_id  = session()->get('active_region') !== 'all' ? session()->get('active_region') : session()->get('region_id');

        if (empty($nominal) || empty($keterangan) || empty($kategori)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Semua kolom wajib diisi.']);
        }

        $type = ($kategori === 'pemasukan') ? 'income' : 'expense';
        $data_simpan = [
            'region_id'   => $region_id,
            'category_id' => $this->request->getPost('category_id'),
            'type'        => $type,
            'kategori'    => $kategori,
            'nominal'     => str_replace(['Rp', '.', ','], '', $nominal),
            'keterangan'  => $keterangan,
            'status'      => 'active',
            'created_by'  => session()->get('id_user'),
        ];

        if ($this->mTransaksiKas->insert($data_simpan)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil dicatat.']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan transaksi.']);
    }


    public function bayar_pengeluaran_harian()
    {
        $id_harian = $this->request->getPost('id_harian');
        $master = $this->mkasHarian->find($id_harian);
        if (!$master || $master['status'] === 'Non-Aktif') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan atau sedang non-aktif.']);
        }

        $region_id = session()->get('active_region') !== 'all' ? session()->get('active_region') : session()->get('region_id');
        $data_simpan = [
            'region_id'  => $region_id,
            'type'       => 'expense',
            'kategori'   => 'pengeluaran_harian',
            'nominal'    => $master['nominal_default'],
            'keterangan' => 'Pembayaran Rutin: ' . $master['nama_pengeluaran'],
            'status'     => 'active',
            'created_by' => session()->get('id_user'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->mTransaksiKas->insert($data_simpan)) {
            return $this->response->setJSON(['status' => 'success', 'message' => $master['nama_pengeluaran'] . ' berhasil dibayarkan.']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses pembayaran.']);
    }


    public function simpan_master_harian()
    {
        $id_harian        = $this->request->getPost('id_harian');
        $nama_pengeluaran = $this->request->getPost('nama_pengeluaran');
        $nominal_default  = str_replace(['Rp', '.', ','], '', $this->request->getPost('nominal_default'));
        if (empty($nama_pengeluaran) || empty($nominal_default)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama dan Nominal wajib diisi.']);
        }

        $data = [
            'nama_pengeluaran' => $nama_pengeluaran,
            'nominal_default'  => $nominal_default,
        ];
        if ($id_harian) {
            $this->mkasHarian->update($id_harian, $data);
            $msg = 'Data master berhasil diperbarui.';
        } else {
            $data['status'] = 'Aktif';
            $this->mkasHarian->insert($data);
            $msg = 'Data master berhasil ditambahkan.';
        }
        return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
    }


    public function hapus_master_harian()
    {
        $id_harian = $this->request->getPost('id_harian');
        if ($this->mkasHarian->delete($id_harian)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data master berhasil dihapus.']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }


    private function _get_datatables_options()
    {
        $limit  = $this->request->getPost('length') ?? 10;
        $offset = $this->request->getPost('start') ?? 0;
        $search = $this->request->getPost('search')['value'] ?? '';
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'DESC';

        $options = [
            'limit'      => $limit,
            'offset'     => $offset,
            'order'      => 't.created_at',
            'mode'       => $orderDir,
            'where_like' => []
        ];

        if (!empty($search)) {
            $options['where_like'] = [
                't.keterangan' => $search,
                't.nominal'    => $search,
            ];
        }
        return $options;
    }


    private function _format_datatables_response($list_data, $total_data)
    {
        $request = \Config\Services::request();
        return $this->response->setJSON([
            'draw'            => intval($request->getPost('draw')),
            'recordsTotal'    => $total_data,
            'recordsFiltered' => $total_data,
            'data'            => $list_data
        ]);
    }

    public function set_filter_region()
    {
        $region_id = $this->request->getPost('region_id');
        session()->set('active_region', $region_id);
        return $this->response->setJSON(['status' => 'success']);
    }
}
