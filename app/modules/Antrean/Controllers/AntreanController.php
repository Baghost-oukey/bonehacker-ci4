<?php

namespace App\Modules\Antrean\Controllers;

use App\Controllers\BaseController;
use App\modules\countries\Models\MCountries;
use App\modules\patients\Models\MPatients;
use App\modules\region\Models\MRegion;
use CodeIgniter\I18n\Time;
use Hermawan\DataTables\DataTable;

class AntreanController extends BaseController
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
        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'title' => 'Antrean',
            'realname' => session()->get('realname'),
            'role' => session()->get('role'),
            'regions_patient' => $region_patient,
            'wilayah' => $this->regionModel->getData(null, $allowed_regions),
            'negara' => $this->countryModel->findAll(),
            'resources' => $this->patientsModel->get_resources(),
        ];

        return view('App\Modules\Antrean\Views\index', $data);
    }

    public function fetchDataTable()
    {
        $request = service('request');
        $region    = $request->getPost('region');
        $startDate = $request->getPost('start_date');
        $endDate   = $request->getPost('end_date');

        $search = $request->getPost('search');
        $searchValue = (is_array($search) && isset($search['value'])) ? $search['value'] : $request->getVar('search');

        $builder = $this->db->table('patient_queues pq')
            ->select('pq.id as queue_id, pq.queue_number, pq.queue_date, p.id as patient_id, p.name as patient_name, p.age as patient_age, p.phone, p.address, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, h.id as history_id, h.process_at, h.finish_at')
            ->select('(SELECT COUNT(*) FROM histories h_vc WHERE h_vc.patient_id = p.id AND h_vc.is_delete = 0) AS visit_count')
            ->select('CASE 
                WHEN h.process_at IS NOT NULL AND h.finish_at IS NULL THEN 1 
                WHEN h.process_at IS NULL AND h.finish_at IS NULL THEN 2 
                ELSE 3 
            END as status_order', false)
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->orderBy('status_order', 'ASC')
            ->orderBy('pq.queue_number', 'ASC');

        $active_region = session()->get('active_region');
        $region_session = session()->get('region_patient');
        $filter = ($region && $region !== 'all') ? $region : ($active_region !== 'all' ? $active_region : $region_session);

        // Smart Search: Jika sedang mencari, abaikan filter wilayah agar lebih mudah menemukan pasien
        if (!empty($searchValue)) {
            $filter = 'all';
        }

        if ($filter !== 'all' && !empty($filter)) {
            if (is_array($filter)) {
                $builder->whereIn('pq.region_id', $filter);
            } else {
                $builder->where('pq.region_id', $filter);
            }
        }

        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(pq.queue_date) >=', date('Y-m-d', strtotime($startDate)))
                ->where('DATE(pq.queue_date) <=', date('Y-m-d', strtotime($endDate)));
        }

        return DataTable::of($builder)
            ->setSearchableColumns(['p.name', 'p.phone', 'pa.kabupaten_nama', 'p.id'])
            ->add('date', function ($row) {
                return !empty($row->queue_date) ? date('d-m-Y', strtotime($row->queue_date)) : '-';
            })
            ->add('name', function ($row) {
                $statusPasien = $row->visit_count > 0
                    ? '<span class="ml-2 inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Lama</span>'
                    : '<span class="ml-2 inline-flex items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Baru</span>';
                
                $urlProfile = site_url('patient/show/' . $row->patient_id);
                return '<div class="flex items-center"><a href="' . $urlProfile . '" target="_blank" rel="noopener noreferrer" class="text-teal-600 hover:text-teal-700 hover:underline transition-colors">' . esc($row->patient_name) . '</a>' . $statusPasien . '</div>';
            })
            ->add('address', function ($row) {
                return implode(', ', array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]));
            })
            ->add('age', function ($row) {
                return $row->patient_age;
            })
            ->add('description', function ($row) {
                if ($row->finish_at !== null) {
                    return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">Selesai</span>';
                } else if ($row->process_at !== null) {
                    return '<span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10"><span class="mr-1 h-1.5 w-1.5 rounded-full bg-amber-600 animate-pulse"></span> Diproses</span>';
                } else {
                    return '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">Menunggu</span>';
                }
            })
            ->add('action', function ($row) {
                $btn = '<div class="flex items-center justify-center gap-2">';
                $historyId = $row->history_id ?? '';

                if ($row->finish_at !== null) {
                    $btn .= '<span class="inline-flex h-8 items-center justify-center rounded-md bg-slate-50 px-3 text-xs font-semibold text-slate-400 border border-slate-200 cursor-not-allowed">Terarsip</span>';
                } else if ($row->process_at !== null) {
                    $btn .= '<a href="' . site_url('antrean/finishQueue/' . $row->queue_id) . '" class="inline-flex h-8 items-center justify-center rounded-md bg-amber-500 px-3 text-xs font-medium text-white shadow transition hover:bg-amber-600">Selesai</a>';
                } else {
                    $btn .= '<a href="' . site_url('antrean/procesToQueue/' . $row->queue_id) . '" class="inline-flex h-8 items-center justify-center rounded-md bg-emerald-600 px-3 text-xs font-medium text-white shadow transition hover:bg-emerald-700">Proses</a>';
                }

                // Tombol Medis - langsung buka modal rekam medis
                $btn .= '<button type="button" onclick="openMedicalRecordModal(' . $row->patient_id . ', ' . ($historyId ?: 'null') . ', ' . $row->queue_id . ')" class="inline-flex h-8 items-center justify-center rounded-md border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-100 hover:text-slate-900"><i class="fas fa-file-medical mr-1.5 text-slate-400"></i> Medis</button>';
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
            ->select('(SELECT COUNT(*) FROM histories WHERE patient_id = p.id AND is_delete = 0) AS visit_count')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left');

        $search = $request->getPost('search');
        $searchValue = $search['value'] ?? null;

        $role = session()->get('role');
        $active_region = session()->get('active_region');
        $region_session = session()->get('region_patient');

        if ($role === 'superadmin') {
            $filter = ($region && $region !== 'all') ? $region : ($active_region !== 'all' ? $active_region : 'all');
        } else if ($role === 'owner') {
            $filter = ($region && $region !== 'all') ? $region : ($active_region !== 'all' ? $active_region : $region_session);
        } else {
            $filter = $region_session;
        }

        // Smart Logic: Jika sedang mencari, abaikan filter wilayah (lintas wilayah)
        if (!empty($searchValue)) {
            $filter = 'all';
        }

        if ($filter !== 'all' && !empty($filter)) {
            if (is_array($filter)) {
                $builder->whereIn('p.region_id', $filter);
            } else {
                $builder->where('p.region_id', $filter);
            }
        }

        $builder->where('p.is_delete', 0);

        return DataTable::of($builder)
            ->filter(function ($builder) use ($request) {
                $search = $request->getPost('search');
                $searchValue = $search['value'] ?? null;
                if ($searchValue) {
                    $builder->groupStart()
                        ->like('p.name', $searchValue, 'both')
                        ->orLike('p.phone', $searchValue, 'both')
                        ->orLike('p.address', $searchValue, 'both')
                        ->orLike('p.id', $searchValue, 'both')
                        ->groupEnd();
                }
            }, true)
            ->add('name', function ($row) {
                return '<div class="font-bold text-slate-800">' . esc($row->name) . '</div>' .
                    ($row->phone ? '<div class="text-[11px] font-medium text-slate-400 mt-0.5"><i class="fab fa-whatsapp mr-1"></i>' . esc($row->phone) . '</div>' : '');
            })
            ->add('address', function ($row) {
                $addressFilter = array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]);
                return '<div class="text-xs text-slate-600 max-w-200px truncate" title="' . esc(implode(', ', $addressFilter)) . '">' . esc(implode(', ', $addressFilter)) . '</div>';
            })
            ->add('description', function ($row) {
                if ($row->visit_count > 0) {
                    return '<span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Lama</span>';
                } else {
                    return '<span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Baru</span>';
                }
            })
            ->add('action', function ($row) {
                return '<button type="button" onclick="window.tambahKeAntrean(' . $row->patient_id . ')" class="inline-flex h-8 items-center justify-center rounded-md bg-teal-600 px-3 text-[11px] font-bold uppercase tracking-wider text-white shadow transition hover:bg-teal-700">Pilih <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i></button>';
            })
            ->toJson(true);
    }

    public function addToQueue($patientId)
    {
        $patient = $this->db->table('patients')->where('id', $patientId)->get()->getRow();

        $active_region = session()->get('active_region');
        // Gunakan wilayah aktif saat ini. Jika 'all' (superadmin), gunakan wilayah asal pasien sebagai fallback.
        $target_region = ($active_region !== 'all') ? $active_region : $patient->region_id;
        $today = date('Y-m-d');

        // Hitung nomor antrean selanjutnya untuk wilayah ini pada hari ini
        $lastQueue = $this->db->table('patient_queues')
            ->where('region_id', $target_region)
            ->where('queue_date', $today)
            ->orderBy('queue_number', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        $nextQueueNumber = ($lastQueue) ? $lastQueue->queue_number + 1 : 1;

        $insert = $this->db->table('patient_queues')->insert([
            'region_id' => $target_region,
            'queue_number' => $nextQueueNumber,
            'patient_id' => $patientId,
            'queue_date' => $today,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($insert) {
            // Dapatkan ID antrean yang baru dibuat
            $queueId = $this->db->insertID();
            
            // Otomatis buat draft rekam medis yang ter-link dengan antrean
            $this->db->table('histories')->insert([
                'patient_queue_id' => $queueId,
                'patient_id' => $patientId,
                'history_region' => $target_region,
                'type' => 'draft',
                'is_delete' => 0
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Pasien ' . $patient->name . ' berhasil ditambahkan ke antrean.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Terjadi kesalahan sistem saat menambah antrean.'
        ])->setStatusCode(500);
    }

    public function procesToQueue($queueId)
    {
        $queue = $this->db->table('patient_queues')->where('id', $queueId)->get()->getRow();
        if (!$queue)
            return redirect()->back()->with('message', ['error', 'Antrean tidak ditemukan.']);

        // Update waktu mulai terapi di draft rekam medis yang sudah ada
        $this->db->table('histories')
            ->where('patient_queue_id', $queueId)
            ->update(['process_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('antrean')->with('message', ['success', 'Terapi dimulai.']);
    }

    public function finishQueue($queueId)
    {
        // Update waktu selesai terapi di draft rekam medis
        $this->db->table('histories')
            ->where('patient_queue_id', $queueId)
            ->update(['finish_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('antrean')->with('message', ['success', 'Pasien telah selesai menjalani Terapi.']);
    }

    public function daftarAntrean()
    {
        $db = \Config\Database::connect();
        $isLogin = session()->get('isLogin');
        
        if ($isLogin) {
            $active_region = session()->get('active_region');
            $role = session()->get('role');
            $region_session = session()->get('region_patient');
            
            if ($role === 'superadmin') {
                $regionId = $this->request->getGet('region') ?: ($active_region !== 'all' ? $active_region : null);
            } else if ($role === 'owner') {
                $regionId = $this->request->getGet('region') ?: ($active_region !== 'all' ? $active_region : $region_session);
            } else {
                $regionId = $region_session;
            }
        } else {
            $regionId = $this->request->getGet('region');
            $data['isPublic'] = true;
        }

        // Handle comma separated string from URL
        if (is_string($regionId) && strpos($regionId, ',') !== false) {
            $regionId = explode(',', $regionId);
        }
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');
        $builder = $db->table('patient_queues pq')
            ->select('pq.*, h.process_at, h.finish_at, p.id as patient_id, p.name as patient_name, p.address, p.phone as patient_phone, p.age as patient_age, r.name as name_region, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama')
            ->select('MAX(h_visit.date) AS last_visit_date, COUNT(h_visit.id) AS visit_count')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h_visit', 'h_visit.patient_id = p.id AND h_visit.is_delete = 0', 'left')
            ->groupBy('pq.id, h.id, p.id, r.id, pa.id'); // pq.* included in group by logic usually requires explicit columns or database config allows it

        if (!empty($regionId)) {
            if (is_array($regionId)) {
                $builder->whereIn('p.region_id', $regionId);
            } else {
                $builder->where('p.region_id', $regionId);
            }
        }

        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(pq.queue_date) >=', $startDate)
                ->where('DATE(pq.queue_date) <=', $endDate);
        }

        $data['patient_queues'] = $builder->orderBy('h.finish_at', 'ASC')
            ->orderBy('pq.created_at', 'ASC')
            ->get()->getResult();

        $statsBuilder = $db->table('patient_queues pq')
            ->select('
                SUM(CASE WHEN h.process_at IS NOT NULL AND h.finish_at IS NULL THEN 1 ELSE 0 END) as processed,
                SUM(CASE WHEN h.finish_at IS NOT NULL THEN 1 ELSE 0 END) as finished
            ')
            ->join('histories h', 'h.patient_queue_id = pq.id AND h.is_delete = 0', 'left')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->where('DATE(pq.queue_date) >=', $startDate)
            ->where('DATE(pq.queue_date) <=', $endDate);

        if (!empty($regionId)) {
            if (is_array($regionId)) {
                $statsBuilder->whereIn('p.region_id', $regionId);
            } else {
                $statsBuilder->where('p.region_id', $regionId);
            }
        }

        $stats = $statsBuilder->get()->getRow();
        $data['processed_queues'] = $stats->processed ?? 0;
        $data['finished_queues'] = $stats->finished ?? 0;
        $data['waiting_queues'] = count($data['patient_queues']) - ($data['processed_queues'] + $data['finished_queues']);

        if (!empty($regionId)) {
            if (is_array($regionId)) {
                $regs = $db->table('regions')->whereIn('id', $regionId)->get()->getResult();
                $data['regionName'] = !empty($regs) ? implode(', ', array_column($regs, 'name')) : 'Wilayah Tidak Ditemukan';
            } else {
                $reg = $db->table('regions')->where('id', $regionId)->get()->getRow();
                $data['regionName'] = $reg ? $reg->name : 'Semua Wilayah';
            }
        } else {
            $data['regionName'] = 'Semua Wilayah';
        }

        $time = Time::parse($startDate, 'Asia/Jakarta', 'id_ID');
        $data['currentDate'] = $time->toLocalizedString('EEEE, dd/MM/yyyy');

        return view('App\modules\antrean\Views\daftar-antrean\index', $data);
    }

    public function export_excell_antrean()
    {
        if (session()->get('role') !== 'superadmin') {
            return redirect()->to('antrean')->with('message', ['error', 'Unauthorized access']);
        }

        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');
        $regionId = $this->request->getGet('region');
        $region_session = session()->get('region_patient');

        if (empty($regionId) || $regionId === 'all') {
            $regionId = $region_session;
        }
        
        // Handle comma separated string from URL
        if (is_string($regionId) && strpos($regionId, ',') !== false) {
            $regionId = explode(',', $regionId);
        }


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
            if (is_array($regionId)) {
                $builder->whereIn('pq.region_id', $regionId);
            } else {
                $builder->where('pq.region_id', $regionId);
            }
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
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
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
        if (session()->get('role') !== 'superadmin') {
            return redirect()->to('antrean')->with('message', ['error', 'Unauthorized access']);
        }

        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');
        $region = $this->request->getGet('region');
        $region_session = session()->get('region_patient');

        if (empty($region) || $region === 'all') {
            $region = $region_session;
        }

        // Handle comma separated string from URL
        if (is_string($region) && strpos($region, ',') !== false) {
            $region = explode(',', $region);
        }

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

        if (!empty($region)) {
            if (is_array($region)) {
                $builder->whereIn('pq.region_id', $region);
            } else {
                $builder->where('pq.region_id', $region);
            }
        }
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
