<?php

namespace App\modules\detail_gaji\Controllers;

use App\Controllers\BaseController;
use App\modules\gaji\Models\Mgajikaryawan;

class Detailgaji extends BaseController
{
    protected $Mgaji;
    protected $db;

    public function __construct()
    {
        $this->Mgaji = new Mgajikaryawan();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return redirect()->to(base_url('gaji'));
    }

    public function review($terapisId)
    {
        $dataDetail = $this->Mgaji->getDetailPerhitungan($terapisId);
        
        if (empty($dataDetail['terapis'])) {
            return redirect()->to(base_url('gaji'))->with('error', 'Data terapis tidak ditemukan.');
        }

        $data = [
            'title'   => 'Review Gaji Karyawan',
            'terapis' => $dataDetail['terapis']
        ];

        return view('App\modules\detail_gaji\Views\review', $data);
    }

    public function proses_simpan()
    {
        // Reuse logic from Gajikaryawan or implement here
        // Since we want to use AJAX (as per review_gaji.js), we return JSON
        
        $terapisId = (int)$this->request->getPost('terapis_id');
        $totalKehadiran = (int)$this->request->getPost('total_kehadiran');

        // Validation logic similar to Gajikaryawan::prosesBayar
        if (empty($terapisId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID terapis tidak valid.', 'csrfHash' => csrf_hash()]);
        }

        // ... (implementing the same logic as prosesBayar but for AJAX)
        
        $gajiData = $this->db->table('gaji_karyawan')->where('terapis_id', $terapisId)->get()->getRowArray();
        if (empty($gajiData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pengaturan gaji belum diset.', 'csrfHash' => csrf_hash()]);
        }

        $tipeGaji = $gajiData['tipe_gaji'];
        $nominalGaji = (int)$gajiData['nominal_gaji'];
        $gajiPokokTotal = ($tipeGaji === 'harian') ? ($nominalGaji * $totalKehadiran) : $nominalGaji;

        $totalPotongan = (int)($this->db->table('kasbon_karyawan')
            ->selectSum('nominal', 'total')
            ->where('terapis_id', $terapisId)
            ->whereIn('status_potongan', ['belum_lunas', 'belum_dipotong'])
            ->get()
            ->getRowArray()['total'] ?? 0);

        $totalTunjangan = (int)($this->db->table('transaksi_tunjangan')
            ->selectSum('nominal', 'total')
            ->where('terapis_id', $terapisId)
            ->where('status_pembayaran', 'Belum Dibayar')
            ->get()
            ->getRowArray()['total'] ?? 0);

        $gajiBersih = $gajiPokokTotal + $totalTunjangan - $totalPotongan;

        if ($gajiBersih < 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gaji bersih tidak boleh negatif.', 'csrfHash' => csrf_hash()]);
        }

        $dataGaji = [
            'terapis_id'       => $terapisId,
            'periode_bulan'    => date('n'),
            'periode_tahun'    => date('Y'),
            'total_kehadiran'  => $totalKehadiran,
            'gaji_pokok_total' => $gajiPokokTotal,
            'total_tunjangan'  => $totalTunjangan,
            'total_potongan'   => $totalPotongan,
            'gaji_bersih'      => $gajiBersih,
            'tanggal_bayar'    => date('Y-m-d H:i:s'),
            'status'           => 'lunas'
        ];

        $this->db->transStart();
        $this->Mgaji->insert($dataGaji);

        $this->db->table('kasbon_karyawan')
            ->where('terapis_id', $terapisId)
            ->whereIn('status_potongan', ['belum_lunas', 'belum_dipotong'])
            ->update(['status_potongan' => 'lunas']);

        $this->db->table('transaksi_tunjangan')
            ->where('terapis_id', $terapisId)
            ->where('status_pembayaran', 'Belum Dibayar')
            ->update(['status_pembayaran' => 'Sudah Cair']);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses gaji.', 'csrfHash' => csrf_hash()]);
        }

        return $this->response->setJSON([
            'status' => 'success', 
            'message' => 'Gaji berhasil dibayarkan.', 
            'csrfHash' => csrf_hash()
        ]);
    }
}
