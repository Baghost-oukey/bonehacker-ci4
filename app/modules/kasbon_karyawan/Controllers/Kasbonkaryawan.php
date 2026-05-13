<?php

namespace App\modules\kasbon_karyawan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\kasbon_karyawan\Models\Mkasbonkaryawan;

class Kasbonkaryawan extends BaseController
{

    protected $Mkasbon;
    protected $db;


    public function __construct()
    {
        $this->Mkasbon = new Mkasbonkaryawan();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        // Hanya owner yang bisa akses
        $role = session()->get('role');
        if (!in_array($role, ['owner', 'admin', 'superadmin'])) {
            return redirect()->back()->with('message', ['error', 'Akses Ditolak', 'Halaman ini hanya untuk owner.']);
        }

        $data = ['title' => 'Kelola Kasbon Karyawan'];
        return view('App\modules\kasbon_karyawan\Views\index', $data);
    }

    public function fetchKaryawan()
    {
        $draw   = $this->request->getPost('draw');
        $start  = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $search = $this->request->getPost('search')['value'];

        $region_patient = session()->get('region_patient');
        $regionFilter   = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

        $dataRaw = $this->Mkasbon->get_datatables($search, $start, $length, $regionFilter);
        $data = [];

        foreach ($dataRaw as $row) {
            // Gunakan sisa_hutang untuk melihat beban hutang yang sebenarnya
            $kasbon = $this->db->table('kasbon_karyawan')
                ->selectSum('sisa_hutang')
                ->where('terapis_id', $row['id'])
                ->where('status_potongan', 'belum_lunas')
                ->get()->getRowArray();

            $totalHutang = $kasbon['sisa_hutang'] ?? 0;

            $data[] = [
                'id'           => $row['id'],
                'nama'         => $row['nama'] ?? 'Tanpa Nama',
                'jabatan'      => $row['nama_jabatan'] ?? 'STAF',
                'gaji_pokok'   => 'Rp ' . number_format($row['nominal_gaji'] ?? 0, 0, ',', '.'),
                'kasbon_aktif' => ($totalHutang > 0) ? '- Rp ' . number_format($totalHutang, 0, ',', '.') : 'Rp 0',
                'kasbon_raw'   => $totalHutang
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => $this->Mkasbon->count_all_active($regionFilter),
            "recordsFiltered" => $this->Mkasbon->count_filtered($search, $regionFilter),
            "data"            => $data,
            "csrfHash"        => csrf_hash()
        ]);
    }


