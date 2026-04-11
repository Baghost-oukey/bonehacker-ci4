<?php

namespace App\modules\antrean\Controllers;

use App\Controllers\BaseController;
use App\modules\countries\Models\MCountries;
use App\modules\patients\Models\MPatients;
use App\modules\region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;
use Hermawan\DataTables\DataTable;
use CodeIgniter\I18n\Time;

class Antrean extends BaseController
{

    protected $patientsModel;
    protected $regionModel;
    protected $countryModel;
    protected $db;

    public function __construct()
    {
        $this->patientsModel = new MPatients();
        $this->regionModel = new MRegion();
        $this->countryModel = new MCountries();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        //
        $region_patients = json_decode(session()->get('regions_patient'), true);

        $data = [
            'title'           => 'Antrean',
            'realname'        => session()->get('realname'),
            'role'            => session()->get('role'),
            'regions_patient' => $region_patients,
            'wilayah'         => $this->regionModel->findAll(),
            'negara'          => $this->countryModel->findAll(),
            'resources'       => $this->patientsModel->get_resources(),
        ];

        return view('App\modules\antrean\Views\views_antrean', $data);
    }

    public function fetchDataTable()
    {
        $request = service('request');
        $region    = $request->getPost('region');
        $startDate = $request->getPost('start_date');
        $endDate   = $request->getPost('end_date');

        $builder = $this->db->table('patient_queues pq')
            ->select('pq.id as queue_id, pq.queue_date, p.id as patient_id, p.name as patient_name, p.age as patient_age, p.phone, p.address, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, h.id as history_id, h.process_at, h.finish_at')
            ->select('(SELECT COUNT(h2.id) FROM histories h2 WHERE h2.patient_id = p.id AND h2.is_delete = 0) AS visit_count')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->where('h.finish_at', null);

        $regions_session = json_decode(session()->get('regions_patient'), true);
        if (session()->get('role') === 'user' && !empty($regions_session)) {
            $builder->whereIn('pq.region_id', is_array($regions_session) ? $regions_session : [$regions_session]);
        }

        if (!empty($region)) $builder->where('p.region_id', $region);
        if (!empty($startDate) && !empty($endDate)) {
            $formatStart = date('Y-m-d', strtotime($startDate));
            $formatEnd   = date('Y-m-d', strtotime($endDate));

            $builder->where('DATE(pq.queue_date) >=', $formatStart)
                ->where('DATE(pq.queue_date) <=', $formatEnd);
        }


        return \Hermawan\DataTables\DataTable::of($builder)
            ->filter(function ($builder) use ($request) {
                $search = $request->getPost('search');
                $searchValue = $search['value'] ?? null;
                if ($searchValue) {
                    $builder->groupStart()
                        ->like('p.name', $searchValue)
                        ->orLike('p.phone', $searchValue)
                        ->orLike('pa.kabupaten_nama', $searchValue)
                        ->orLike('pq.queue_date', $searchValue)
                        ->groupEnd();
                }
            }, true)

            ->add('date', function ($row) {
                return !empty($row->queue_date) ? date('d-m-Y', strtotime($row->queue_date)) : '-';
            })
            ->add('name', function ($row) {
                return $row->patient_name;
            })
            ->add('address', function ($row) {
                return implode(', ', array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]));
            })
            ->add('age', function ($row) {
                return $row->patient_age;
            })
            ->add('description', function ($row) {
                return $row->visit_count > 0 ? 'Pasien Lama' : 'Pasien Baru';
            })
            ->add('action', function ($row) {
                $btn = '<div class="btn-group d-flex justify-content-between align-items-center" style="gap: 5px;">';
                $role = session()->get('role');

                $historyId = $row->history_id ?? '';

                if ($role === 'superadmin') {
                    if ($row->process_at !== null) {
                        $btn .= '<a href="' . site_url('antrean/finishQueue/' . $row->queue_id) . '" class="btn btn-warning btn-md w-100">Selesai</a>';
                    } else {
                        $btn .= '<a href="' . site_url('antrean/procesToQueue/' . $row->queue_id) . '" class="btn btn-success btn-md w-100"> Proses Pasien </a>';
                    }
                } else {
                    if ($row->process_at !== null) {
                        return '<span class="badge badge-primary">Pasien Dalam Terapi</span>';
                    }
                    return '<span class="badge badge-warning">Menunggu Konfirmasi</span>';
                }

                $urlHistory = site_url('patient/show/' . $row->patient_id) . '?openModalRiwayat=true';
                if (!empty($historyId)) {
                    $urlHistory .= '&history_id=' . $historyId;
                }
                $urlHistory .= '&queue_id=' . $row->queue_id;

                $btn .= '<a href="' . $urlHistory . '" class="btn btn-info btn-md"><i class="fas fa-file-medical"></i> Rekam Medis</a>';
                $btn .= '</div>';
                return $btn;
            })
            ->toJson(true);
    }

    public function fetchPatientDataTables()
    {
        $request = service('request');
        $region = $request->getPost('region');

        $builder = $this->db->table('patients p')
            ->select('p.id AS patient_id, p.name, p.phone, p.address, p.age, r.name as name_region, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama')
            ->select('COALESCE((SELECT MAX(date) FROM histories h WHERE h.patient_id = p.id AND h.is_delete = 0), "-") AS last_visit_date')
            ->select('COALESCE((SELECT COUNT(h2.id) FROM histories h2 WHERE h2.patient_id = p.id AND h2.is_delete = 0), 0) AS visit_count')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left');

        if (!empty($region)) {
            $builder->where('p.region_id', $region);
        }

        return \Hermawan\DataTables\DataTable::of($builder)
            ->add('name', function ($row) {
                return $row->name . ' (' . $row->phone . ')';
            })
            ->add('address', function ($row) {
                $addressFilter = array_filter([
                    $row->address,
                    $row->desa_nama,
                    $row->kecamatan_nama,
                    $row->kabupaten_nama,
                    $row->provinsi_nama
                ]);
                return implode(', ', $addressFilter);
            })
            ->add('description', function ($row) {
                if ($row->visit_count > 0) {
                    return '<span class="badge badge-danger">Pasien Lama</span>';
                } else {
                    return '<span class="badge badge-primary">Pasien Baru</span>';
                }
            })
            ->add('action', function ($row) {
                return '<a href="' . site_url('antrean/addToQueue/' . $row->patient_id) . '" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add to Queue</a>';
            })
            ->toJson(true);
    }

    public function addToQueue($patientId)
    {
        $patient = $this->db->table('patients')->where('id', $patientId)->get()->getRow();

        if (!$patient || empty($patient->region_id)) {
            return redirect()->back()->with('message', ['error', 'Data pasien atau wilayah tidak valid.']);
        }

        $this->db->table('patient_queues')->insert([
            'region_id'  => $patient->region_id,
            'patient_id' => $patientId,
            'queue_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('antrean')->with('message', ['success', 'Pasien berhasil ditambahkan ke antrean.']);
    }

    public function procesToQueue($queueId)
    {
        $queue = $this->db->table('patient_queues')->where('id', $queueId)->get()->getRow();
        if (!$queue) return redirect()->back()->with('message', ['error', 'Antrean tidak ditemukan.']);

        $this->db->table('histories')->insert([
            'patient_queue_id' => $queueId,
            'patient_id'       => $queue->patient_id,
            'type'             => 'draft',
            'process_at'       => date('Y-m-d H:i:s'),
            'is_delete'        => 0
        ]);

        return redirect()->to('antrean')->with('message', ['success', 'Terapi dimulai.']);
    }

    public function finishQueue($queueId)
    {
        $this->db->table('histories')
            ->where('patient_queue_id', $queueId)
            ->update(['finish_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('antrean')->with('message', ['success', 'Pasien telah selesai menjalani Terapi.']);
    }

    public function daftarAntrean()
    {
        $db = \Config\Database::connect();
        $regionId  = $this->request->getGet('region');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $builder = $db->table('patient_queues pq')
            ->select('pq.*, h.process_at, h.finish_at, p.id as patient_id, p.name as patient_name, p.address, p.phone as patient_phone, p.age as patient_age, r.name as name_region, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama')
            ->select('(SELECT MAX(date) FROM histories h WHERE h.patient_id = p.id AND h.is_delete = 0) AS last_visit_date')
            ->select('(SELECT COUNT(h.id) FROM histories h WHERE h.patient_id = p.id AND h.is_delete = 0) AS visit_count')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left');

        if (!empty($regionId)) {
            $builder->where('p.region_id', $regionId);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(pq.queue_date) >=', $startDate)
                ->where('DATE(pq.queue_date) <=', $endDate);
        }

        $data['patient_queues'] = $builder->orderBy('h.finish_at', 'ASC')
            ->orderBy('pq.created_at', 'ASC')
            ->get()->getResult();

        $processedBuilder = $db->table('patient_queues pq')
            ->join('histories h', 'h.patient_queue_id = pq.id AND h.is_delete = 0', 'left')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->where('h.process_at IS NOT NULL')
            ->where('h.finish_at IS NULL')
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate);

        if (!empty($regionId)) $processedBuilder->where('p.region_id', $regionId);
        $data['processed_queues'] = $processedBuilder->countAllResults();

        $finishedBuilder = $db->table('patient_queues pq')
            ->join('histories h', 'h.patient_queue_id = pq.id AND h.is_delete = 0', 'left')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->where('h.finish_at IS NOT NULL')
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate);

        if (!empty($regionId)) $finishedBuilder->where('p.region_id', $regionId);
        $data['finished_queues'] = $finishedBuilder->countAllResults();
        $data['waiting_queues'] = count($data['patient_queues']) - ($data['processed_queues'] + $data['finished_queues']);

        if (!empty($regionId)) {
            $reg = $db->table('regions')->where('id', $regionId)->get()->getRow();
            $data['regionName'] = $reg ? $reg->name : 'Semua Wilayah';
        } else {
            $data['regionName'] = 'Semua Wilayah';
        }

        $time = Time::parse($startDate, 'Asia/Jakarta', 'id_ID');
        $data['currentDate'] = $time->toLocalizedString('EEEE, dd/MM/yyyy');

        return view('App\modules\antrean\Views\views_daftar_antrean', $data);
    }

    public function export_excell_antrean()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $regionId  = $this->request->getGet('region');


        $builder = $this->db->table('patient_queues pq')
            ->select('pq.queue_date, 
        p.name as patient_name, 
        p.phone, 
        p.address, 
        pa.desa_nama, 
        pa.kecamatan_nama,  
        pa.kabupaten_nama,
        h.process_at, 
        h.finish_at,
        t.nama as therapist_name,
        (CASE 
            WHEN h.finish_at IS NOT NULL THEN "Selesai"
            WHEN h.process_at IS NOT NULL THEN "Diproses"
            ELSE "Menunggu"
        END) as status_label')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->join('terapis t', 't.terapis_id = h.terapis_id', 'left')
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate);
        if (!empty($regionId)) {
            $builder->where('pq.region_id', $regionId);
        }

        $query = $builder->get();

        //    Buat Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['No', 'Tgl Antrean', 'Nama Pasien', 'No WA', 'Alamat Lengkap', 'Status', 'Terapis', 'Mulai', 'Selesai', 'Durasi'];
        $sheet->fromArray($headers, NULL, 'A1');
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E7D32'],
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

        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension('1')->setRowHeight(25);

        $rowNum = 2;
        $no = 1;

        // Column $ Isi
        while ($row = $query->getUnbufferedRow()) {
            $fullAddress = implode(', ', array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]));

            $durasi = '-';
            if ($row->process_at && $row->finish_at) {
                $start = new \DateTime($row->process_at);
                $end = new \DateTime($row->finish_at);
                $interval = $start->diff($end);
                $durasi = $interval->format('%i Menit');
            }
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, date('d-m-Y', strtotime($row->queue_date)));
            // $sheet->setCellValue('C' . $rowNum, $row->patient_id);
            $sheet->setCellValue('C' . $rowNum, $row->patient_name);
            $sheet->setCellValue('D' . $rowNum, $row->phone);
            $sheet->setCellValue('E' . $rowNum, $fullAddress);
            $sheet->setCellValue('F' . $rowNum, $row->status_label);
            $sheet->setCellValue('G' . $rowNum, $row->therapist_name ?: '-');
            $sheet->setCellValue('H' . $rowNum, $row->process_at ? date('H:i', strtotime($row->process_at)) : '-');
            $sheet->setCellValue('I' . $rowNum, $row->finish_at ? date('H:i', strtotime($row->finish_at)) : '-');
            $sheet->setCellValue('J' . $rowNum, $durasi);
            $rowNum++;
        }

        $filename = 'Antrean_' . $startDate . '_to_' . $endDate . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Output 
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function print_pdf_antrean()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');
        $region = $this->request->getGet('region');

        $builder = $this->db->table('patient_queues pq')
            ->select('pq.queue_date, p.name as patient_name, p.phone, p.address, 
            pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama,
            h.process_at, h.finish_at, t.nama as therapist_name,
            (CASE 
                WHEN h.finish_at IS NOT NULL THEN "Selesai"
                WHEN h.process_at IS NOT NULL THEN "Diproses"
                ELSE "Menunggu"
            END) as status_label')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->join('terapis t', 't.terapis_id = h.terapis_id', 'left')
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate);

        if (!empty($region)) $builder->where('pq.region_id', $region);
        $query = $builder->get();


        // Buat PDF
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Laporan Antrean Pasien');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'LAPORAN ANTREAN PASIEN', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFillColor(46, 125, 50);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);

        // Column 
        $pdf->Cell(8, 8, 'No', 1, 0, 'C', 1);
        $pdf->Cell(22, 8, 'Tgl Antrean', 1, 0, 'C', 1);
        $pdf->Cell(35, 8, 'Nama Pasien', 1, 0, 'C', 1);
        $pdf->Cell(25, 8, 'No WA', 1, 0, 'C', 1);
        $pdf->Cell(55, 8, 'Alamat Lengkap', 1, 0, 'C', 1);
        $pdf->Cell(20, 8, 'Status', 1, 0, 'C', 1);
        $pdf->Cell(35, 8, 'Terapis', 1, 0, 'C', 1);
        $pdf->Cell(17, 8, 'Mulai', 1, 0, 'C', 1);
        $pdf->Cell(17, 8, 'Selesai', 1, 0, 'C', 1);
        $pdf->Cell(43, 8, 'Durasi', 1, 1, 'C', 1);

        // Isi Table
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        $no = 1;

        while ($row = $query->getUnbufferedRow()) {

            $durasi = '-';
            if ($row->process_at && $row->finish_at) {
                $start = new \DateTime($row->process_at);
                $end = new \DateTime($row->finish_at);
                $durasi = $start->diff($end)->format('%i Menit');
            }
            $alamat = implode(', ', array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]));
            $jamMulai = $row->process_at ? date('H:i', strtotime($row->process_at)) : '-';
            $jamSelesai = $row->finish_at ? date('H:i', strtotime($row->finish_at)) : '-';

            $startY = $pdf->GetY();

            $pdf->Cell(8, 7, $no++, 1, 0, 'C');
            $pdf->Cell(22, 7, date('d-m-Y', strtotime($row->queue_date)), 1, 0, 'C');
            $pdf->Cell(35, 7, $row->patient_name, 1, 0, 'L');
            $pdf->Cell(25, 7, $row->phone, 1, 0, 'L');

            // MultiCell untuk Alamat (agar bisa wrap text)
            $pdf->MultiCell(55, 7, $alamat, 1, 'L', 0, 0);
            $pdf->SetXY($pdf->GetX(), $startY);
            $pdf->SetX(10 + 8 + 22 + 35 + 25 + 55);

            $pdf->Cell(20, 7, $row->status_label, 1, 0, 'C');
            $pdf->Cell(35, 7, $row->therapist_name ?: '-', 1, 0, 'L');
            $pdf->Cell(17, 7, $jamMulai, 1, 0, 'C');
            $pdf->Cell(17, 7, $jamSelesai, 1, 0, 'C');
            $pdf->Cell(43, 7, $durasi, 1, 1, 'C');
        }

        // Output
        $this->response->setHeader('Content-Type', 'application/pdf');
        $pdf->Output('Laporan_Antrean_' . date('Ymd') . '.pdf', 'I');
        exit();
    }
}
