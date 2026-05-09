<?php

namespace App\modules\statistikresource\Controllers;

use App\Controllers\BaseController;
use App\modules\statistikresource\Models\MStatistikResource;
use CodeIgniter\HTTP\ResponseInterface;

class StatistikResource extends BaseController
{


    protected $model_marketing;

    public function __construct()
    {
        $this->model_marketing = new MStatistikResource();
    }

    public function index()
    {
        $session = session();
        $region_patient = $session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $model_region = new \App\modules\region\Models\MRegion();

        $data = [
            'wilayah'         => $model_region->getData(null, $allowed_regions),
            'title'           => "Statistik Media Sosial",
            'role'            => $session->get('role'),
            'realname'        => $session->get('realname'),
            'current_segment' => 'statistikresource',
        ];
        return view('App\modules\statistikresource\Views\views_statistik_resource', $data);
    }

    public function get_marketing_data()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $regionID = $this->request->getGet('region_id');
        if (empty($startDate) || empty($endDate)) {
            $startDate = date('Y-m-01'); 
            $endDate   = date('Y-m-t'); 
        }
        $sources = $this->model_marketing->get_sumber_marketing($startDate, $endDate, $regionID);
        $totalAll = 0;
        if (!empty($sources)) {
            foreach ($sources as $s) {
                $totalAll += (int)$s->total_pasien;
            }
        }
        return $this->response->setJSON([
            'status'             => 'success',
            'csrf_hash'          => csrf_hash(), 
            'total_all_patients' => $totalAll,
            'details'            => $sources
        ]);
    }
}
