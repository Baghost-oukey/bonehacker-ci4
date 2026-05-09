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
        $region_patient = $session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $model_region = new \App\modules\region\Models\MRegion();

        $data = [
            'realname'         => $session->get('realname'),
            'role'             => $session->get('role'),
            'base_url'         => base_url(),
            'current_segment'  => $this->request->getUri()->getSegment(1),
            'wilayah'          => $model_region->getData(null, $allowed_regions),
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

        if (!$regionId || $regionId === 'null') {
            $rp = session()->get('region_patient');
            $regionId = ($rp !== 'all' && !empty($rp)) ? (is_array($rp) ? $rp[0] : $rp) : null;
        }

        $resultData = [];
        if ($tag === 'complaint') {
            $resultData = $this->model_statistictag->getComplaintStatistic($startDate, $endDate, $regionId);
        } elseif ($tag === 'medhis') {
            $resultData = $this->model_statistictag->getMedhisStatistic($startDate, $endDate, $regionId);
        }
        return $this->response->setJSON($resultData);
    }
}
