<?php

namespace App\modules\jasa_pelayanan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\jasa_pelayanan\Models\Mjasapelayanan;

class Jasapelayanan extends BaseController
{
    protected $model_jasapelayanan;
    protected $db;
    protected $modelJaspelSettings;

    public function __construct()
    {
        $this->model_jasapelayanan = new Mjasapelayanan();
        $this->modelJaspelSettings = new \App\modules\jasa_pelayanan\Models\MJaspelSettings();
        $this->db = \Config\Database::connect();
        helper(['url', 'form']);
    }

    /**
     * Halaman Jasa Pelayanan - Reguler
     * Tampil data per hari dengan total pasien dan komisi
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

        $settingsReguler = $this->modelJaspelSettings->getAllWithRegion('reguler');

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
            'current_month'   => date('Y-m'),
            'settings'        => $settingsReguler,
        ];

        return view('App\modules\jasa_pelayanan\Views\index_regular', $data);
    }

    /**
     * Halaman Jasa Pelayanan - Kejantanan
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

        $settingsKejantanan = $this->modelJaspelSettings->getAllWithRegion('kejantanan');

        $data = [
            'realname'         => session()->get('realname'),
            'role'             => $role,
            'title'            => 'Jasa Pelayanan - Kejantanan',
            'kategori'         => 'Kejantanan',
            'msg'              => session()->getFlashdata('message'),
            'wilayah'          => $mRegion->getData(null, $allowed_regions) ?? [],
            'sess_region_name' => session()->get('active_region_name'),
            'sess_region_id'   => session()->get('active_region'),
            'sess_role'        => $role,
            'current_month'    => date('Y-m'),
            'settings_kejantanan' => $settingsKejantanan,
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

            // Format tanggal ke bahasa Indonesia
            $bulan_indo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $dateFormatted = '-';
            if (!empty($row->last_visit_date)) {
                $tgl = date('d', strtotime($row->last_visit_date));
                $bln = $bulan_indo[(int)date('m', strtotime($row->last_visit_date))];
                $thn = date('Y', strtotime($row->last_visit_date));
                $dateFormatted = "$tgl $bln $thn";
            }

            $output[] = [
                "id"           => $row->id,
                "name"         => $row->name . ' (' . ($row->phone ?? '-') . ')',
                "name_region"  => $row->name_region ?? '-',
                "address"      => $fullAddress ?: '-',
                "date"         => $dateFormatted,
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
        
        $bulan_indo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($dataOutput as $row) {
            $tgl = date('d', strtotime($row->tanggal_layanan));
            $bln = $bulan_indo[(int)date('m', strtotime($row->tanggal_layanan))];
            $thn = date('Y', strtotime($row->tanggal_layanan));
            
            $output[] = [
                "no"              => $no++,
                "tanggal_layanan" => "$tgl $bln $thn",
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
        $MKaryawan = new \App\Modules\Karyawan\Models\MKaryawan();

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'title'           => 'Detail Pasien — ' . $kategori,
            'kategori'        => $kategori,
            'patient'         => $patientData,
            'address'         => (object) $addressData,
            'wilayah'         => $mRegion->getData(null, $allowed_regions) ?? [],
            'negara'          => $mCountries->asObject()->findAll(),
            'terapis'         => $MKaryawan->asObject()->findAll(),
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

    /**
     * Halaman Pengaturan Jaspel
     */
    public function settings()
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }



        $role = session()->get('role');
        $region_patient = session()->get('region_patient');
        
        // Hanya superadmin dan owner yang bisa akses
        if (!in_array($role, ['superadmin', 'owner'])) {
            return redirect()->back()->with('message', ['error', 'Akses Ditolak', 'Anda tidak memiliki akses ke halaman ini.']);
        }

        $mRegion = model('App\modules\region\Models\MRegion');
        $MKaryawan = new \App\Modules\Karyawan\Models\MKaryawan();
        
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $regions = $mRegion->getData(null, $allowed_regions);

        // Get all settings
        $settings = $this->modelJaspelSettings->getAllWithRegion();
        $settingsReguler    = $this->modelJaspelSettings->getAllWithRegion('reguler');
        $settingsKejantanan = $this->modelJaspelSettings->getAllWithRegion('kejantanan');
        
        // Get all terapis grouped by region
        $allTerapis = $MKaryawan->select('terapis.*, regions.name as region_name')
                               ->join('regions', 'regions.id = terapis.region_id', 'left')
                               ->where('terapis.is_active', 1)
                               ->findAll();

        $data = [
            'title'              => 'Pengaturan Jasa Pelayanan',
            'realname'           => session()->get('realname'),
            'role'               => $role,
            'regions'            => $regions,
            'settings'           => $settingsReguler,
            'settings_kejantanan'=> $settingsKejantanan,
            'all_terapis'        => $allTerapis,
            'msg'                => session()->getFlashdata('message'),
        ];

        return view('App\modules\jasa_pelayanan\Views\settings', $data);
    }

    /**
     * Save Jaspel Settings
     */
    public function saveSettings()
    {
        if (!session()->get('isLogin')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $regionId   = $this->request->getPost('region_id');
        $nominal    = $this->request->getPost('nominal_per_pasien');
        $terapisIds = $this->request->getPost('terapis_ids');
        $tipe       = $this->request->getPost('tipe') ?? 'reguler'; // 'reguler' atau 'kejantanan'

        if (empty($regionId) || empty($nominal)) {
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => 'Region dan nominal wajib diisi',
                'csrfHash' => csrf_hash()
            ]);
        }

        $data = [
            'nominal_per_pasien' => $nominal,
            'terapis_ids'        => json_encode($terapisIds ?? []),
            'is_active'          => 1, // Enforce active state
            'updated_by'         => session()->get('userId'),
        ];

        // Check if new insert
        $existing = $this->modelJaspelSettings->where('region_id', $regionId)->where('tipe', $tipe)->first();
        if (!$existing) {
            $data['created_by'] = session()->get('userId');
        }

        $result = $this->modelJaspelSettings->saveSettings($regionId, $data, $tipe);

        if ($result) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Pengaturan berhasil disimpan',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'   => 'error',
            'message'  => 'Gagal menyimpan pengaturan',
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Get Jaspel Data Per Hari (untuk DataTables)
     */
    public function getJaspelPerHari()
    {
        $request = \Config\Services::request();
        
        $bulan = $this->request->getPost('bulan') ?? date('Y-m');
        $regionId = $this->request->getPost('region_id');
        
        $role = session()->get('role');
        $region_patient = session()->get('region_patient');
        
        // Filter region berdasarkan role
        if ($role !== 'superadmin') {
            if ($region_patient !== 'all') {
                $allowed = is_array($region_patient) ? $region_patient : [$region_patient];
                if (empty($regionId) || !in_array($regionId, $allowed)) {
                    $regionId = $allowed[0];
                }
            }
        }

        if (empty($regionId)) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'csrfHash' => csrf_hash()
            ]);
        }

        // Get jaspel settings untuk region ini
        $settings = $this->modelJaspelSettings->getByRegion($regionId, 'reguler');
        
        if (!$settings) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'message' => 'Pengaturan jaspel belum dibuat untuk cabang ini',
                'csrfHash' => csrf_hash()
            ]);
        }

        $nominalPerPasien = $settings->nominal_per_pasien;
        $terapisIdsAllowed = json_decode($settings->terapis_ids, true) ?? [];

        // Parse bulan
        list($tahun, $bulanNum) = explode('-', $bulan);
        $startDate = "$tahun-$bulanNum-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Query: Hitung jumlah pasien per hari dari antrean yang sudah selesai, EXCLUDE kejantanan
        $pasienPerHari = $this->db->table('patient_queues pq')
            ->select('DATE(pq.queue_date) as tanggal, COUNT(DISTINCT pq.patient_id) as total_pasien')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'inner')
            ->where('pq.region_id', $regionId)
            ->where('h.finish_at IS NOT NULL', null, false)
            ->where('(h.kejantanan != \'ya\' OR h.kejantanan IS NULL)', null, false)
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate)
            ->groupBy('DATE(pq.queue_date)')
            ->orderBy('tanggal', 'DESC')
            ->get()
            ->getResult();

        $output = [];
        $no = 1;

        foreach ($pasienPerHari as $row) {
            $tanggal = $row->tanggal;
            $totalPasien = $row->total_pasien;

            // Cek terapis yang hadir di tanggal ini (dari terapis yang berhak dan masih aktif)
            $terapisHadir = [];
            if (!empty($terapisIdsAllowed)) {
                $terapisHadir = $this->db->table('absensi_karyawan ak')
                    ->select('ak.terapis_id')
                    ->join('terapis t', 't.id = ak.terapis_id', 'inner')
                    ->whereIn('ak.terapis_id', $terapisIdsAllowed)
                    ->where('ak.tanggal', $tanggal)
                    ->where('ak.status', 'Hadir')
                    ->where('t.is_active', 1) // Hanya terapis yang aktif
                    ->get()
                    ->getResultArray();
            }

            $jumlahTerapisHadir = count($terapisHadir);
            // ✅ Rumus benar: Total Jaspel = Pool (pasien × nominal)
            $totalJaspelHariIni = $totalPasien * $nominalPerPasien;
            // ✅ Jaspel/Terapis = Pool dibagi terapis yang hadir
            $jaspelPerTerapis = $jumlahTerapisHadir > 0 ? $totalJaspelHariIni / $jumlahTerapisHadir : 0;

            // Get nama terapis yang hadir
            $terapisHadirIds = [];
            $namaTerapisHadir = '-';
            if ($jumlahTerapisHadir > 0) {
                $terapisHadirIds = array_column($terapisHadir, 'terapis_id');
                $namaTerapis = $this->db->table('terapis')
                    ->select('nama')
                    ->whereIn('id', $terapisHadirIds)
                    ->where('is_active', 1)
                    ->get()
                    ->getResultArray();
                $namaTerapisHadir = implode(', ', array_column($namaTerapis, 'nama'));
            }

            // Simpan/update ke tabel jaspel_harian
            $existing = $this->db->table('jaspel_harian')
                ->where('tanggal', $tanggal)
                ->where('region_id', $regionId)
                ->where('tipe', 'reguler')
                ->get()->getRow();
            $jaspelHarianData = [
                'total_pasien'         => $totalPasien,
                'nominal_per_pasien'   => $nominalPerPasien,
                'total_jaspel'         => $totalJaspelHariIni,
                'terapis_hadir_ids'    => json_encode($terapisHadirIds),
                'jumlah_terapis_hadir' => $jumlahTerapisHadir,
                'jaspel_per_terapis'   => $jaspelPerTerapis,
                'updated_at'           => date('Y-m-d H:i:s'),
            ];
            if ($existing) {
                $this->db->table('jaspel_harian')->where('id', $existing->id)->update($jaspelHarianData);
            } else {
                $jaspelHarianData['tanggal']    = $tanggal;
                $jaspelHarianData['region_id']  = $regionId;
                $jaspelHarianData['tipe']       = 'reguler';
                $jaspelHarianData['is_processed'] = 0;
                $jaspelHarianData['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('jaspel_harian')->insert($jaspelHarianData);
            }

            // Format tanggal ke bahasa Indonesia
            $bulan_indo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $tgl = date('d', strtotime($tanggal));
            $bln = $bulan_indo[(int)date('m', strtotime($tanggal))];
            $thn = date('Y', strtotime($tanggal));
            $tanggalIndo = "$tgl $bln $thn";

            $output[] = [
                'no'                => $no++,
                'tanggal'           => $tanggalIndo,
                'total_pasien'      => $totalPasien,
                'terapis_hadir'     => $jumlahTerapisHadir . ' orang',
                'nama_terapis'      => $namaTerapisHadir,
                'total_jaspel'      => 'Rp ' . number_format($totalJaspelHariIni, 0, ',', '.'),
                'jaspel_per_terapis'=> 'Rp ' . number_format($jaspelPerTerapis, 0, ',', '.'),
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($this->request->getPost('draw')),
            'recordsTotal' => count($output),
            'recordsFiltered' => count($output),
            'data' => $output,
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * Get Jaspel Kejantanan Per Hari (untuk DataTables)
     * Sama seperti reguler, tapi hanya antrean yang rekam medisnya kejantanan = 'ya'
     */
    public function getJaspelKejantananPerHari()
    {
        $bulan    = $this->request->getPost('bulan') ?? date('Y-m');
        $regionId = $this->request->getPost('region_id');

        $role           = session()->get('role');
        $region_patient = session()->get('region_patient');

        if ($role !== 'superadmin') {
            if ($region_patient !== 'all') {
                $allowed = is_array($region_patient) ? $region_patient : [$region_patient];
                if (empty($regionId) || !in_array($regionId, $allowed)) {
                    $regionId = $allowed[0];
                }
            }
        }

        if (empty($regionId)) {
            return $this->response->setJSON([
                'draw'            => intval($this->request->getPost('draw')),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'csrfHash'        => csrf_hash()
            ]);
        }

        $settings = $this->modelJaspelSettings->getByRegion($regionId, 'kejantanan');

        if (!$settings) {
            return $this->response->setJSON([
                'draw'            => intval($this->request->getPost('draw')),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'message'         => 'Pengaturan jaspel kejantanan belum dibuat untuk cabang ini',
                'csrfHash'        => csrf_hash()
            ]);
        }

        $nominalPerPasien  = $settings->nominal_per_pasien;
        $terapisIdsAllowed = json_decode($settings->terapis_ids, true) ?? [];

        list($tahun, $bulanNum) = explode('-', $bulan);
        $startDate = "$tahun-$bulanNum-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        // Hitung pasien per hari: antrean selesai + rekam medis kejantanan = 'ya'
        $pasienPerHari = $this->db->table('patient_queues pq')
            ->select('DATE(pq.queue_date) as tanggal, COUNT(DISTINCT pq.patient_id) as total_pasien')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'inner')
            ->where('pq.region_id', $regionId)
            ->where('h.finish_at IS NOT NULL', null, false)
            ->where('h.kejantanan', 'ya')
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate)
            ->groupBy('DATE(pq.queue_date)')
            ->orderBy('tanggal', 'DESC')
            ->get()
            ->getResult();

        $bulan_indo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $output = [];
        $no     = 1;

        foreach ($pasienPerHari as $row) {
            $tanggal    = $row->tanggal;
            $totalPasien = $row->total_pasien;

            // Cek terapis yang hadir di tanggal ini
            $terapisHadir = [];
            if (!empty($terapisIdsAllowed)) {
                $terapisHadir = $this->db->table('absensi_karyawan ak')
                    ->select('ak.terapis_id')
                    ->join('terapis t', 't.id = ak.terapis_id', 'inner')
                    ->whereIn('ak.terapis_id', $terapisIdsAllowed)
                    ->where('ak.tanggal', $tanggal)
                    ->where('ak.status', 'Hadir')
                    ->where('t.is_active', 1)
                    ->get()
                    ->getResultArray();
            }

            $jumlahTerapisHadir  = count($terapisHadir);
            // ✅ Rumus benar: Total Jaspel = Pool (pasien × nominal)
            $totalJaspelHariIni  = $totalPasien * $nominalPerPasien;
            // ✅ Jaspel/Terapis = Pool dibagi terapis yang hadir
            $jaspelPerTerapis    = $jumlahTerapisHadir > 0 ? $totalJaspelHariIni / $jumlahTerapisHadir : 0;

            $terapisHadirIds = [];
            $namaTerapisHadir = '-';
            if ($jumlahTerapisHadir > 0) {
                $terapisHadirIds = array_column($terapisHadir, 'terapis_id');
                $namaTerapis = $this->db->table('terapis')
                    ->select('nama')
                    ->whereIn('id', $terapisHadirIds)
                    ->where('is_active', 1)
                    ->get()
                    ->getResultArray();
                $namaTerapisHadir = implode(', ', array_column($namaTerapis, 'nama'));
            }

            // Simpan/update ke tabel jaspel_harian
            $existingKej = $this->db->table('jaspel_harian')
                ->where('tanggal', $tanggal)
                ->where('region_id', $regionId)
                ->where('tipe', 'kejantanan')
                ->get()->getRow();
            $jaspelHarianDataKej = [
                'total_pasien'         => $totalPasien,
                'nominal_per_pasien'   => $nominalPerPasien,
                'total_jaspel'         => $totalJaspelHariIni,
                'terapis_hadir_ids'    => json_encode($terapisHadirIds),
                'jumlah_terapis_hadir' => $jumlahTerapisHadir,
                'jaspel_per_terapis'   => $jaspelPerTerapis,
                'updated_at'           => date('Y-m-d H:i:s'),
            ];
            if ($existingKej) {
                $this->db->table('jaspel_harian')->where('id', $existingKej->id)->update($jaspelHarianDataKej);
            } else {
                $jaspelHarianDataKej['tanggal']    = $tanggal;
                $jaspelHarianDataKej['region_id']  = $regionId;
                $jaspelHarianDataKej['tipe']       = 'kejantanan';
                $jaspelHarianDataKej['is_processed'] = 0;
                $jaspelHarianDataKej['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('jaspel_harian')->insert($jaspelHarianDataKej);
            }

            $tgl = date('d', strtotime($tanggal));
            $bln = $bulan_indo[(int)date('m', strtotime($tanggal))];
            $thn = date('Y', strtotime($tanggal));

            $output[] = [
                'no'                 => $no++,
                'tanggal'            => "$tgl $bln $thn",
                'total_pasien'       => $totalPasien,
                'terapis_hadir'      => $jumlahTerapisHadir . ' orang',
                'nama_terapis'       => $namaTerapisHadir,
                'total_jaspel'       => 'Rp ' . number_format($totalJaspelHariIni, 0, ',', '.'),
                'jaspel_per_terapis' => 'Rp ' . number_format($jaspelPerTerapis, 0, ',', '.'),
            ];
        }

        return $this->response->setJSON([
            'draw'            => intval($this->request->getPost('draw')),
            'recordsTotal'    => count($output),
            'recordsFiltered' => count($output),
            'data'            => $output,
            'csrfHash'        => csrf_hash()
        ]);
    }
}
