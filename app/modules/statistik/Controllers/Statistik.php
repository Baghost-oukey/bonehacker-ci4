<?php

namespace App\modules\statistik\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use App\modules\statistik\Models\MStatistik;
use CodeIgniter\HTTP\ResponseInterface;

class Statistik extends BaseController
{
    protected $model_statistik;
    protected $model_region;
    protected $session;

    public function __construct()
    {
        $this->model_statistik = new MStatistik();
        $this->model_region = new MRegion();
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        $region_patient = $this->session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik',
            'wilayah'         => $this->model_region->getData(null, $allowed_regions),
            'msg'             => $this->session->getFlashdata('message') ?: ['', '', '']
        ];

        return view('App\modules\statistik\Views\views_statistik', $data);
    }

    // public function fetch_statistics()
    // {
    //     $startDate = $this->request->getGet('start_date');
    //     $endDate   = $this->request->getGet('end_date');
    //     $filter    = $this->request->getGet('filter') ?? 'daily';
    //     $regid     = $this->request->getGet('region_id');
    //     $data = $this->model_statistik->get_statistics($startDate, $endDate, $regid, $filter);

    //     return $this->response->setJSON($data);
    // }
    public function fetch_analysis()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        // Use URL param if provided, otherwise fall back to session
        $regionId  = $this->request->getGet('region_id');
        if (!$regionId || $regionId === 'all') {
            $rp = $this->session->get('region_patient');
            $regionId = ($rp !== 'all' && !empty($rp)) ? (is_array($rp) ? $rp[0] : $rp) : null;
        }
        $result = $this->model_statistik->get_analisis($startDate, $endDate, $regionId);

        $summary = [
            'total' => 0,
            'baru'  => 0,
            'lama'  => 0,
            'avg_per_day' => 0
        ];

        foreach ($result as $row) {
            $summary['total'] += (int)$row->total_pasien;
            $summary['baru']  += (int)$row->pasien_baru;
            $summary['lama']  += (int)$row->pasien_lama;
        }

        $diff = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;
        $summary['avg_per_day'] = $diff > 0 ? round($summary['total'] / $diff, 1) : 0;

        return $this->response->setJSON([
            'summary' => $summary,
            'details' => $result
        ]);
    }
}
