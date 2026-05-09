<?php

namespace App\modules\tunjangan_karyawan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\tunjangan_karyawan\Models\Mtunjangankaryawan;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Tunjangankaryawan extends BaseController
{

    protected $Mtunjangan;

    public function __construct()
    {
        $this->Mtunjangan = new Mtunjangankaryawan();
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
        $data = [
            'title' => 'Master Data Tunjangan Karyawan'
        ];
        return view('App\modules\tunjangan_karyawan\Views\index', $data);
    }

    public function fetch()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak.']);
        }

        $tunjangan = $this->Mtunjangan->orderBy('nama_tunjangan', 'ASC')->findAll();
        $data      = [];
        $no        = 1;

        foreach ($tunjangan as $row) {
            $data[] = [
                'no'             => $no++,
                'id'             => $row['id'],
                'nama_tunjangan' => esc($row['nama_tunjangan']),
                'kategori'       => $this->formatKategoriBadge(esc($row['kategori']))
            ];
        }

        return $this->response->setStatusCode(200)->setJSON(['data' => $data]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak.']);
        }

        $idRaw = $this->request->getPost('id');
        $id    = !empty($idRaw) ? (int) $idRaw : null;

        $dataSimpan = [
            'nama_tunjangan' => trim((string) $this->request->getPost('nama_tunjangan')),
            'kategori'       => trim((string) $this->request->getPost('kategori'))
        ];

        if ($id !== null) {
            if (!$this->Mtunjangan->update($id, $dataSimpan)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'errors' => $this->Mtunjangan->errors()
                ]);
            }
            $pesan = 'Data master tunjangan berhasil diperbarui.';
        } else {
            if (!$this->Mtunjangan->insert($dataSimpan)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'errors' => $this->Mtunjangan->errors()
                ]);
            }
            $pesan = 'Jenis tunjangan baru berhasil ditambahkan.';
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status'   => 'success',
            'message'  => $pesan,
            'csrfHash' => csrf_hash() 
        ]);
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
