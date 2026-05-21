<?php

namespace App\modules\tunjangan_karyawan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\tunjangan_karyawan\Models\Mtunjangankaryawan;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Tunjangankaryawan extends BaseController
{

    protected $Mtunjangan;
    protected $db;

    public function __construct()
    {
        $this->Mtunjangan = new Mtunjangankaryawan();
        $this->db = \Config\Database::connect();
    }


    private function formatKategoriBadge(string $kategori): string
    {
        if (strtolower($kategori) === 'pemasukan') {
            return '<span class="inline-flex px-2 py-1 bg-teal-100 text-teal-700 text-xs font-bold rounded-md uppercase tracking-wider">Pemasukan</span>';
        }

        return '<span class="inline-flex px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-md uppercase tracking-wider">Potongan</span>';
    }


    public function index()
    {
        $role           = session()->get('role');
        $region_patient = session()->get('region_patient');
        $regionId       = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : null;

        // Ambil daftar terapis
        $terapisQuery = $this->db->table('terapis t')
            ->select('t.id, t.nama, j.nama_jabatan, r.name as wilayah, gk.tipe_gaji, gk.nominal_gaji, gk.potong_absen, gk.nominal_potong_absen')
            ->join('jabatan j', 'j.id = t.jabatan_id', 'left')
            ->join('regions r', 'r.id = t.region_id', 'left')
            ->join('gaji_karyawan gk', 'gk.terapis_id = t.id', 'left')
            ->where('t.is_active', 1);

        if ($regionId) {
            $terapisQuery->where('t.region_id', $regionId);
        }

        $terapisList = $terapisQuery->get()->getResultArray();

        // Count active routine deductions per therapist
        $potonganCountRows = $this->db->table('potongan_rutin')
            ->select('terapis_id, COUNT(id) as cnt')
            ->where('is_active', 1)
            ->groupBy('terapis_id')
            ->get()->getResultArray();
        $potonganCounts = array_column($potonganCountRows, 'cnt', 'terapis_id');

        // Count allowances/benefits per therapist
        $masterItems = $this->db->table('tunjangan_karyawan')->get()->getResultArray();
        $allowanceCounts = [];
        foreach ($masterItems as $item) {
            $ids = json_decode($item['terapis_ids'] ?? '[]', true);
            if (is_array($ids)) {
                foreach ($ids as $tid) {
                    $allowanceCounts[$tid] = ($allowanceCounts[$tid] ?? 0) + 1;
                }
            }
        }

        foreach ($terapisList as &$t) {
            $tid = (int)$t['id'];
            $t['allowance_count'] = $allowanceCounts[$tid] ?? 0;
            $t['potongan_count'] = $potonganCounts[$tid] ?? 0;
        }

        // Ambil terapis untuk pilihan di form master item
        $terapisPilihan = $this->db->table('terapis t')
            ->select('t.id, t.nama')
            ->where('t.is_active', 1);
        if ($regionId) {
            $terapisPilihan->where('t.region_id', $regionId);
        }

        $data = [
            'title'             => 'Master Gaji',
            'terapis_gaji'      => $terapisList,
            'terapis'           => $terapisPilihan->get()->getResultArray(),
            'region_id'         => $regionId,
        ];
        return view('App\modules\tunjangan_karyawan\Views\index', $data);
    }

    public function fetch()
    {
        $region_patient = session()->get('region_patient');
        $regionId       = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : null;

        $items = $regionId
            ? $this->Mtunjangan->getByRegion($regionId)
            : $this->Mtunjangan->orderBy('kategori','ASC')->orderBy('nama_tunjangan','ASC')->findAll();

        $data = [];
        $no   = 1;

        foreach ($items as $row) {
            $terapisIds  = json_decode($row['terapis_ids'] ?? '[]', true);
            $jumlah      = count($terapisIds);
            
            $terapisNames = [];
            if (!empty($terapisIds)) {
                $namesResult = $this->db->table('terapis')
                    ->select('nama')
                    ->whereIn('id', $terapisIds)
                    ->where('is_active', 1)
                    ->get()
                    ->getResultArray();
                $terapisNames = array_column($namesResult, 'nama');
            }
            
            if ($row['kategori'] === 'tunjangan') {
                $kategoriBadge = '<span class="inline-flex px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-md">Tunjangan</span>';
            } elseif ($row['kategori'] === 'benefit') {
                $kategoriBadge = '<span class="inline-flex px-2 py-1 bg-teal-100 text-teal-700 text-xs font-bold rounded-md">Benefit (Non-Cash)</span>';
            } else {
                $kategoriBadge = '<span class="inline-flex px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-md">Potongan</span>';
            }

            $tipeBadge = $row['tipe'] === 'harian'
                ? '<span class="text-xs text-amber-600 font-semibold">Harian</span>'
                : '<span class="text-xs text-blue-600 font-semibold">Bulanan</span>';

            $data[] = [
                'no'             => $no++,
                'id'             => $row['id'],
                'nama_tunjangan' => esc($row['nama_tunjangan']),
                'kategori'       => $kategoriBadge,
                'nominal'        => 'Rp ' . number_format($row['nominal'], 0, ',', '.') . ' / ' . ($row['tipe'] === 'harian' ? 'hari' : 'bulan'),
                'tipe'           => $tipeBadge,
                'terapis_count'  => $jumlah . ' terapis',
                'terapis_names'  => $terapisNames,
            ];
        }

        return $this->response->setJSON(['data' => $data, 'csrfHash' => csrf_hash()]);
    }