    public function store()
    {
        $terapisId  = $this->request->getPost('terapis_id');
        $nominal    = (int) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal'));
        $keterangan = $this->request->getPost('keterangan');

        if ($nominal <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nominal tidak valid.']);
        }

        // Ambil info gaji dan hutang saat ini
        // $gaji = $this->db->table('gaji_karyawan')->where('terapis_id', $terapisId)->get()->getRowArray();
        // $gajiPokok = $gaji ? $gaji['nominal_gaji'] : 0;

        // if ($gajiPokok == 0) {
        //     return $this->response->setJSON(['status' => 'error', 'message' => 'Karyawan belum memiliki Gaji Pokok.']);
        // }

        // $totalHutangAktif = $this->Mkasbon->getTotalHutangAktif($terapisId);
        // $sisaLimit = $gajiPokok - $totalHutangAktif;

        // if ($nominal > $sisaLimit) {
        //     return $this->response->setJSON([
        //         'status' => 'error',
        //         'message' => 'Ditolak! Sisa limit plafon: Rp ' . number_format($sisaLimit, 0, ',', '.')
        //     ]);
        // }

        // Simpan dengan penanganan Transaksi (Keamanan Keuangan)
        $this->db->transStart();

        $dataSimpan = [
            'terapis_id'      => $terapisId,
            'tanggal'         => date('Y-m-d'),
            'nominal'         => $nominal,
            'sisa_hutang'     => $nominal, // Awal hutang = nominal
            'keterangan'      => $keterangan,
            'status_potongan' => 'belum_lunas'
        ];

        $this->Mkasbon->insert($dataSimpan);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan sistem saat mencatat kasbon.']);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Kasbon berhasil dicairkan!',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $karyawan = $this->Mkasbon->getDetailKaryawan($id);

        if (!$karyawan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Gunakan fungsi dari Model yang sudah kita buat tadi agar lebih bersih
        $totalHutangAktif = $this->Mkasbon->getTotalHutangAktif($id);
        $gajiPokok        = $karyawan['gaji_pokok'] ?? 0;

        $karyawan['total_kasbon_aktif'] = $totalHutangAktif;

        $data = [
            'title'    => 'Detail Kasbon - ' . $karyawan['nama'],
            'karyawan' => $karyawan,
            'riwayat'  => $this->Mkasbon->getHistoryByTerapis($id)
        ];

        return view('App\modules\kasbon_karyawan\Views\detail_kasbon', $data);
    }

    public function bayar()
    {
        // 1. Keamanan: Pastikan hanya bisa diakses via AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Akses langsung tidak diizinkan');
        }

        // 2. Ambil data dari form cicilan
        $terapisId  = $this->request->getPost('terapis_id');
        $nominal    = (int) preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_cicilan'));
        $keterangan = $this->request->getPost('keterangan_cicilan');

        if ($nominal <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Nominal pembayaran tidak valid.'
            ]);
        }

        // 3. Mulai Transaksi Database (Biar aman kalau di tengah jalan gagal)
        $this->db->transStart();

        // 4. Ambil semua kasbon aktif (belum lunas) milik karyawan ini, urutkan dari yang PALING LAMA (FIFO)
        $kasbonAktif = $this->db->table('kasbon_karyawan')
            ->where('terapis_id', $terapisId)
            ->where('status_potongan', 'belum_lunas')
            ->orderBy('id', 'ASC') // ASC = yang paling lama dilunasi duluan
            ->get()->getResultArray();

        $sisaUangPembayaran = $nominal;

        // 5. Logika FIFO: Putar (looping) data hutangnya dan potong satu per satu
        foreach ($kasbonAktif as $kb) {
            if ($sisaUangPembayaran <= 0) {
                break;
            }

            $idKasbon = $kb['id'];
            $hutangBarisIni = (int)$kb['sisa_hutang'];

            if ($sisaUangPembayaran >= $hutangBarisIni) {
                $this->db->table('kasbon_karyawan')->where('id', $idKasbon)->update([
                    'sisa_hutang'     => 0,
                    'status_potongan' => 'lunas' // Status otomatis berubah
                ]);

                $sisaUangPembayaran -= $hutangBarisIni;
            } else {
                $sisaHutangBaru = $hutangBarisIni - $sisaUangPembayaran;
                $this->db->table('kasbon_karyawan')->where('id', $idKasbon)->update([
                    'sisa_hutang' => $sisaHutangBaru
                ]);

                // Uang habis
                $sisaUangPembayaran = 0;
            }
        }

        // --- BAGIAN BARU: Hitung Saldo Hutang Terbaru ---
        $totalHutangSekarang = $this->db->table('kasbon_karyawan')
            ->selectSum('sisa_hutang')
            ->where('terapis_id', $terapisId)
            ->where('status_potongan', 'belum_lunas')
            ->get()->getRow()->sisa_hutang ?? 0;

        // --- BAGIAN BARU: Catat ke Riwayat dengan Nominal dan Saldo ---
        $this->db->table('kasbon_karyawan')->insert([
            'terapis_id'      => $terapisId,
            'tanggal'         => date('Y-m-d'),
            'nominal'         => $nominal, 
            'sisa_hutang'     => $totalHutangSekarang,
            'keterangan'      => $keterangan,
            'status_potongan' => 'lunas'
        ]);

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat memproses pembayaran.'
            ]);
        }

        // 8. Sukses!
        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Pembayaran sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' berhasil dicatat!',
            'csrfHash' => csrf_hash() // Update hash agar form tidak expired
        ]);
    }
}
