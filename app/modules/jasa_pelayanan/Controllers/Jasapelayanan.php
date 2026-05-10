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

    /**
     * Halaman Jasa Pelayanan - Reguler
     * Tampil daftar pasien (mirip Rekam Medis)
     */
    public function reguler()
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $mRegion = model('App\modules\region\Models\MRegion');

        $role = session()->get('role');
        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'realname'        => session()->get('realname'),
            'role'            => $role,
            'title'           => 'Jasa Pelayanan - Reguler',
            'kategori'        => 'Reguler',
            'msg'             => session()->getFlashdata('message'),
            'wilayah'         => $mRegion->getData(null, $allowed_regions) ?? [],
            'sess_region_name' => session()->get('active_region_name'),
            'sess_region_id'  => session()->get('active_region'),
            'sess_role'       => $role,
        ];

        return view('App\modules\jasa_pelayanan\Views\index_regular', $data);
    }

    /**
     * Halaman Jasa Pelayanan - Kejantanan
     * Tampil daftar pasien (mirip Rekam Medis)
     */
    public function kejantanan()
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $mRegion = model('App\modules\region\Models\MRegion');

        $role = session()->get('role');
        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'realname'        => session()->get('realname'),
            'role'            => $role,
            'title'           => 'Jasa Pelayanan - Kejantanan',
            'kategori'        => 'Kejantanan',
            'msg'             => session()->getFlashdata('message'),
            'wilayah'         => $mRegion->getData(null, $allowed_regions) ?? [],
            'sess_region_name' => session()->get('active_region_name'),
            'sess_region_id'  => session()->get('active_region'),
            'sess_role'       => $role,
        ];

        return view('App\modules\jasa_pelayanan\Views\index_kejantanan', $data);
    }

    /**
     * Fetch daftar pasien berdasarkan kategori layanan (Reguler/Kejantanan)
     * Format mirip Patients::fetch2() untuk custom pagination
     */
    public function fetchPatients()
    {
        $request = \Config\Services::request();

        $limit    = $this->request->getPost('length') ?? 10;
        $start    = $this->request->getPost('start') ?? 0;
        $search   = $request->getPost('search') ?? '';
        $kategori = $request->getPost('kategori') ?? 'Reguler';

        $region_patient  = session()->get('region_patient');
        $regionFilter = ($region_patient !== 'all') ? $region_patient : null;

        // Tentukan status kejantanan untuk filter
        $kejantananStatus = ($kategori === 'Kejantanan') ? 'ya' : 'tidak';

        // Query: pasien yang punya riwayat dengan kejantanan = ya/tidak
        $builder = $this->db->table('patients p')
            ->select('
                p.id, p.name, p.phone, p.address, p.region_id,
                r.name as name_region,
                pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama,
                COUNT(DISTINCT h.id) AS visit_count,
                MAX(h.date) AS last_visit_date
            ')
            ->join('histories h', 'h.patient_id = p.id AND h.is_delete = 0 AND h.kejantanan = "' . $kejantananStatus . '"', 'inner')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left');

        // Filter region
        if (!empty($regionFilter)) {
            if (is_array($regionFilter)) {
                $builder->whereIn('p.region_id', $regionFilter);
            } else {
                $builder->where('p.region_id', $regionFilter);
            }
        }

        // Search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.phone', $search)
                ->orLike('p.address', $search)
                ->orLike('p.id', $search)
                ->groupEnd();
        }

        $builder->groupBy([
            'p.id', 'p.name', 'p.phone', 'p.address', 'p.region_id',
            'r.name', 'pa.desa_nama', 'pa.kecamatan_nama', 'pa.kabupaten_nama', 'pa.provinsi_nama'
        ]);

        // Count filtered
        $tempBuilder = clone $builder;
        $totalFiltered = $this->db->table('(' . $tempBuilder->getCompiledSelect() . ') AS temp_table')->countAllResults();

        // Get data
        $data = $builder->orderBy('last_visit_date', 'DESC')
            ->limit($limit, $start)
            ->get()
            ->getResult();

        // Count total (semua pasien yang punya history dengan kategori ini)
        $totalBuilder = $this->db->table('patients p')
            ->select('p.id')
            ->join('histories h', 'h.patient_id = p.id AND h.is_delete = 0 AND h.kejantanan = "' . $kejantananStatus . '"', 'inner')
            ->groupBy('p.id');
        $totalData = $this->db->table('(' . $totalBuilder->getCompiledSelect() . ') AS total_table')->countAllResults();

        // Format output
        $output = [];
        foreach ($data as $row) {
            $addressParts = array_filter([
                $row->address,
                $row->desa_nama,
                $row->kecamatan_nama,
                $row->kabupaten_nama,
                $row->provinsi_nama
            ]);
            $fullAddress = implode(', ', $addressParts);

            $output[] = [
                "id"           => $row->id,
                "name"         => $row->name . ' (' . ($row->phone ?? '-') . ')',
                "name_region"  => $row->name_region ?? '-',
                "address"      => $fullAddress ?: '-',
                "date"         => !empty($row->last_visit_date) ? date('d-m-Y', strtotime($row->last_visit_date)) : '-',
                "visit_count"  => $row->visit_count ?? 0,
                "phone"        => $row->phone ?? '-',
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($this->request->getPost('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data"            => $output,
            "csrfHash"        => csrf_hash(),
        ]);
    }

    /**
     * Fetch data transaksi layanan (untuk DataTables - dipertahankan sebagai legacy)
     */
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


    /**
     * Detail Pasien — Reguler (form riwayat tanpa kejantanan)
     */
    public function showReguler($id = null)
    {
        return $this->renderDetailPage($id, 'Reguler');
    }

    /**
     * Detail Pasien — Kejantanan (form riwayat fokus kejantanan)
     */
    public function showKejantanan($id = null)
    {
        return $this->renderDetailPage($id, 'Kejantanan');
    }

    /**
     * Shared logic untuk render halaman detail pasien
     */
    private function renderDetailPage($id, $kategori)
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $patientModel = new \App\modules\patients\Models\MPatients();
        $patientData = $patientModel->find($id);

        if (!$patientData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Pasien dengan ID $id tidak ditemukan.");
        }

        $addressData = $this->db->table('patient_address')
            ->where('patient_id', $id)
            ->get()
            ->getRowArray() ?? [];

        if (!$addressData) {
            $addressData = [
                'desa_id' => '', 'desa_nama' => '',
                'kecamatan_id' => '', 'kecamatan_nama' => '',
                'kabupaten_id' => '', 'kabupaten_nama' => '',
                'provinsi_id' => '', 'provinsi_nama' => '',
            ];
        }

        $mRegion  = new \App\modules\region\Models\MRegion();
        $mCountries = new \App\modules\countries\Models\MCountries();
        $mTerapis = new \App\modules\terapis\Models\MTerapis();

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'title'           => 'Detail Pasien — ' . $kategori,
            'kategori'        => $kategori,
            'patient'         => $patientData,
            'address'         => (object) $addressData,
            'wilayah'         => $mRegion->getData(null, $allowed_regions) ?? [],
            'negara'          => $mCountries->asObject()->findAll(),
            'terapis'         => $mTerapis->asObject()->findAll(),
            'resources'       => $patientModel->get_resources(),
            'patient_id'      => $id,
            'queue_id'        => $this->request->getGet('queue_id') ?? '',
            'file_urls'       => json_decode($patientData->url ?? '[]', true),
            'current_date'    => date('Y-m-d'),
            'created_at'      => !empty($patientData->created_at) ? date('j F Y H:i', strtotime($patientData->created_at)) : '-',
            'updated_at'      => !empty($patientData->updated_at) ? date('j F Y H:i', strtotime($patientData->updated_at)) : '-',
            'created_by_name' => $this->getUserName($patientData->created_by ?? null),
            'updated_by_name' => $this->getUserName($patientData->updated_by ?? null),
            'realname'        => session()->get('realname'),
            'role'            => session()->get('role'),
            'regions_patient' => $region_patient,
            'msg'             => session()->getFlashdata('message') ?? ['', '', ''],
        ];

        $data['has_updated'] = ($data['updated_at'] !== '-');

        $viewPath = ($kategori === 'Kejantanan')
            ? 'App\modules\jasa_pelayanan\Views\detailKejantanan\show'
            : 'App\modules\jasa_pelayanan\Views\detailRegular\show';

        return view($viewPath, $data);
    }

    /**
     * Helper: Ambil nama user berdasarkan ID
     */
    private function getUserName($userId)
    {
        if (empty($userId)) return '-';
        $user = $this->db->table('users')->select('realname')->where('id', $userId)->get()->getRow();
        return $user ? $user->realname : '-';
    }

    // 3. FUNGSI DETAIL LEGACY (Melihat Laporan Transaksi Lengkap)
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

        return view('App\modules\jasa_pelayanan\Views\detailRegular\show', $data);
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
}
