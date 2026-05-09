<?php

namespace App\modules\statistikresult\Controllers;

use App\Controllers\BaseController;
use App\modules\statistikresult\Models\Mstatistikresult;
use CodeIgniter\HTTP\ResponseInterface;

class Statistikresult extends BaseController
{

    protected $model_statistik_result;

    public function __construct()
    {
        $this->model_statistik_result = new Mstatistikresult();
    }
    public function index()
    {
        $session = session();
        $selectedRegionId = $this->request->getGet('region_id');
        $regionsPatient = $session->get('regions_patient');
        $regionsPatientDecoded = is_string($regionsPatient) ? json_decode($regionsPatient, true) : $regionsPatient;

        $data = [
            'realname'        => $session->get('realname'),
            'role'            => $session->get('role'),
            'regions_patient' => $regionsPatientDecoded,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'wilayah'         => $this->model_statistik_result->getRegions(),
            'selected_region' => $selectedRegionId,
            'title'           => 'Statistik Hasil Pemeriksaan',
        ];

        return view('App\modules\statistikresult\Views\views_statistic_result', $data);
    }

    public function fetch_statistics()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $filter    = $this->request->getGet('filter');
        $regionId  = $this->request->getVar('region_id');
        // $regionId  = (!empty($regionId)) ? (int)$regionId: null;
        if (empty($regionId) || $regionId === 'null') {
        $regionId = null;
    }
        $data = $this->model_statistik_result->getResultStatistic($startDate, $endDate, $regionId, $filter);

        return $this->response->setJSON($data);
    }
}
