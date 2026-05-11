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
        $region_patient = $this->session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $msg = $this->session->getFlashdata('message') ?? $this->session->getFlashdata('msg');
        $model_region = new \App\modules\region\Models\MRegion();

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik Gender',
            'wilayah'         => $model_region->getData(null, $allowed_regions),
            'msg'             => $msg ?? ['', '', '']
        ];
        return view('App\modules\statistikgender\Views\views_statistik_gender', $data);
    }

    public function fetch_statistics()
    {
        $startDate   = $this->request->getGet('start_date');
        $endDate     = $this->request->getGet('end_date');
        $filter      = $this->request->getGet('filter') ?? 'daily';
        $getRegionId = $this->request->getVar('region_id');
        if (!$getRegionId || $getRegionId === 'null' || $getRegionId === '') {
            $rp = $this->session->get('region_patient');
            $getRegionId = ($rp !== 'all' && !empty($rp)) ? (is_array($rp) ? $rp[0] : $rp) : null;
        }
        $regionId = (!empty($getRegionId)) ? (int)$getRegionId : null;
        $data = $this->model_statistic_gender->get_statistics($startDate, $endDate, $regionId, $filter);
        return $this->response->setJSON($data);
    }
}
