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
        $region_patient = $session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $model_region = new \App\modules\region\Models\MRegion();

        $data = [
            'realname'        => $session->get('realname'),
            'role'            => $session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'wilayah'         => $model_region->getData(null, $allowed_regions),
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
        if (empty($regionId) || $regionId === 'null') {
            $rp = session()->get('region_patient');
            $regionId = ($rp !== 'all' && !empty($rp)) ? (is_array($rp) ? $rp[0] : $rp) : null;
        }
        $data = $this->model_statistik_result->getResultStatistic($startDate, $endDate, $regionId, $filter);

        return $this->response->setJSON($data);
    }
}
