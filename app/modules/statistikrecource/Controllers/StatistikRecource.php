<?php

namespace App\modules\statistikrecource\Controllers;

use App\Controllers\BaseController;
use App\modules\statistikrecource\Models\MstatistikRecource;
use CodeIgniter\HTTP\ResponseInterface;

class StatistikRecource extends BaseController
{


    protected $model_marketing;

    public function __construct()
    {
        $this->model_marketing = new MstatistikRecource();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $session = session();
        $data = [
            'wilayah'         => $db->table('regions')->get()->getResult(),
            'title'           => "Statistik Media Sosial",
            'role'            => $session->get('role'),
            'realname'        => $session->get('realname'),
            'current_segment' => 'statistikresource',
        ];
        $data['wilayah'] = $db->table('regions')->get()->getResult();
        $data['title'] = "Statistik Media Sosial";
        return view('App\modules\statistikrecource\Views\views_statistik_resource', $data);
    }

    public function get_marketing_data()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $regionID = $this->request->getGet('region_id');

        if (!$startDate || !$endDate) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Rentang tanggal harus diisi'
            ]);
        }

        $sources = $this->model_marketing->get_sumber_marketing($startDate, $endDate, $regionID);

        $totalAll = 0;
        if (!empty($sources)) {
            foreach ($sources as $s) {
                $totalAll += (int)$s->total_pasien;
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'total_all_patients' => $totalAll,
            'details' => $sources
        ]);
    }
}
