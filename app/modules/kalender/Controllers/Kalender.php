<?php

namespace App\modules\kalender\Controllers;

use App\Controllers\BaseController;
use App\modules\kalender\Models\MKalender;
use App\modules\region\Models\MRegion;

class Kalender extends BaseController
{
    protected $model;
    protected $mRegion;

    public function __construct()
    {
        $this->model   = new MKalender();
        $this->mRegion = new MRegion();
    }

    public function index()
    {
        if (!session()->get('isLogin')) return redirect()->to(base_url('auth'));

        $role           = session()->get('role');
        $region_patient = session()->get('region_patient');
        $tahun          = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $bulan          = (int) ($this->request->getGet('bulan') ?? date('n'));

        // Superadmin lihat kalender global, owner lihat kalender cabangnya
        if ($role === 'superadmin') {
            $regionId    = null;
            $liburKhusus = $this->model->getGlobal($tahun);
            $canEdit     = true;
            $canCopy     = false;
        } else {
            $regionId    = is_array($region_patient) ? $region_patient[0] : $region_patient;
            $liburKhusus = $this->model->getByRegion($tahun, $regionId);
            $canEdit     = in_array($role, ['owner', 'admin']);
            $canCopy     = in_array($role, ['owner', 'admin']);
        }

        // Expand libur rutin jadi array tanggal
        $liburRutinTanggal = $this->model->generateLiburRutin($tahun, $regionId);

        // Semua libur (khusus + rutin) untuk highlight kalender
        $semuaLibur = array_column(
            array_filter($liburKhusus, fn($l) => $l['tipe'] === 'libur_khusus'),
            'tanggal'
        );
        $semuaLibur = array_merge($semuaLibur, $liburRutinTanggal);

        // Libur rutin rules
        $liburRutin = array_filter($liburKhusus, fn($l) => $l['tipe'] === 'libur_rutin');
        if ($role === 'superadmin') {
            $liburRutin = array_filter(
                $this->model->getGlobal($tahun),
                fn($l) => $l['tipe'] === 'libur_rutin'
            );
        }

        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'title'              => 'Kalender Kerja',
            'role'               => $role,
            'tahun'              => $tahun,
            'bulan'              => $bulan,
            'libur_khusus'       => array_values(array_filter($liburKhusus, fn($l) => $l['tipe'] === 'libur_khusus')),
            'libur_rutin'        => array_values($liburRutin),
            'semua_libur'        => array_unique($semuaLibur),
            'can_edit'           => $canEdit,
            'can_copy'           => $canCopy,
            'region_id'          => $regionId,
            'wilayah'            => $this->mRegion->getData(null, $allowed_regions) ?? [],
            'has_kalender_cabang'=> $regionId ? count($this->model->getByRegion($tahun, $regionId)) > 0 : false,
            'global_count'       => count($this->model->getGlobal($tahun)),
            'realname'           => session()->get('realname'),
        ];

