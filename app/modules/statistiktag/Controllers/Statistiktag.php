<?php

namespace App\modules\statistiktag\Controllers;

use App\Controllers\BaseController;
use App\modules\statistiktag\Models\Mstatistiktag;
use CodeIgniter\HTTP\ResponseInterface;

class Statistiktag extends BaseController
{

    protected $model_statistictag;
    public function __construct()
    {

        $this->model_statistictag = new Mstatistiktag();
    }
    public function index()
    {
        $session = session();
        $selectedRegionId = $this->request->getGet('region_id');
        $regionsPatient = $session->get('regions_patient');
        $regionsPatientDecoded = is_string($regionsPatient) ? json_decode($regionsPatient, true) : $regionsPatient;

        $data = [
            'realname'         => $session->get('realname'),
            'role'             => $session->get('role'),
            'regions_patient'  => $regionsPatientDecoded,
            'base_url'         => base_url(),
            'current_segment'  => $this->request->getUri()->getSegment(1),
            'wilayah'          => $this->model_statistictag->getRegions(),
            'selected_region'  => $selectedRegionId,
            'title'            => 'Statistik Keluhan & Riwayat Medis',
        ];

        return view('App\modules\statistiktag\Views\views_statistiktag', $data);
    }

    public function fetch_statistics()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $filter    = $this->request->getGet('filter'); 
        $tag       = $this->request->getGet('tag');    
        $regionId  = $this->request->getGet('region_id');

        $resultData = [];

        // Logika pemilihan model berdasarkan tag
        if ($tag === 'complaint') {
            $resultData = $this->model_statistictag->getComplaintStatistic($startDate, $endDate, $regionId);
        } elseif ($tag === 'medhis') {
            $resultData = $this->model_statistictag->getMedhisStatistic($startDate, $endDate, $regionId);
        }
        log_message('debug', "Data fetched for tag: {$tag}, Start: {$startDate}, End: {$endDate}");
        return $this->response->setJSON($resultData);
    }
}
