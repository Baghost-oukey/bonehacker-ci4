<?php

namespace App\Modules\Journal\Controllers;

use App\Controllers\BaseController;
use App\Modules\Journal\Models\MJournal;
use App\Modules\Patients\Models\MPatients;
use App\modules\region\Models\MRegion;
use Ngekoding\CodeIgniterDataTables\DataTables;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JournalController extends BaseController
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
        $session = session();
        $region_patient = $session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        return view('App\Modules\Journal\Views\index', [
            'title' => 'Journal Patients',
            'role' => $session->get('role'),
            'wilayah' => $this->model_regions->getData(null, $allowed_regions),
            'current_segment' => 'journal',
            'realname' => $session->get('realname'),
        ]);
    }

    public function fetch()
    {
        $request = $this->request;
        $session = session();

        // PARAMETER (DISERAGAMKAN)
        $cabang = $request->getPost('region');
        $start_date = $request->getPost('start_date');
        $end_date = $request->getPost('end_date');

        // SEARCH 
        $searchData = $request->getPost('search');
        $keyword = $searchData['value'] ? $searchData['value'] : '';

        //  FALLBACK SESSION
        // if (!$cabang) {
        //     $cabang = $session->get('active_cabang') ?? null;
        // }

        // Use region_patient session as the single source of truth.
        // If a specific region is posted, use it; otherwise fall back to session.
        $region_session = $session->get('region_patient');
        if (empty($cabang) || $cabang === 'all') {
            $cabang = $region_session;
        }

        //  QUERY
        $queryBuilder = $this->model_journal->get_query_for_Journal(
            $cabang,
            $start_date,
            $end_date
        );

        // FILTER KE BUILDER 
        if (!empty($keyword)) {
            $queryBuilder->groupStart()
                ->like('p.name', $keyword)
                ->orLike('p.phone', $keyword)
                ->groupEnd();
        }
        $datatables = new DataTables($queryBuilder);
        $start = (int) ($request->getPost('start') ?? 0);
        $datatables->addColumn('no', function () use (&$start) {
            return ++$start;
        });

        $datatables->addColumn('tanggal', function ($row) {
            return !empty($row->tanggal)
                ? date('d-m-Y', strtotime($row->tanggal))
                : '-';
        });

        $datatables->addColumn('status', function ($row) {
            return $row->status ?? '-';
        });

        $datatables->addColumn('nama', function ($row) {
            $name = esc($row->nama);
            $url = site_url('patient/show/' . $row->patient_id);

            $phone = !empty($row->nowa)
                ? '<br><small>(' . esc($row->nowa) . ')</small>'
                : '';

            return '<a href="' . $url . '" target="_blank"><strong>' . $name . '</strong></a>' . $phone;
        });

        $datatables->addColumn('alamat', function ($row) {
            return $row->alamat ?? '-';
        });

        $datatables->addColumn('result_names', function ($row) {
            return $row->result_names ?? '-';
        });

        $datatables->addColumn('measures', function ($row) {
            return $row->measures ?? '-';
        });

        $datatables->addColumn('action', function ($row) {
            return '<a href="' . site_url('patient/show/' . $row->patient_id) . '" 
                        target="_blank"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>';
        });

        //  RESPONSE AMAN
        $response = $datatables->asObject()->generate();

        if (is_object($response)) {
            $data = json_decode($response->getBody(), true);
        } else {
            $data = ['data' => []];
        }

        //  UPDATE CSRF
        $data['new_token'] = csrf_hash();

        return $this->response->setJSON($data);
    }

    // ================= EXPORT PDF =================
    public function export_pdf()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $cabang = $this->request->getGet('region') ?? $this->request->getGet('cabang_id');
        $region_session = session()->get('region_patient');
        if (empty($cabang) || $cabang === 'all') {
            $cabang = $region_session;
        }

        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        $journals = $this->model_journal->get_query_for_Journal(
            $cabang,
            $start_date,
            $end_date,
            true
        );

        $html = view('App\Modules\Journal\Views\pdf_template', [
            'journals' => $journals
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($dompdf->output());
    }

    // ================= EXPORT EXCEL =================
    public function export_excell()
    {
        $cabang = $this->request->getGet('region') ?? $this->request->getGet('cabang_id');
        $region_session = session()->get('region_patient');
        if (empty($cabang) || $cabang === 'all') {
            $cabang = $region_session;
        }

        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        $data = $this->model_journal->get_query_for_Journal(
            $cabang,
            $start_date,
            $end_date,
            true
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Tanggal', 'Nama', 'Status', 'Alamat', 'Hasil Pemeriksaan', 'Tindakan', 'No. WA'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($data as $i => $item) {
            $sheet->fromArray([
                $i + 1,
                $item->tanggal,
                $item->nama,
                $item->status,
                $item->alamat,
                $item->result_names,
                $item->measures,
                $item->nowa
            ], NULL, 'A' . $rowNum);

            $rowNum++;
        }

        $filename = 'Journal_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ================= ROUTER EXPORT =================
    public function export_file_journal()
    {
        if (session()->get('role') !== 'superadmin') {
            return redirect()->to('journal')->with('error', 'Unauthorized access');
        }

        $format = $this->request->getGet('format_type');

        return ($format === 'pdf')
            ? $this->export_pdf()
            : $this->export_excell();
    }
}
