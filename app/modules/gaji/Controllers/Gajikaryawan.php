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
        $region_patient = session()->get('region_patient');
        $sessionRegionId = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : 'all';

        $regionId = $this->request->getGet('region_id') ?? $sessionRegionId;
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title'            => 'Kelola Gaji Karyawan',
            'estimasi_gaji'    => $this->Mriwayatgaji->getPayrollEstimates($regionId),
            'riwayat_gaji'     => $this->Mriwayatgaji->getPayrollHistory($bulan, $tahun, $regionId),
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
        if (!empty($detail['terapis'])) {
            return $this->response->setJSON(['status' => 'success', 'data' => $detail['terapis']]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }


    public function prosesBayar()
    {
        $terapisId = (int)$this->request->getPost('terapis_id');
        $totalKehadiran = (int)$this->request->getPost('total_kehadiran');

        // --- VALIDASI SERVER-SIDE ---
        if (empty($terapisId) || $terapisId <= 0) {
            return redirect()->to('/gaji')->with('error', 'ID terapis tidak valid.');
        }

        // Pastikan terapis ada dan aktif
        $terapis = $this->db->table('terapis')
            ->where('id', $terapisId)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if (empty($terapis)) {
            return redirect()->to('/gaji')->with('error', 'Terapis tidak ditemukan atau tidak aktif.');
        }

        // Pastikan gaji sudah di-set
        $gajiData = $this->db->table('gaji_karyawan')
            ->where('terapis_id', $terapisId)
            ->get()
            ->getRowArray();

        if (empty($gajiData) || empty($gajiData['tipe_gaji'])) {
            return redirect()->to('/gaji')->with('error', 'Pengaturan gaji belum diset untuk karyawan ini.');
        }

        // Validasi total kehadiran berdasarkan tipe gaji
        $tipeGaji = $gajiData['tipe_gaji'] ?? 'bulanan';
        
        if ($tipeGaji === 'harian' && $totalKehadiran <= 0) {
            return redirect()->to('/gaji')->with('error', 'Total kehadiran harus lebih dari 0 untuk gaji harian.');
        }

        if ($totalKehadiran < 0) {
            return redirect()->to('/gaji')->with('error', 'Total kehadiran tidak boleh negatif.');
        }

        // --- PERHITUNGAN GAJI ---
        $nominalGaji = isset($gajiData['nominal_gaji']) ? (int)$gajiData['nominal_gaji'] : 0;

        if ($nominalGaji <= 0) {
            return redirect()->to('/gaji')->with('error', 'Nominal gaji tidak valid.');
        }

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
            return redirect()->to('/gaji')->with('error', 'Gaji bersih tidak boleh negatif (potongan lebih besar dari gaji pokok + tunjangan).');
        }

        // --- PROSES PEMBAYARAN ---
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
        $this->Mriwayatgaji->insert($dataGaji);

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
            return redirect()->to('/gaji')->with('error', 'Gagal memproses gaji karyawan.');
        }

        return redirect()->to('/gaji')->with('success', 'Gaji karyawan berhasil diproses dan dibayarkan.');
    }
    public function fetchEstimasi()
    {
        $region_patient = session()->get('region_patient');
        $sessionRegionId = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : 'all';
        $regionId = $this->request->getGet('region_id') ?? $sessionRegionId;

        $data = $this->Mriwayatgaji->getPayrollEstimates($regionId);
        return $this->response->setJSON([
            'status'   => 'success',
            'data'     => $data,
            'csrfHash' => csrf_hash()
        ]);
    }
}
