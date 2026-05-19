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

    /**
     * Check if current user is authorized to access admin gaji features
     */
    private function checkAdminAccess()
    {
        $role = session()->get('role');
        if ($role === 'user' && !empty(session()->get('terapis_id'))) {
            // Redirect terapis to beranda
            return redirect()->to(base_url('beranda'))->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        return null;
    }

    public function index()
    {
        // Check authorization
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
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
        // Check authorization
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
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
        // Check authorization
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
        $detail = $this->Mriwayatgaji->getDetailPerhitungan($terapisId);
        if (!empty($detail['terapis'])) {
            return $this->response->setJSON([
                'status'    => 'success',
                'data'      => $detail['terapis'],
                'kalkulasi' => $detail['komponen'],
                'csrfHash'  => csrf_hash()
            ]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }


    public function prosesBayar()
    {
        // Check authorization
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
        $terapisId = (int)$this->request->getPost('terapis_id');

        if (empty($terapisId) || $terapisId <= 0) {
            return redirect()->to('/gaji')->with('error', 'ID terapis tidak valid.');
        }

        // Hitung ulang dari model
        $dataDetail = $this->Mriwayatgaji->getDetailPerhitungan($terapisId);
        if (empty($dataDetail['terapis'])) {
            return redirect()->to('/gaji')->with('error', 'Data terapis tidak ditemukan.');
        }

        $k = $dataDetail['komponen'];

        $dataGaji = [
            'terapis_id'       => $terapisId,
            'periode_bulan'    => date('n'),
            'periode_tahun'    => date('Y'),
            'total_kehadiran'  => $k['kehadiran'],
            'gaji_pokok_total' => $k['gaji_pokok'],
            'total_tunjangan'  => $k['total_A'] - $k['gaji_pokok'] + $k['total_B'],
            'total_potongan'   => $k['total_C'],
            'gaji_bersih'      => $k['gaji_bersih'],
            'tanggal_bayar'    => date('Y-m-d H:i:s'),
            'status'           => 'lunas'
        ];

        $this->db->transStart();
        $riwayatId = $this->Mriwayatgaji->insert($dataGaji, true);

        // Simpan detail komponen (snapshot)
        $detailBatch = [];
        $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'take_home', 'nama_komponen' => 'Gaji Pokok', 'nominal' => $k['gaji_pokok']];
        if ($k['jaspel_reguler'] > 0)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'take_home', 'nama_komponen' => 'Jasa Pelayanan Reguler', 'nominal' => $k['jaspel_reguler']];
        if ($k['jaspel_kejantanan'] > 0)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'take_home', 'nama_komponen' => 'Jasa Terapi Kejantanan', 'nominal' => $k['jaspel_kejantanan']];
        foreach ($k['benefit_list'] as $b)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'benefit', 'nama_komponen' => $b['nama'], 'nominal' => $b['nominal']];
        foreach ($k['potongan_list'] as $p)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'potongan', 'nama_komponen' => $p['nama'], 'nominal' => $p['nominal']];
        if ($k['total_kasbon'] > 0)
            $detailBatch[] = ['riwayat_gaji_id' => $riwayatId, 'kelompok' => 'potongan', 'nama_komponen' => 'Cicilan Kasbon', 'nominal' => $k['total_kasbon']];

        if (!empty($detailBatch))
            $this->db->table('riwayat_gaji_detail')->insertBatch($detailBatch);

        $this->db->table('kasbon_karyawan')
            ->where('terapis_id', $terapisId)
            ->where('status_potongan', 'belum_lunas')
            ->update(['status_potongan' => 'lunas']);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return redirect()->to('/gaji')->with('error', 'Gagal memproses gaji karyawan.');
        }

        return redirect()->to('/gaji')->with('success', 'Gaji karyawan berhasil diproses dan dibayarkan.');
    }
    public function fetchEstimasi()
    {
        // Check authorization
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
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
        // Check authorization
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;
        
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

        // Fallback cerdas untuk Admin: Cocokkan terapis berdasarkan nama (realname) atau username jika belum ada link terapis_id
        if (!$terapis_id && session()->get('role') === 'admin') {
            $realname = session()->get('realname');
            $username = session()->get('username');
            
            $terapis = $this->db->table('terapis')->select('id')
                ->groupStart()
                    ->where('nama', $realname)
                    ->orWhere('nama', $username)
                ->groupEnd()
                ->get()->getRow();
                
            if (!$terapis) {
                $builder = $this->db->table('terapis')->select('id');
                $hasCond = false;
                if (!empty($realname) && strlen($realname) >= 3) {
                    $builder->like('nama', $realname);
                    $hasCond = true;
                }
                if (!empty($username) && strlen($username) >= 3) {
                    if ($hasCond) {
                        $builder->orLike('nama', $username);
                    } else {
                        $builder->like('nama', $username);
                        $hasCond = true;
                    }
                }
                if ($hasCond) {
                    $terapis = $builder->get()->getRow();
                }
            }
            
            if ($terapis) {
                $terapis_id = $terapis->id;
                session()->set('terapis_id_int', $terapis_id);
            }
        }

        if (!$terapis_id) {
            $data = [
                'title'       => 'Gaji Saya',
                'is_unlinked' => true,
                'role'        => session()->get('role'),
                'realname'    => session()->get('realname'),
            ];
            return view('App\modules\gaji\Views\monitor', $data);
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
