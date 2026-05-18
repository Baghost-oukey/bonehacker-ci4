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
        $region_patient = $this->session->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $msg = $this->session->getFlashdata('message') ?? $this->session->getFlashdata('msg');

        $model_region = new MRegion();
        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Statistik Daerah',
            'wilayah'         => $model_region->getData(null, $allowed_regions),
            'msg'             => $msg ?? ['', '', '']
        ];

        return view('App\modules\statistikdaerah\Views\views_statistik_daerah', $data);
    }
    public function fetch_statistics()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        // Validation for secure date formats (Best Practice)
        if (!$startDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-d');
        }
        if (!$endDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-d');
        }

        $regionId = $this->request->getGet('region_id');
        $kabupatenId = $this->request->getGet('kabupaten_id');
        $kecamatanId = $this->request->getGet('kecamatan_id');
        $desaId      = $this->request->getGet('desa_id');

        $data = $this->model_statistic_daerah->get_statistic(
            $startDate,
            $endDate,
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
