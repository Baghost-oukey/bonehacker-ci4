<?php

namespace App\modules\absensi_karyawan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\absensi_karyawan\Models\Mabsensikaryawan;
use App\modules\terapis\Models\MTerapis;

class Absensikaryawan extends BaseController
{

    protected $model_terapis;
    protected $model_absensi;

    public function __construct()
    {
        $this->model_terapis = new MTerapis();
        $this->model_absensi = new Mabsensikaryawan();
    }

    public function index()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $terapisQuery = $this->model_terapis->where('is_active', 1);
        if (!empty($allowed_regions)) {
            if (is_array($allowed_regions)) {
                $terapisQuery->whereIn('region_id', $allowed_regions);
            } else {
                $terapisQuery->where('region_id', $allowed_regions);
            }
        }

        $data = [
            'title'        => 'Presensi Harian',
            'terapis'      => $terapisQuery->findAll(),
            'rekap_harian' => $this->model_absensi->getRekapHarian($bulan, $tahun),
            'filter_bulan' => $bulan,
            'filter_tahun' => $tahun,
            'tanggal'      => date('Y-m-d')
        ];

        return view('App\modules\absensi_karyawan\Views\index', $data);
    }

    public function export()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $rekap = $this->model_absensi->getRekapHarian($bulan, $tahun);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No')
              ->setCellValue('B1', 'Tanggal')
              ->setCellValue('C1', 'Total Hadir')
              ->setCellValue('D1', 'Total Tidak Hadir');

        $row = 2;
        foreach ($rekap as $index => $r) {
            $sheet->setCellValue('A' . $row, $index + 1)
                  ->setCellValue('B' . $row, date('d-m-Y', strtotime($r['tanggal'])))
                  ->setCellValue('C' . $row, $r['total_hadir'])
                  ->setCellValue('D' . $row, $r['total_tidak_hadir']);
            $row++;
        }

        $filename = 'Rekap_Presensi_' . $bulan . '_' . $tahun . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function store($tanggal = null)
    {
        if ($tanggal === null || !strtotime($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $absensiByTanggal = $this->model_absensi->getByTanggal($tanggal);
        $rekapByTanggal = [];

        foreach ($absensiByTanggal as $row) {
            $rekapByTanggal[$row['terapis_id']] = $row;
        }

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $terapisQuery = $this->model_terapis->where('is_active', 1);
        if (!empty($allowed_regions)) {
            if (is_array($allowed_regions)) { $terapisQuery->whereIn('region_id', $allowed_regions); }
            else { $terapisQuery->where('region_id', $allowed_regions); }
        }

        $data = [
            'title'            => 'Input Presensi',
            'terapis'          => $terapisQuery->findAll(),
            'tanggal'          => $tanggal,
            'rekap_by_tanggal' => $rekapByTanggal
        ];

        return view('App\modules\absensi_karyawan\Views\absensi', $data);
    }

    public function detail($tanggal = null)
    {
        return $this->store($tanggal);
    }

    public function simpan_massal()
    {
        $postAbsen = $this->request->getPost('absen');
        $tanggal   = $this->request->getPost('tanggal');

        if (empty($postAbsen) || empty($tanggal)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak valid.']);
        }

        $dataInsert = [];
        foreach ($postAbsen as $row) {
            $dataInsert[] = [
                'terapis_id' => $row['terapis_id'],
                'tanggal'    => $tanggal,
                'status'     => $row['status'],
                'keterangan' => $row['keterangan'] ?? null
            ];
        }

        // DATABASE TRANSACTION (BEST PRACTICE)
        $db = \Config\Database::connect();
        $db->transStart(); // Mulai proteksi transaksi
        $this->model_absensi->where('tanggal', $tanggal)->delete();
        $this->model_absensi->insertBatch($dataInsert);

        $db->transComplete(); 
        if ($db->transStatus() === true) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Presensi tanggal ' . date('d M Y', strtotime($tanggal)) . ' berhasil disimpan!',
                'csrfHash' => csrf_hash()
            ]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan ke database. Sistem telah membatalkan perubahan.']);
    }
}