        return view('App\modules\kalender\Views\index', $data);
    }

    /**
     * Simpan libur khusus (tanggal tertentu)
     */
    public function store()
    {
        if (!session()->get('isLogin')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $role       = session()->get('role');
        $tanggal    = $this->request->getPost('tanggal');
        $keterangan = $this->request->getPost('keterangan');
        $tahun      = (int) date('Y', strtotime($tanggal));

        if (empty($tanggal) || empty($keterangan)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal dan keterangan wajib diisi']);
        }

        if ($role === 'superadmin') {
            $regionId = null;
        } else {
            $region_patient = session()->get('region_patient');
            $regionId = is_array($region_patient) ? $region_patient[0] : $region_patient;
        }

        // Cek duplikat
        $existing = $this->model->where('tanggal', $tanggal)
                                ->where('region_id', $regionId)
                                ->where('tipe', 'libur_khusus')
                                ->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal ini sudah ada di kalender']);
        }

        $this->model->insert([
            'tanggal'    => $tanggal,
            'keterangan' => $keterangan,
            'tipe'       => 'libur_khusus',
            'region_id'  => $regionId,
            'tahun'      => $tahun,
            'is_active'  => 1,
            'created_by' => session()->get('userId'),
            'updated_by' => session()->get('userId'),
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Hari libur berhasil ditambahkan',
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Simpan libur rutin (hari dalam seminggu)
     */
    public function storeRutin()
    {
        if (!session()->get('isLogin')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $role       = session()->get('role');
        $hariRutin  = $this->request->getPost('hari_rutin'); // 0-6
        $keterangan = $this->request->getPost('keterangan');
        $tahun      = (int) ($this->request->getPost('tahun') ?? date('Y'));

        if ($role === 'superadmin') {
            $regionId = null;
        } else {
            $region_patient = session()->get('region_patient');
            $regionId = is_array($region_patient) ? $region_patient[0] : $region_patient;
        }

        // Cek duplikat hari rutin
        $existing = $this->model->where('hari_rutin', $hariRutin)
                                ->where('region_id', $regionId)
                                ->where('tahun', $tahun)
                                ->where('tipe', 'libur_rutin')
                                ->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hari rutin ini sudah diset']);
        }

        $this->model->insert([
            'tanggal'    => "$tahun-01-01", // placeholder
            'keterangan' => $keterangan,
            'tipe'       => 'libur_rutin',
            'hari_rutin' => $hariRutin,
            'region_id'  => $regionId,
            'tahun'      => $tahun,
            'is_active'  => 1,
            'created_by' => session()->get('userId'),
            'updated_by' => session()->get('userId'),
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Libur rutin berhasil ditambahkan',
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Hapus libur
     */
    public function destroy($id)
    {
        if (!session()->get('isLogin')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $role = session()->get('role');
        $item = $this->model->find($id);

        if (!$item) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        // Superadmin hanya bisa hapus global, owner hanya bisa hapus milik cabangnya
        if ($role === 'superadmin' && $item['region_id'] !== null) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak bisa hapus kalender cabang']);
        }
        if ($role !== 'superadmin') {
            $region_patient = session()->get('region_patient');
            $regionId = is_array($region_patient) ? $region_patient[0] : $region_patient;
            if ($item['region_id'] != $regionId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
            }
        }

        $this->model->delete($id);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Hari libur berhasil dihapus',
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Copy kalender global ke cabang (untuk owner)
     */
    public function copyGlobal()
    {
        if (!session()->get('isLogin')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $role = session()->get('role');
        if (!in_array($role, ['owner', 'admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya owner yang bisa copy kalender']);
        }

        $tahun          = (int) ($this->request->getPost('tahun') ?? date('Y'));
        $region_patient = session()->get('region_patient');
        $regionId       = is_array($region_patient) ? $region_patient[0] : $region_patient;
        $userId         = session()->get('userId');

        $result = $this->model->copyGlobalToRegion($tahun, $regionId, $userId);

        if ($result) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Kalender global berhasil disalin ke cabang Anda',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Kalender global kosong atau gagal disalin'
        ]);
    }

    /**
     * Get data libur untuk AJAX (dipakai kalender visual)
     */
    public function getData()
    {
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $role  = session()->get('role');

        if ($role === 'superadmin') {
            $regionId = null;
        } else {
            $region_patient = session()->get('region_patient');
            $regionId = is_array($region_patient) ? $region_patient[0] : $region_patient;
        }

        $liburKhusus       = $this->model->getByTahun($tahun, $regionId);
        $liburRutinTanggal = $this->model->generateLiburRutin($tahun, $regionId);

        $tanggalLibur = array_column(
            array_filter($liburKhusus, fn($l) => $l['tipe'] === 'libur_khusus'),
            null, 'tanggal'
        );

        return $this->response->setJSON([
            'libur_khusus'  => array_values($tanggalLibur),
            'libur_rutin'   => $liburRutinTanggal,
            'semua_libur'   => array_unique(array_merge(array_keys($tanggalLibur), $liburRutinTanggal)),
            'csrfHash'      => csrf_hash()
        ]);
    }
}
