<?php

namespace App\modules\jasa_pelayanan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\jasa_pelayanan\Models\Mjasapelayanan;

class Jasapelayanan extends BaseController
{
    protected $model_jasapelayanan;
    protected $db;

    public function __construct()
    {
        $this->model_jasapelayanan = new Mjasapelayanan();
        $this->db = \Config\Database::connect();
        helper(['url', 'form']);
    }


    public function reguler()
    {
        $data = [
            'title'    => 'Jasa Pelayanan - Reguler',
            'kategori' => 'Reguler' // Kunci pembeda
        ];
        // Keduanya me-load View yang sama, tapi dikirim 'kategori' yang berbeda
        return view('App\modules\jasa_pelayanan\Views\index_regular', $data);
    }

    public function kejantanan()
    {
        $data = [
            'title'    => 'Jasa Pelayanan - Kejantanan',
            'kategori' => 'Kejantanan' // Kunci pembeda
        ];
        return view('App\modules\jasa_pelayanan\Views\index', $data);
    }

    public function fetch()
    {
        $request = \Config\Services::request();

        $kategori = $request->getPost('kategori');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $search = $request->getPost('search')['value'] ?? '';
        $builder = $this->model_jasapelayanan->getDatatablesBuilder($kategori);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('t.nama', $search)
                ->groupEnd();
        }

        $tempBuilder = clone $builder;
        $totalFiltered = $this->db->table('(' . $tempBuilder->getCompiledSelect() . ') AS temp_table')->countAllResults();

        $dataOutput = $builder->orderBy('jp.tanggal_layanan', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResult();

        $totalData = $this->model_jasapelayanan
            ->where('kategori_layanan', $kategori)
            ->where('is_delete', 0)
            ->countAllResults();

        $no = $start + 1;
        $output = [];

        foreach ($dataOutput as $row) {
            $output[] = [
                "no"              => $no++,
                "tanggal_layanan" => date('d-m-Y', strtotime($row->tanggal_layanan)),
                "nama_pasien"     => $row->nama_pasien . ' (' . ($row->telepon_pasien ?? '-') . ')',
                "nama_terapis"    => $row->nama_terapis ?? '-',
                "action"          => '
                    <div class="flex items-center justify-center gap-2">
                        <a href="' . base_url('jasa-pelayanan/show/' . $row->id) . '" title="Detail Data" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button type="button" onclick="destroy(' . $row->id . ')" title="Hapus Data" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>'
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data"            => $output,
            "csrfHash"        => csrf_hash() // Penting untuk auto-renew CSRF di AJAX
        ]);
    }


    // 3. FUNGSI DETAIL (Melihat Laporan Transaksi Lengkap)
    public function show($id)
    {
        $detailLayanan = $this->model_jasapelayanan->showDetail($id);

        if (!$detailLayanan) {
            return redirect()->back()->with('error', 'Data layanan tidak ditemukan.');
        }

        $data = [
            'title'   => 'Detail ' . $detailLayanan->kategori_layanan,
            'layanan' => $detailLayanan
        ];

        return view('App\modules\jasa_pelayanan\Views\index_regular', $data);
    }

    // 4. FUNGSI HAPUS DATA (Soft Delete)
    public function destroy($id)
    {
        $this->model_jasapelayanan->destroy($id);

        return $this->response->setJSON([
            'status'    => true,
            'message'   => 'Data berhasil dihapus',
            'new_token' => csrf_hash()
        ]);
    }

    // public function index()
    // {
    //     //
    // }
}
