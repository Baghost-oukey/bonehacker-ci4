<?php

namespace App\modules\gaji\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\gaji\Models\Mgajikaryawan;

class Gajikaryawan extends BaseController
{

    protected $Mriwayatgaji;
    protected $db;

    public function __construct()
    {
        $this->Mriwayatgaji = new Mgajikaryawan();
        $this->db = \Config\Database::connect();
    }


    public function index()
    {
        $regionId = $this->request->getGet('region_id') ?? 'all';
        // Filter untuk Tab 2 (Riwayat), default bulan dan tahun saat ini
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title'            => 'Kelola Gaji Karyawan',
            'estimasi_gaji'    => $this->Mriwayatgaji->getPayrollEstimates($regionId), // Data Tab 1
            'riwayat_gaji'     => $this->Mriwayatgaji->getPayrollHistory($bulan, $tahun, $regionId), // Data Tab 2
            'filter_region'    => $regionId,
            'filter_bulan'     => $bulan,
            'filter_tahun'     => $tahun
        ];

        return view('App\modules\gaji\Views\index', $data);
    }

    public function saveSetting()
    {
        $terapisId   = $this->request->getPost('terapis_id');
        $tipeGaji    = $this->request->getPost('tipe_gaji');
        $nominalGaji = preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_gaji'));
        $builder = $this->db->table('gaji_karyawan');
        $cekData = $builder->where('terapis_id', $terapisId)->get()->getRowArray();
        $dataSimpan = [
            'terapis_id'   => $terapisId,
            'tipe_gaji'    => $tipeGaji,
            'nominal_gaji' => $nominalGaji,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($cekData) {
            $builder->where('terapis_id', $terapisId)->update($dataSimpan);
            $pesan = 'Pengaturan gaji berhasil diperbarui.';
        } else {
            $dataSimpan['created_at'] = date('Y-m-d H:i:s');
            $builder->insert($dataSimpan);
            $pesan = 'Pengaturan gaji berhasil ditambahkan.';
        }

        return redirect()->to('/gaji')->with('success', $pesan);
    }


    public function detailEstimasi($terapisId)
    {
        $detail = $this->Mriwayatgaji->getDetailPerhitungan($terapisId);
        if ($detail) {
            return $this->response->setJSON(['status' => 'success', 'data' => $detail]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }


    public function prosesBayar()
    {
        $terapisId = $this->request->getPost('terapis_id');
        $dataGaji = [
            'terapis_id'       => $terapisId,
            'periode_bulan'    => date('n'),
            'periode_tahun'    => date('Y'),
            'total_kehadiran'  => $this->request->getPost('total_kehadiran') ?? 0,
            'gaji_pokok_total' => preg_replace('/[^0-9]/', '', $this->request->getPost('gaji_pokok_total')),
            'total_tunjangan'  => preg_replace('/[^0-9]/', '', $this->request->getPost('total_tunjangan')),
            'total_potongan'   => preg_replace('/[^0-9]/', '', $this->request->getPost('total_potongan')),
            'gaji_bersih'      => preg_replace('/[^0-9]/', '', $this->request->getPost('gaji_bersih')),
            'tanggal_bayar'    => date('Y-m-d H:i:s'),
            'status'           => 'lunas'
        ];

        $this->db->transStart();
        $this->Mriwayatgaji->insert($dataGaji);
        $this->db->table('kasbon')
            ->where('terapis_id', $terapisId)
            ->where('status_potongan', 'belum_dipotong')
            ->update(['status_potongan' => 'sudah_dipotong']);

        // 4. Ubah status tindakan terapis menjadi 'sudah_dibayar' (Jika ada tabel tindakannya)
        // $this->db->table('transaksi_tindakan')
        //          ->where('terapis_id', $terapisId)
        //          ->where('status_gaji', 'belum_dibayar')
        //          ->update(['status_gaji' => 'sudah_dibayar']);

        // 5. Selesaikan Transaction
        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return redirect()->to('/gaji')->with('error', 'Gagal memproses gaji karyawan.');
        }

        return redirect()->to('/gaji')->with('success', 'Gaji karyawan berhasil diproses dan dibayarkan.');
    }
}
