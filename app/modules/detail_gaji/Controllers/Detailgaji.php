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
            'title'    => 'Review Gaji Karyawan',
            'terapis'  => $dataDetail['terapis'],
            'komponen' => $dataDetail['komponen'],
        ];

        return view('App\modules\detail_gaji\Views\review', $data);
    }

    public function proses_simpan()
    {
        $terapisId = (int)$this->request->getPost('terapis_id');

        if (empty($terapisId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID terapis tidak valid.', 'csrfHash' => csrf_hash()]);
        }

        // Hitung ulang dari model untuk keakuratan
        $dataDetail = $this->Mgaji->getDetailPerhitungan($terapisId);
        if (empty($dataDetail['terapis'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.', 'csrfHash' => csrf_hash()]);
        }

        $k          = $dataDetail['komponen'];
        $gajiBersih = $k['gaji_bersih'];

        $dataGaji = [
            'terapis_id'       => $terapisId,
            'periode_bulan'    => date('n'),
            'periode_tahun'    => date('Y'),
            'total_kehadiran'  => $k['kehadiran'],
            'gaji_pokok_total' => $k['gaji_pokok'],
            'total_tunjangan'  => $k['total_A'] - $k['gaji_pokok'] + $k['total_B'],
            'total_potongan'   => $k['total_C'],
            'gaji_bersih'      => $gajiBersih,
            'tanggal_bayar'    => date('Y-m-d H:i:s'),
            'status'           => 'lunas'
        ];

        $this->db->transStart();
        $riwayatId = $this->Mgaji->insert($dataGaji, true); // true = return insert ID

        // Simpan detail komponen sebagai snapshot
        $detailBatch = [];

        // Take Home
        $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'take_home', 'nama_komponen' => 'Gaji Pokok', 'nominal' => $k['gaji_pokok']];
        if ($k['jaspel_reguler'] > 0)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'take_home', 'nama_komponen' => 'Jasa Pelayanan Reguler', 'nominal' => $k['jaspel_reguler']];
        if ($k['jaspel_kejantanan'] > 0)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'take_home', 'nama_komponen' => 'Jasa Terapi Kejantanan', 'nominal' => $k['jaspel_kejantanan']];

        // Benefit
        foreach ($k['benefit_list'] as $b)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'benefit', 'nama_komponen' => $b['nama'], 'nominal' => $b['nominal']];

        // Potongan
        foreach ($k['potongan_list'] as $p)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'potongan', 'nama_komponen' => $p['nama'], 'nominal' => $p['nominal']];
        if ($k['total_kasbon'] > 0)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'potongan', 'nama_komponen' => 'Cicilan Kasbon', 'nominal' => $k['total_kasbon']];

        if (!empty($detailBatch))
            $this->db->table('riwayat_gaji_detail')->insertBatch($detailBatch);

        // Lunasi kasbon
        $this->db->table('kasbon_karyawan')
            ->where('terapis_id', $terapisId)
            ->where('status_potongan', 'belum_lunas')
            ->update(['status_potongan' => 'lunas']);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses gaji.', 'csrfHash' => csrf_hash()]);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Gaji berhasil dibayarkan.',
            'csrfHash' => csrf_hash()
        ]);
    }
}
