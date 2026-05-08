<?php

namespace App\modules\absensi_karyawan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\absensi_karyawan\Models\Mabsensikaryawan;
use App\modules\terapis\Models\MTerapis;

class Absensikaryawan extends BaseController
{

    protected $model_terapis;
    protected $model_absensi;

    public function __construct()
    {
        $this->model_terapis = new MTerapis();
        $this->model_absensi = new Mabsensikaryawan();
    }

    public function index()
    {
        $data = [
            'title'        => 'Presensi Harian',
            'terapis'      => $this->model_terapis->where('is_active', 1)->findAll(),
            'rekap_harian' => $this->model_absensi->getRekapHarian(),
            'tanggal'      => date('Y-m-d')
        ];

        return view('App\modules\absensi_karyawan\Views\index', $data);
    }

    public function store($tanggal = null)
    {
        if ($tanggal === null || !strtotime($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $absensiByTanggal = $this->model_absensi->getByTanggal($tanggal);
        $rekapByTanggal = [];

        foreach ($absensiByTanggal as $row) {
            $rekapByTanggal[$row['terapis_id']] = $row;
        }

        $data = [
            'title'            => 'Input Presensi',
            'terapis'          => $this->model_terapis->where('is_active', 1)->findAll(),
            'tanggal'          => $tanggal,
            'rekap_by_tanggal' => $rekapByTanggal
        ];

        return view('App\modules\absensi_karyawan\Views\absensi', $data);
    }

    public function detail($tanggal = null)
    {
        return $this->store($tanggal);
    }

    public function simpan_massal()
    {
        $postAbsen = $this->request->getPost('absen');
        $tanggal   = $this->request->getPost('tanggal');

        if (!$postAbsen || !$tanggal) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak valid.']);
        }

        $dataInsert = [];
        foreach ($postAbsen as $row) {
            $dataInsert[] = [
                'terapis_id' => $row['terapis_id'],
                'tanggal'    => $tanggal,
                'status'     => $row['status'],
                'keterangan' => $row['keterangan'] ?? null
            ];
        }

        // Hapus data lama di tanggal yang sama agar tidak duplikat/double entry
        $this->model_absensi->where('tanggal', $tanggal)->delete();

        if ($this->model_absensi->insertBatch($dataInsert)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Presensi tanggal ' . date('d M Y', strtotime($tanggal)) . ' berhasil disimpan!',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan ke database.']);
    }
}
