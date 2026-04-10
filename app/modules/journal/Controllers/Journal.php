<?php

namespace App\modules\journal\Controllers;

use App\Controllers\BaseController;
use App\modules\journal\Models\MJournal;
use App\modules\patients\Models\MPatients;
use App\modules\region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;
use Hermawan\DataTables\DataTable;
use Ngekoding\CodeIgniterDataTables\DataTables;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Journal extends BaseController
{
    protected $model_journal;
    protected $model_regions;
    protected $model_patients;

    public function __construct()
    {
        $this->model_journal = new MJournal();
        $this->model_regions = new MRegion();
        $this->model_patients = new MPatients();
    }
    public function index()
    {
        //
        $data = [
            'title' => 'Journal Patients',
            'role'  => session()->get('role'),
            'wilayah' =>  $this->model_regions->findAll(),
        ];

        return view('App\modules\journal\Views\views_journal', $data);
    }

    public function fetch()
    {
        $region = $this->request->getPost('region');
        $start_date = $this->request->getPost('start_date');
        $end_date   = $this->request->getPost('end_date');

        $queryBuilder = $this->model_journal->get_query_for_Journal($region, $start_date, $end_date);
        $datatables = new DataTables($queryBuilder);



        $start = (int)($this->request->getPost('start') ?? 0);

        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });
        $datatables->addColumn('tanggal', function ($row) {
            return !empty($row->tanggal) ? date('d-m-Y', strtotime($row->tanggal)) : '-';
        });

        $datatables->addColumn('status', function ($row) {
            return $row->status;
        });



        $datatables->addColumn('nama', function ($row) {
            $name = esc($row->nama);
            $url  = site_url('patient/show/' . $row->patient_id);
            $phone = (!empty($row->nowa)) ? '<br><small class="text-muted">(' . esc($row->nowa) . ')</small>' : '';

            return '<a href="' . $url . '" target="_blank"><strong>' . $name . '</strong></a>' . $phone;
        });

        $datatables->addColumn('alamat', function ($row) {
            return $row->alamat;
        });

        $datatables->addColumn('result_names', function ($row) {
            return $row->result_names ?? '-';
        });

        $datatables->addColumn('measures', function ($row) {
            return $row->measures ?? '-';
        });

        $datatables->addColumn('action', function ($row) {
            return '<a href="' . site_url('patient/show/' . $row->patient_id) . '" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>';
        });

        $response = $datatables->asObject()->generate();
        if (is_object($response)) {
            $data = json_decode($response->getBody(), true);
        } else {
            $data = ['data' => []];
        }
        $data['new_token'] = csrf_hash();

        return $this->response->setJSON($data);
    }

    public function export_pdf()
    {

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $region_id  = $this->request->getGet('region_id');
        $start_date = $this->request->getGet('start_date');
        $end_date   = $this->request->getGet('end_date');

        $journals = $this->model_journal->get_query_for_Journal($region_id, $start_date, $end_date, true);

        $data = [
            'journals' => $journals,
            'status'   => []
        ];

        // foreach ($journals as $journal) {
        //     $data['status'][$journal->id] = $this->model_journal->getPatientStatus($journal->id);
        // }

        $html = view('App\Modules\Journal\Views\pdf_template', $data);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setBody($dompdf->output())
            ->noCache();
    }

    public function export_excell()
    {
        $region_id  = $this->request->getGet('region_id');
        $start_date = $this->request->getGet('start_date');
        $end_date   = $this->request->getGet('end_date');

        $data = $this->model_journal->get_query_for_Journal($region_id, $start_date, $end_date, true);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['No', 'Tanggal', 'Nama', 'Status', 'Alamat', 'Hasil Pemeriksaan', 'Tindakan', 'No. WA'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($data as $index => $item) {
            // $status = $this->model_journal->getPatientStatus($item->id);
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $item->tanggal);
            $sheet->setCellValue('C' . $rowNum, $item->nama);
            $sheet->setCellValue('D' . $rowNum, $item->status);
            $sheet->setCellValue('E' . $rowNum, $item->alamat);
            $sheet->setCellValue('F' . $rowNum, $item->result_names);
            $sheet->setCellValue('G' . $rowNum, $item->measures);
            $sheet->setCellValue('H' . $rowNum, $item->nowa);
            $rowNum++;
        }

        $lastRow = $rowNum - 1;
        $range   = 'A1:H' . $lastRow;
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => 'center']
        ]);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A2:B' . $lastRow)->getAlignment()->setHorizontal('center'); // No & Tanggal
        $sheet->getStyle('D2:D' . $lastRow)->getAlignment()->setHorizontal('center'); // Status
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(30);
        $sheet->getColumnDimension('G')->setAutoSize(false)->setWidth(30);
        $sheet->getStyle('E2:G' . $lastRow)->getAlignment()->setWrapText(true);
        $filename = 'Journal_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function export_file_journal()
    {
        $format = $this->request->getGet('format_type');
        if ($format === 'pdf') {
            return $this->export_pdf();
        } else {
            return $this->export_excell();
        }
    }
}
