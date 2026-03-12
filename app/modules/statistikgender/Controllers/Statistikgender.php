<?php

namespace App\modules\statistikgender\Controllers;


use App\Controllers\BaseController;
use App\modules\statistikgender\Models\MStatistikgender;
use CodeIgniter\HTTP\ResponseInterface;

class Statistikgender extends BaseController
{

    protected $model_statistic_gender;
    protected $session;

    public function __construct()
    {
        $this->model_statistic_gender = new Mstatistikgender();
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        //
        $regions_patient = $this->session->get('regions_patient');
        $regions_patient = is_string($regions_patient) ? json_decode($regions_patient, true) : $regions_patient;

        // Ambil flash data untuk pesan (jika ada)
        $msg = $this->session->getFlashdata('message') ?? $this->session->getFlashdata('msg');

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'regions_patient' => $regions_patient,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik Gender',
            'wilayah'         => $this->model_statistic_gender->getRegions(), 
            'msg'             => $msg ?? ['', '', '']
        ];
        return view('App\modules\statistikgender\Views\views_statistik_gender', $data);
    }

    public function fetch_statistics()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $filter    = $this->request->getGet('filter') ?? 'daily';
        $regid     = $this->request->getGet('region_id');
        $data = $this->model_statistic_gender->get_statistics($startDate, $endDate, $regid, $filter);
        return $this->response->setJSON($data);
    }
}
