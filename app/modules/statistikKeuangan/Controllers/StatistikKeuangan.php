<?php

namespace App\Modules\StatistikKeuangan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\transaksi\Models\MTransaksi;
use App\Modules\region\Models\MRegion;

class StatistikKeuangan extends BaseController
{

    protected $mTransaksi;
    protected $mRegion;

    public function __construct()
    {
        $this->mTransaksi = new MTransaksi();
        $this->mRegion    = new MRegion();
    }

    public function index()
    {
        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $list_regions = $this->mRegion->getData(null, $allowed_regions);

        $data = [
            'title'        => 'Analisis Keuangan',
            'role'         => session()->get('role'),
            'list_regions' => $list_regions
        ];
        return view('App\Modules\StatistikKeuangan\Views\index', $data);
    }

    public function get_chart_data()
    {
        $days = (int) ($this->request->getGet('days') ?? 7);
        $region_param = $this->request->getGet('region');
        $region_patient = session()->get('region_patient');

        if ($region_param && $region_param !== 'all') {
            $filter_region = $region_param;
        } else {
            $filter_region = ($region_patient !== 'all') ? $region_patient : null;
        }

        $trend = $this->mTransaksi->getFinanceTrend($days, $filter_region);
        $structure = $this->mTransaksi->getExpenseStructure($days, $filter_region);
        return $this->response->setJSON([
            'status' => 'success',
            'trend' => $trend,
            'structure' => $structure
        ]);
    }

    public function export_excel()
    {
        $days = (int) ($this->request->getGet('days') ?? 7);
        $region_param = $this->request->getGet('region');
        $region_patient = session()->get('region_patient');

        if ($region_param && $region_param !== 'all') {
            $filter_region = $region_param;
        } else {
            $filter_region = ($region_patient !== 'all') ? $region_patient : null;
        }

        $trend = $this->mTransaksi->getFinanceTrend($days, $filter_region);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analisis Keuangan');

        $headers = ['Tanggal', 'Pemasukan', 'Pengeluaran', 'Selisih'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($trend as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['tanggal']);
            $sheet->setCellValue('B' . $rowNum, $row['pemasukan']);
            $sheet->setCellValue('C' . $rowNum, $row['pengeluaran']);
            $sheet->setCellValue('D' . $rowNum, $row['pemasukan'] - $row['pengeluaran']);
            $rowNum++;
        }

        $filename = 'Analisis_Keuangan_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function export_pdf()
    {
        $days = (int) ($this->request->getGet('days') ?? 7);
        $region_param = $this->request->getGet('region');
        $region_patient = session()->get('region_patient');

        if ($region_param && $region_param !== 'all') {
            $filter_region = $region_param;
        } else {
            $filter_region = ($region_patient !== 'all') ? $region_patient : null;
        }

        $trend = $this->mTransaksi->getFinanceTrend($days, $filter_region);

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Laporan Analisis Keuangan');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Laporan Analisis Keuangan', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, 'Periode: ' . $days . ' Hari Terakhir', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 8, 'Tanggal', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Pemasukan', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Pengeluaran', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Selisih', 1, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        foreach ($trend as $row) {
            $pdf->Cell(40, 7, $row['tanggal'], 1, 0, 'C');
            $pdf->Cell(50, 7, 'Rp ' . number_format($row['pemasukan'], 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell(50, 7, 'Rp ' . number_format($row['pengeluaran'], 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell(50, 7, 'Rp ' . number_format($row['pemasukan'] - $row['pengeluaran'], 0, ',', '.'), 1, 1, 'R');
        }

        $this->response->setHeader('Content-Type', 'application/pdf');
        $pdf->Output('Analisis_Keuangan_' . date('Ymd') . '.pdf', 'I');
        exit();
    }
}
