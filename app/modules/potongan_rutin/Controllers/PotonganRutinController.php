<?php

namespace App\modules\potongan_rutin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Karyawan\Models\MKaryawan;
use App\modules\kasbon_karyawan\Models\MPotonganRutin;

class PotonganRutinController extends BaseController
{
    protected $model_karyawan;
    protected $model_transaksi_karyawan;
    protected $model_potongan_rutin;
    protected $db;

    public function __construct()
    {
        $this->model_karyawan             = new MKaryawan();
        $this->model_transaksi_karyawan   = $this->model_karyawan;
        $this->model_potongan_rutin       = new MPotonganRutin();
        $this->db                         = \Config\Database::connect();
    }

    public function index()
    {
        $role = session()->get('role');
        $data = [
            'title'            => 'Kelola Potongan Rutin',
            'current_segment'  => 'potongan-rutin',
            'role'             => $role,
        ];
        return view('App\modules\potongan_rutin\Views\index', $data);
    }

    public function fetch()
    {
        $draw   = $this->request->getPost('draw');
        $start  = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $search = $this->request->getPost('search')['value'];

        $region_patient = session()->get('region_patient');
        $regionFilter   = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        $dataRaw = $this->model_transaksi_karyawan->get_datatables_terapis($search, $start, $length, $regionFilter);
        $data    = [];

        foreach ($dataRaw as $row) {
            $settings      = $this->model_potongan_rutin->getByTerapis((int) $row['id']);
            $jumlahSetting = count($settings);

            $data[] = [
                'id'            => $row['id'],
                'nama'          => esc($row['nama']),
                'jabatan'       => esc($row['nama_jabatan'] ?? 'TERAPIS'),
                'gaji_pokok'    => 'Rp ' . number_format($row['nominal_gaji'] ?? 0, 0, ',', '.'),
                'potongan_info' => $jumlahSetting > 0
                    ? $jumlahSetting . ' potongan rutin aktif'
                    : 'Belum ada setting',
                'potongan_raw'  => $jumlahSetting,
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => $this->model_transaksi_karyawan->count_all_terapis($regionFilter),
            "recordsFiltered" => $this->model_transaksi_karyawan->count_filtered_terapis($search, $regionFilter),
            "data"            => $data,
            "csrfHash"        => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $terapis = $this->model_transaksi_karyawan->getDetailTerapis($id);
        if (!$terapis) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $data = [
            'title'    => 'Setting Potongan Rutin - ' . $terapis['nama'],
            'terapis'  => $terapis,
            'settings' => $this->model_potongan_rutin->getByTerapis((int) $id),
        ];

        return view('App\modules\potongan_rutin\Views\detail', $data);
    }

    public function saveSetting()
    {
        $terapisId    = (int) $this->request->getPost('terapis_id');
        $namaPotongan = $this->request->getPost('nama_potongan');
        $nominal      = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $userId       = session()->get('userId');

        if (!$terapisId || empty($namaPotongan) || $nominal <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $result = $this->model_potongan_rutin->saveSetting($terapisId, $namaPotongan, $nominal, $userId);

        return $this->response->setJSON([
            'status'   => $result ? 'success' : 'error',
            'message'  => $result ? 'Setting potongan rutin berhasil disimpan' : 'Gagal menyimpan',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function saveSettingMassal()
    {
        $namaPotongan = $this->request->getPost('nama_potongan');
        $nominal      = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $userId       = session()->get('userId');

        if (empty($namaPotongan) || $nominal <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $region_patient = session()->get('region_patient');
        $terapisQuery   = $this->model_karyawan->where('is_active', 1);

        if (!empty($region_patient) && $region_patient !== 'all') {
            if (is_array($region_patient)) {
                $terapisQuery->whereIn('region_id', $region_patient);
            } else {
                $terapisQuery->where('region_id', $region_patient);
            }
        }

        $terapisAktif = $terapisQuery->findAll();
        if (empty($terapisAktif)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada terapis aktif']);
        }

        $count = 0;
        foreach ($terapisAktif as $t) {
            $id = is_object($t) ? $t->id : $t['id'];
            $this->model_potongan_rutin->saveSetting($id, $namaPotongan, $nominal, $userId);
            $count++;
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => "Setting potongan rutin berhasil diterapkan ke $count terapis",
            'csrfHash' => csrf_hash()
        ]);
    }

    public function deleteSetting($id)
    {
        $this->model_potongan_rutin->delete($id);
        return $this->response->setJSON(['status' => 'success', 'csrfHash' => csrf_hash()]);
    }
}
