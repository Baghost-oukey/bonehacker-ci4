<?php

namespace App\modules\statisktik\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use App\modules\statisktik\Models\MStatistik;
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
        //
        $msg = $this->session->get('pesan');
        $regions_patient = json_decode($this->session->get('regions_patient') ?? '[]', true);

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'regions_patient' => $regions_patient,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik',
            'wilayah'         => $this->model_region->findAll(), // Sesuaikan method di model CI4 Anda
            'msg'             => $msg ?: $this->session->getFlashdata('message') ?: ['', '', '']
        ];

        return view('App\modules\statisktik\Views\views_statistik', $data);
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
        $regionId  = $this->request->getGet('region_id');
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
