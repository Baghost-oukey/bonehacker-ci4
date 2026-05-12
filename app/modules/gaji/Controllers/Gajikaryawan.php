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
        $potongAbsen = $this->request->getPost('potong_absen') ? 1 : 0;
        
        $builder = $this->db->table('gaji_karyawan');
        $cekData = $builder->where('terapis_id', $terapisId)->get()->getRowArray();
        $dataSimpan = [
            'terapis_id'   => $terapisId,
            'tipe_gaji'    => $tipeGaji,
            'potong_absen' => $potongAbsen,
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

        // Potongan Absen untuk Gaji Bulanan
        $potonganAbsen = 0;
        if ($tipeGaji === 'bulanan' && isset($gajiData['potong_absen']) && $gajiData['potong_absen'] == 1) {
            $mKalender = new \App\modules\kalender\Models\MKalender();
            // Estimasi hari kerja berdasarkan bulan ini
            $hariKerja = $mKalender->getHariKerjaBulanan(date('n'), date('Y'), $terapis['region_id'] ?? null);
            if ($hariKerja > 0) {
                $absenHari = $hariKerja - $totalKehadiran;
                if ($absenHari > 0) {
                    $potonganPerHari = $nominalGaji / $hariKerja;
                    $potonganAbsen = $potonganPerHari * $absenHari;
                    $gajiPokokTotal = $nominalGaji - $potonganAbsen;
                }
            }
        }

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
    public function export()
    {
        $role = session()->get('role');
        if (!in_array($role, ['superadmin', 'owner', 'admin'])) {
            return redirect()->to('/gaji')->with('error', 'Unauthorized access');
        }

        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $region_patient = session()->get('region_patient');
        $sessionRegionId = ($region_patient !== 'all' && !empty($region_patient))
            ? (is_array($region_patient) ? $region_patient[0] : $region_patient)
            : 'all';
        $regionId = $this->request->getGet('region_id') ?? $sessionRegionId;

        $data = $this->Mriwayatgaji->getPayrollHistory($bulan, $tahun, $regionId);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Penggajian');

        // --- 1. JUDUL LAPORAN ---
        $nama_bulan = date('F', mktime(0, 0, 0, $bulan, 1));
        $sheet->setCellValue('A1', 'LAPORAN RIWAYAT PENGGAJIAN KARYAWAN');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', "Periode: $nama_bulan $tahun | Dicetak: " . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // --- 2. HEADER TABEL ---
        $headers = ['No', 'Tanggal Bayar', 'Nama Karyawan', 'Wilayah', 'Periode', 'Gaji Pokok', 'Tunjangan', 'Potongan', 'Gaji Bersih'];
        $sheet->fromArray($headers, NULL, 'A4');

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
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
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // --- 3. ISI DATA ---
        $rowNum = 5;
        foreach ($data as $i => $row) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, date('d/m/Y', strtotime($row['tanggal_bayar'])));
            $sheet->setCellValue('C' . $rowNum, $row['nama']);
            $sheet->setCellValue('D' . $rowNum, $row['wilayah']);
            $sheet->setCellValue('E' . $rowNum, date('F', mktime(0, 0, 0, $row['periode_bulan'], 1)) . ' ' . $row['periode_tahun']);
            $sheet->setCellValue('F' . $rowNum, $row['gaji_pokok_total']);
            $sheet->setCellValue('G' . $rowNum, $row['total_tunjangan']);
            $sheet->setCellValue('H' . $rowNum, $row['total_potongan']);
            $sheet->setCellValue('I' . $rowNum, $row['gaji_bersih']);

            // Styling baris data
            $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Format Currency (Rupiah)
            $sheet->getStyle('F' . $rowNum . ':I' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            
            $rowNum++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Gaji_' . $nama_bulan . '_' . $tahun . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
    public function monitor()
    {
        // Gunakan Integer ID untuk sinkronisasi database
        $terapis_id = session()->get('terapis_id_int');
        
        // Fallback: Jika session baru belum ada (user belum relogin), cari manual berdasarkan string ID
        if (!$terapis_id && session()->get('terapis_id')) {
            $terapisStringId = session()->get('terapis_id');
            $terapis = $this->db->table('terapis')->select('id')->where('terapis_id', $terapisStringId)->get()->getRow();
            if ($terapis) {
                $terapis_id = $terapis->id;
                session()->set('terapis_id_int', $terapis_id); // Simpan ke session untuk request berikutnya
            }
        }

        if (!$terapis_id) {
            return redirect()->to(base_url('beranda'))->with('error', 'Akun Anda tidak terhubung dengan data Terapis.');
        }

        $detail = $this->Mriwayatgaji->getDetailPerhitungan($terapis_id);
        $history = $this->Mriwayatgaji->getHistoryByTerapis($terapis_id);

        $data = [
            'title'     => 'Gaji Saya',
            'estimasi'  => $detail['terapis'],
            'history'   => $history,
            'realname'  => session()->get('realname'),
        ];

        return view('App\modules\gaji\Views\monitor', $data);
    }
}