    public function store()
    {
        $idRaw      = $this->request->getPost('id');
        $id         = !empty($idRaw) ? (int) $idRaw : null;
        $nominal    = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $terapisIds = $this->request->getPost('terapis_ids') ?? [];

        $region_patient = session()->get('region_patient');
        $regionId       = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : null;

        $dataSimpan = [
            'nama_tunjangan' => trim($this->request->getPost('nama_tunjangan')),
            'kategori'       => trim($this->request->getPost('kategori')),
            'nominal'        => $nominal,
            'tipe'           => $this->request->getPost('tipe') ?? 'bulanan',
            'terapis_ids'    => json_encode(array_map('intval', $terapisIds)),
            'region_id'      => $regionId,
        ];

        if ($id !== null) {
            $this->Mtunjangan->update($id, $dataSimpan);
            $pesan = 'Master gaji berhasil diperbarui.';
        } else {
            $this->Mtunjangan->insert($dataSimpan);
            $pesan = 'Master gaji berhasil ditambahkan.';
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $pesan, 'csrfHash' => csrf_hash()]);
    }

    public function detail($id)
    {
        $item = $this->Mtunjangan->find($id);
        if (!$item) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }
        return $this->response->setJSON(['status' => 'success', 'data' => $item]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak.']);
        }

        try {
            if ($this->Mtunjangan->delete($id)) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status'   => 'success',
                    'message'  => 'Data master tunjangan berhasil dihapus.',
                    'csrfHash' => csrf_hash()
                ]);
            }

            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus data.'
            ]);
        } catch (DatabaseException $e) {
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                return $this->response->setStatusCode(409)->setJSON([ // 409 Conflict
                    'status'  => 'error',
                    'message' => 'Konflik: Data ini tidak bisa dihapus karena sudah dipakai dalam riwayat tunjangan karyawan.'
                ]);
            }

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem pada database.'
            ]);
        }
    }

    public function terapisDetail($id)
    {
        $id = (int)$id;
        
        // 1. Fetch therapist basic info
        $terapis = $this->db->table('terapis t')
            ->select('t.id, t.nama, j.nama_jabatan, r.name as wilayah')
            ->join('jabatan j', 'j.id = t.jabatan_id', 'left')
            ->join('regions r', 'r.id = t.region_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();
            
        if (!$terapis) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terapis tidak ditemukan']);
        }
        
        // 2. Fetch basic salary settings
        $gaji = $this->db->table('gaji_karyawan')
            ->where('terapis_id', $id)
            ->get()->getRowArray();
            
        if (!$gaji) {
            $gaji = [
                'terapis_id' => $id,
                'tipe_gaji' => '',
                'nominal_gaji' => 0,
                'potong_absen' => 0,
                'nominal_potong_absen' => 0
            ];
        }
        
        // 3. Fetch all master allowances & benefits and check if this therapist is selected
        $region_patient = session()->get('region_patient');
        $regionId       = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : null;
            
        $itemsQuery = $this->db->table('tunjangan_karyawan');
        if ($regionId) {
            $itemsQuery->where('region_id', $regionId);
        }
        $masterItems = $itemsQuery->orderBy('kategori', 'ASC')->orderBy('nama_tunjangan', 'ASC')->get()->getResultArray();
        
        $allowancesList = [];
        foreach ($masterItems as $item) {
            $ids = json_decode($item['terapis_ids'] ?? '[]', true);
            $isChecked = is_array($ids) && in_array($id, $ids);
            
            $allowancesList[] = [
                'id' => $item['id'],
                'nama' => esc($item['nama_tunjangan']),
                'kategori' => $item['kategori'], // tunjangan, benefit, potongan
                'tipe' => $item['tipe'], // bulanan, harian
                'nominal' => $item['nominal'],
                'checked' => $isChecked
            ];
        }
        
        // 4. Fetch routine deductions (potongan_rutin)
        $potongan = $this->db->table('potongan_rutin')
            ->where('terapis_id', $id)
            ->where('is_active', 1)
            ->get()->getResultArray();
            
        return $this->response->setJSON([
            'status' => 'success',
            'terapis' => $terapis,
            'gaji' => $gaji,
            'allowances' => $allowancesList,
            'potongan_rutin' => $potongan,
            'csrfHash' => csrf_hash()
        ]);
    }

    public function saveTerapisSettings()
    {
        $terapisId = (int)$this->request->getPost('terapis_id');
        if (!$terapisId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Terapis tidak valid']);
        }
        
        $userId = session()->get('userId');
        
        // Begin Transaction
        $this->db->transStart();
        
        // 1. Save / Update Basic Salary settings in gaji_karyawan
        $tipeGajiRaw = $this->request->getPost('tipe_gaji');
        $tipeGaji = in_array($tipeGajiRaw, ['bulanan', 'harian']) ? $tipeGajiRaw : 'bulanan';
        $nominalGaji = (float)preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_gaji'));
        $potongAbsen = $this->request->getPost('potong_absen') ? 1 : 0;
        $nominalPotongAbsen = (float)preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_potong_absen') ?? '0');
        
        $gkBuilder = $this->db->table('gaji_karyawan');
        $existsGk = $gkBuilder->where('terapis_id', $terapisId)->get()->getRowArray();
        
        $dataGk = [
            'terapis_id' => $terapisId,
            'tipe_gaji' => $tipeGaji,
            'nominal_gaji' => $nominalGaji,
            'potong_absen' => $potongAbsen,
            'nominal_potong_absen' => $nominalPotongAbsen,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existsGk) {
            $gkBuilder->where('terapis_id', $terapisId)->update($dataGk);
        } else {
            $dataGk['created_at'] = date('Y-m-d H:i:s');
            $gkBuilder->insert($dataGk);
        }
        
        // 2. Mapped Allowances / Benefits update in tunjangan_karyawan
        $selectedAllowanceIds = $this->request->getPost('allowances') ?? [];
        $selectedAllowanceIds = array_map('intval', $selectedAllowanceIds);
        
        // Fetch all master items
        $masterItems = $this->db->table('tunjangan_karyawan')->get()->getResultArray();
        foreach ($masterItems as $item) {
            $ids = json_decode($item['terapis_ids'] ?? '[]', true);
            if (!is_array($ids)) {
                $ids = [];
            }
            
            $isInSelected = in_array((int)$item['id'], $selectedAllowanceIds);
            $isInIds = in_array($terapisId, $ids);
            
            if ($isInSelected && !$isInIds) {
                // Add
                $ids[] = $terapisId;
                $this->db->table('tunjangan_karyawan')
                    ->where('id', $item['id'])
                    ->update(['terapis_ids' => json_encode(array_values(array_map('intval', $ids)))]);
            } elseif (!$isInSelected && $isInIds) {
                // Remove
                $ids = array_filter($ids, fn($val) => $val !== $terapisId);
                $this->db->table('tunjangan_karyawan')
                    ->where('id', $item['id'])
                    ->update(['terapis_ids' => json_encode(array_values(array_map('intval', $ids)))]);
            }
        }
        
        // 3. Routine Deductions update in potongan_rutin
        $potonganNames = $this->request->getPost('potongan_nama') ?? [];
        $potonganNominals = $this->request->getPost('potongan_nominal') ?? [];
        
        // Delete previous
        $this->db->table('potongan_rutin')
            ->where('terapis_id', $terapisId)
            ->delete();
            
        // Insert new ones
        for ($i = 0; $i < count($potonganNames); $i++) {
            $nama = trim($potonganNames[$i]);
            $nom = (float)preg_replace('/[^0-9]/', '', $potonganNominals[$i]);
            
            if (!empty($nama) && $nom > 0) {
                $this->db->table('potongan_rutin')->insert([
                    'terapis_id' => $terapisId,
                    'nama_potongan' => $nama,
                    'nominal' => $nom,
                    'is_active' => 1,
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        $this->db->transComplete();
        
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan pengaturan', 'csrfHash' => csrf_hash()]);
        }
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Pengaturan gaji terapis berhasil disimpan', 'csrfHash' => csrf_hash()]);
    }

    /**
     * Simpan hanya pengaturan gaji pokok terapis (tipe_gaji, nominal_gaji, potong_absen).
     * Tidak mengubah tunjangan atau potongan rutin.
     */
    public function saveGajiPokok()
    {
        $terapisId = (int)$this->request->getPost('terapis_id');
        if (!$terapisId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Terapis tidak valid', 'csrfHash' => csrf_hash()]);
        }

        $tipeGajiRaw = $this->request->getPost('tipe_gaji');
        $tipeGaji    = in_array($tipeGajiRaw, ['bulanan', 'harian']) ? $tipeGajiRaw : 'bulanan';
        $nominalGaji = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_gaji') ?? '0');
        $potongAbsen = $this->request->getPost('potong_absen') ? 1 : 0;
        $nominalPotongAbsen = (float) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_potong_absen') ?? '0');

        $gkBuilder = $this->db->table('gaji_karyawan');
        $exists    = $gkBuilder->where('terapis_id', $terapisId)->get()->getRowArray();

        $dataGk = [
            'terapis_id'           => $terapisId,
            'tipe_gaji'            => $tipeGaji,
            'nominal_gaji'         => $nominalGaji,
            'potong_absen'         => $potongAbsen,
            'nominal_potong_absen' => $nominalPotongAbsen,
            'updated_at'           => date('Y-m-d H:i:s'),
        ];

        if ($exists) {
            $this->db->table('gaji_karyawan')->where('terapis_id', $terapisId)->update($dataGk);
        } else {
            $dataGk['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('gaji_karyawan')->insert($dataGk);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Gaji pokok berhasil disimpan.',
            'csrfHash' => csrf_hash(),
        ]);
    }
}
