<?php

namespace App\modules\dashboard\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $mRegion    = model('App\modules\region\Models\MRegion');
        $mCountries = model('App\modules\countries\Models\MCountries');
        $mPatient   = model('App\modules\patients\Models\MPatients');
        $activeRegion = session()->get('active_region');

        $regions_patient = $mRegion->findAll();

        $data = [
            'realname'        => session()->get('realname'),
            'role'            => session()->get('role'),
            'regions_patient' => $regions_patient,
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Rekam Medis',
            'msg'             => session()->getFlashdata('message'),
            'wilayah'         => $mRegion->getData() ?? [], 
            'resources'       => $mPatient->get_resources() ?? [],
            'negara'          => $mCountries->getData() ?? [],
            'patient_information' => null, 

        ];

        return view('App\modules\dashboard\Views\dashboard_views', $data);
    }
}
