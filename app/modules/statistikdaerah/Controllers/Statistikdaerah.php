<?php

namespace App\modules\statistikdaerah\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use App\modules\statistikdaerah\Models\Mstatistikdaerah;
use CodeIgniter\HTTP\ResponseInterface;

class Statistikdaerah extends BaseController
{

    protected $model_statistic_daerah;
    protected $session;

    public function __construct()
    {
        $this->model_statistic_daerah = new Mstatistikdaerah();
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        //
        $region_patient = $this->session->get('regions_patient');
        $region_patient = is_string($region_patient) ? json_decode($region_patient, true) : $region_patient;

        $msg = $this->session->getFlashdata('message') ?? $this->session->getFlashdata('msg');

        $model_region = new MRegion();
        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'regions_patient' => $region_patient,
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik Daerah',
            'wilayah'         => $model_region->getData(),
            'msg'             => $msg ?? ['', '', '']
        ];

        return view('App\modules\statistikdaerah\Views\views_statistik_daerah', $data);
    }
    public function fetch_statistics()
    {
        $regionId = $this->request->getGet('region_id');
        $kabupatenId = $this->request->getGet('kabupaten_id');
        $kecamatanId = $this->request->getGet('kecamatan_id');
        $desaId      = $this->request->getGet('desa_id');

        $data = $this->model_statistic_daerah->get_statistic(
            $this->request->getGet('start_date'),
            $this->request->getGet('end_date'),
            !empty($regionId) ? (int)$regionId : null,
            $this->request->getGet('filter') ?? 'daily',
            !empty($kabupatenId) ? $kabupatenId : null,
            !empty($kecamatanId) ? $kecamatanId : null,
            !empty($desaId) ? $desaId : null
        );

        return $this->response->setJSON($data);
    }

    public function fetch_kabupaten()
    {
        $kabupaten = $this->model_statistic_daerah->get_all_kabupaten();
        return $this->response->setJSON($kabupaten);
    }

    public function fetch_kecamatan()
    {
        $kabupatenId = $this->request->getGet('kabupaten_id');
        $kecamatan   = $this->model_statistic_daerah->get_kecamatan_by_kabupaten($kabupatenId);
        return $this->response->setJSON($kecamatan);
    }

    public function fetch_desa()
    {
        $kecamatanId = $this->request->getGet('kecamatan_id');
        $desa        = $this->model_statistic_daerah->get_desa_by_kecamatan($kecamatanId);
        return $this->response->setJSON($desa);
    }
}
