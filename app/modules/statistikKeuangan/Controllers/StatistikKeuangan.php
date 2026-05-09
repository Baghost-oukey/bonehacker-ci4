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
        $db = \Config\Database::connect();
        $list_regions = $this->mRegion->asArray()->select('id, name')->findAll();

        $data = [
            'title'        => 'Analisis Keuangan',
            'role'         => session()->get('role'),
            'list_regions' => $list_regions
        ];
        return view('App\Modules\StatistikKeuangan\Views\index', $data);
    }

    public function get_chart_data()
    {
        $role = session()->get('role');
        $days = (int) ($this->request->getGet('days') ?? 7);
        $region_param = $this->request->getGet('region');
        if ($role === 'superadmin' || $role === 'owner') {
            $filter_region = ($region_param && $region_param !== 'all') ? $region_param : null;
        } else {
            $filter_region = session()->get('region_id');
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
