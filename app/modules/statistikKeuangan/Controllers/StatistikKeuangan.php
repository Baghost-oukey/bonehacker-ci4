<?php

namespace App\Modules\StatistikKeuangan\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\transaksi\Models\MTransaksi;
use App\Modules\region\Models\MRegion;

class StatistikKeuangan extends BaseController
{

    protected $mTransaksi;
    protected $mRegion;

    public function __construct()
    {
        $this->mTransaksi = new MTransaksi();
        $this->mRegion    = new MRegion();
    }

    public function index()
    {
        $region_patient = session()->get('region_patient');
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;
        $list_regions = $this->mRegion->getData(null, $allowed_regions);

        $data = [
            'title'        => 'Analisis Keuangan',
            'role'         => session()->get('role'),
            'list_regions' => $list_regions
        ];
        return view('App\Modules\StatistikKeuangan\Views\index', $data);
    }

    public function get_chart_data()
    {
        $days = (int) ($this->request->getGet('days') ?? 7);
        $region_param = $this->request->getGet('region');
        $region_patient = session()->get('region_patient');
        if ($region_param && $region_param !== 'all') {
            $filter_region = $region_param;
        } elseif ($region_patient !== 'all' && !empty($region_patient)) {
            $filter_region = is_array($region_patient) ? $region_patient[0] : $region_patient;
        } else {
            $filter_region = null;
        }

        $trend = $this->mTransaksi->getFinanceTrend($days, $filter_region);
       $structure = $this->mTransaksi->getExpenseStructure($days, $filter_region);
        return $this->response->setJSON([
            'status' => 'success',
            'trend' => $trend,
            'structure' => $structure
        ]);
    }
}
