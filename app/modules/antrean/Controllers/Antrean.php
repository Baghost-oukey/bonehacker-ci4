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
            ->select('pq.id as queue_id, pq.queue_date, p.id as patient_id, p.name as patient_name, p.age, p.phone, p.address, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, h.process_at, h.finish_at')
            ->select('(SELECT COUNT(h2.id) FROM histories h2 WHERE h2.patient_id = p.id AND h2.is_delete = 0) AS visit_count')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->where('h.finish_at', null);

        $regions_session = json_decode(session()->get('regions_patient'), true);
        if (session()->get('role') === 'user' && !empty($regions_session)) {
            $builder->whereIn('pq.region_id', is_array($regions_session) ? $regions_session : [$regions_session]);
        }

        // Filter Input User
        if (!empty($region)) $builder->where('p.region_id', $region);
        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(pq.queue_date) >=', $startDate)
                ->where('DATE(pq.queue_date) <=', $endDate);
        }

        return \Hermawan\DataTables\DataTable::of($builder)
            ->add('date', function ($row) {
                return !empty($row->queue_date) ? date('d-m-Y', strtotime($row->queue_date)) : '-';
            })
            ->add('address_full', function ($row) {
                return implode(', ', array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]));
            })
            ->add('description', function ($row) {
                return $row->visit_count > 0 ? 'Pasien Lama' : 'Pasien Baru';
            })
            ->add('action', function ($row) {
                $btn = '<div class="btn-group">';
                if ($row->process_at !== null) {
                    $btn .= '<a href="' . site_url('antrean/finishQueue/' . $row->queue_id) . '" class="btn btn-warning btn-sm">Selesai</a>';
                } else {
                    $btn .= '<a href="' . site_url('antrean/processQueue/' . $row->queue_id) . '" class="btn btn-success btn-sm">Proses</a>';
                }
                $btn .= '<a href="' . site_url('patient/show/' . $row->patient_id) . '?openModalRiwayat=true&queue_id=' . $row->queue_id . '" class="btn btn-info btn-sm"><i class="fas fa-file-medical"></i> Rekam Medis</a>';
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
                return $row->visit_count > 0 ? '<span class="badge badge-info">Pasien Lama</span>' : '<span class="badge badge-success">Pasien Baru</span>';
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

        return redirect()->to('antrean')->with('message', ['success', 'Terapi selesai.']);
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

        // Load View standar (Bukan Blade)
        return view('App\modules\antrean\Views\views_daftar_antrean', $data);
    }
}
