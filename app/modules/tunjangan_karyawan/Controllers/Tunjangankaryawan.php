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

        // Ambil daftar terapis untuk pilihan
        $terapis = $this->db->table('terapis t')
            ->select('t.id, t.nama')
            ->where('t.is_active', 1);

        if ($regionId) {
            $terapis->where('t.region_id', $regionId);
        }

        $data = [
            'title'    => 'Master Gaji',
            'terapis'  => $terapis->get()->getResultArray(),
            'region_id'=> $regionId,
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
            $kategoriBadge = $row['kategori'] === 'tunjangan'
                ? '<span class="inline-flex px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-md">Tunjangan</span>'
                : '<span class="inline-flex px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-md">Potongan</span>';
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
}
