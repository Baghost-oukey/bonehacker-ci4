<?php

namespace App\modules\absensi_karyawan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\absensi_karyawan\Models\Mabsensikaryawan;
use App\Modules\Karyawan\Models\MKaryawan;

class Absensikaryawan extends BaseController
{

    protected $model_karyawan;
    protected $model_absensi;

    public function __construct()
    {
        $this->model_karyawan = new MKaryawan();
        $this->model_absensi = new Mabsensikaryawan();
    }

    public function index()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $terapisQuery = $this->model_karyawan->where('is_active', 1)->where('is_presensi', 1);
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
            'rekap_harian' => $this->model_absensi->getRekapHarian($bulan, $tahun, $allowed_regions),
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

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $rekap = $this->model_absensi->getRekapHarian($bulan, $tahun, $allowed_regions);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi');

        // --- 1. JUDUL & INFORMASI ---
        $bulan_list = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $nama_bulan = $bulan_list[(int)$bulan];

        $sheet->setCellValue('A1', 'LAPORAN REKAP PRESENSI HARIAN TERAPIS');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', "Periode: $nama_bulan $tahun");
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // --- 2. HEADER TABEL ---
        $sheet->setCellValue('A4', 'No')
            ->setCellValue('B4', 'Tanggal')
            ->setCellValue('C4', 'Hadir')
            ->setCellValue('D4', 'Tanpa Keterangan')
            ->setCellValue('E4', 'Izin')
            ->setCellValue('F4', 'Cuti');

        // Styling Header (Blue Theme like user screenshot)
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Blue-800
            ],
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // --- 3. ISI DATA ---
        $row = 5;
        foreach ($rekap as $index => $r) {
            $sheet->setCellValue('A' . $row, $index + 1)
                ->setCellValue('B' . $row, date('d-m-Y', strtotime($r['tanggal'])))
                ->setCellValue('C' . $row, $r['total_hadir'])
                ->setCellValue('D' . $row, $r['total_tidak_hadir'])
                ->setCellValue('E' . $row, $r['total_izin'])
                ->setCellValue('F' . $row, $r['total_cuti']);

            // Style baris data
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E2E8F0'], // Slate-200
                    ],
                ],
            ]);
            
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Rekap_Presensi_' . $nama_bulan . '_' . $tahun . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function presensi($tanggal = null)
    {
        if ($tanggal === null || !strtotime($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $absensiByTanggal = $this->model_absensi->getByTanggal($tanggal, $allowed_regions);
        $rekapByTanggal = [];

        foreach ($absensiByTanggal as $row) {
            $rekapByTanggal[$row['terapis_id']] = $row;
        }

        $terapisQuery = $this->model_karyawan->where('is_active', 1)->where('is_presensi', 1);
        if (!empty($allowed_regions)) {
            if (is_array($allowed_regions)) {
                $terapisQuery->whereIn('region_id', $allowed_regions);
            } else {
                $terapisQuery->where('region_id', $allowed_regions);
            }
        }

        $data = [
            'title'            => 'Input Presensi',
            'terapis'          => $terapisQuery->findAll(),
            'tanggal'          => $tanggal,
            'rekap_by_tanggal' => $rekapByTanggal
        ];

        return view('App\modules\absensi_karyawan\Views\presensi', $data);
    }

    public function detail($tanggal = null)
    {
        return $this->presensi($tanggal);
    }

    public function tambah()
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $terapisQuery = $this->model_karyawan
            ->select('terapis.*, regions.name as region_name')
            ->join('regions', 'regions.id = terapis.region_id', 'left')
            ->where('terapis.is_active', 1)
            ->where('terapis.is_presensi', 1);

        if (!empty($allowed_regions)) {
            if (is_array($allowed_regions)) {
                $terapisQuery->whereIn('terapis.region_id', $allowed_regions);
            } else {
                $terapisQuery->where('terapis.region_id', $allowed_regions);
            }
        }

        $data = [
            'title'   => 'Tambah Presensi',
            'terapis' => $terapisQuery->orderBy('terapis.nama', 'ASC')->findAll(),
        ];

        return view('App\modules\absensi_karyawan\Views\tambah_presensi', $data);
    }

    public function simpan_presensi_baru()
    {
        $tanggal = $this->request->getPost('tanggal');
        $terapisIds = $this->request->getPost('terapis_ids');

        if (empty($tanggal) || empty($terapisIds)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tanggal dan terapis wajib dipilih'
            ]);
        }

        // Validasi tanggal tidak boleh lebih dari hari ini
        $today = date('Y-m-d');
        if ($tanggal > $today) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak dapat input presensi untuk tanggal masa depan'
            ]);
        }

        $dataInsert = [];
        foreach ($terapisIds as $terapisId) {
            $dataInsert[] = [
                'terapis_id' => $terapisId,
                'tanggal'    => $tanggal,
                'status'     => 'Hadir', // Default Hadir
                'keterangan' => null
            ];
        }

        // DATABASE TRANSACTION
        $db = \Config\Database::connect();
        $db->transStart();

        // Hapus presensi existing untuk terapis yang dipilih di tanggal ini
        $this->model_absensi->whereIn('terapis_id', $terapisIds)
            ->where('tanggal', $tanggal)
            ->delete();

        // Insert presensi baru
        $this->model_absensi->insertBatch($dataInsert);

        $db->transComplete();

        if ($db->transStatus() === true) {
            $jumlahTerapis = count($terapisIds);
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => "Presensi untuk {$jumlahTerapis} terapis pada tanggal " . date('d M Y', strtotime($tanggal)) . ' berhasil disimpan!',
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menyimpan ke database. Sistem telah membatalkan perubahan.'
        ]);
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
                'redirect' => base_url('kehadiran'),
                'csrfHash' => csrf_hash()
            ]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan ke database. Sistem telah membatalkan perubahan.']);
    }
}
