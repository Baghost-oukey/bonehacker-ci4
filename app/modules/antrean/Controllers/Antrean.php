<?php

namespace App\modules\antrean\Controllers;

use App\Controllers\BaseController;
use App\modules\countries\Models\MCountries;
use App\modules\patients\Models\MPatients;
use App\modules\region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;
use Hermawan\DataTables\DataTable;

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
            ->select('pq.*, h.process_at, h.finish_at, p.id as patient_id, p.name as patient_name, p.address, p.phone, p.age, r.name as name_region, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama')
            ->select('(SELECT MAX(date) FROM histories h2 WHERE h2.patient_id = p.id AND h2.is_delete = 0) AS last_visit')
            ->select('(SELECT COUNT(h3.id) FROM histories h3 WHERE h3.patient_id = p.id AND h3.is_delete = 0) AS visit_count')
            ->join('histories h', 'h.patient_queue_id = pq.id', 'left')
            ->join('patients p', 'p.id = pq.patient_id', 'left')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
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
            ->add('address_full', function($row) {
                return implode(', ', array_filter([$row->address, $row->desa_nama, $row->kecamatan_nama, $row->kabupaten_nama]));
            })
            ->add('description', function($row) {
                return $row->visit_count > 0 ? 'Pasien Lama' : 'Pasien Baru';
            })
            ->add('action', function($row) {
                $btn = '<div class="btn-group">';
                if ($row->process_at !== null) {
                    $btn .= '<a href="'.site_url('antrean/finishQueue/'.$row->id).'" class="btn btn-warning btn-sm"><i class="fas fa-check"></i> Selesai</a>';
                } else {
                    $btn .= '<a href="'.site_url('antrean/processQueue/'.$row->id).'" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Proses</a>';
                }
                $btn .= '<a href="'.site_url('patient/show/'.$row->patient_id).'?openModalRiwayat=true&queue_id='.$row->id.'" class="btn btn-info btn-sm"><i class="fas fa-file-medical"></i> Rekam Medis</a>';
                $btn .= '</div>';
                return $btn;
            })
            ->toJson();
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
}
