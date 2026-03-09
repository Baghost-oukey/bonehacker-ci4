<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        //
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $mRegion    = model('App\modules\region\Models\MRegion');
        $mCountries = model('App\modules\countries\Models\MCountries');
        $mPatient   = model('App\modules\patient\Models\MPatient');

        $regions_patient = session()->get('regions_patient');

        $data = [
            'realname'        => session()->get('realname'),
            'role'            => session()->get('role'),
            'regions_patient' => $regions_patient,
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Rekam Medis',
            'msg'             => session()->getFlashdata('message'),
            // Memanggil data dari model
            'wilayah'         => $mRegion->getData(),
            'resources'       => $mPatient->get_resources(),
            'negara'          => $mCountries->getData()
        ];

        return view('\App\modules\dashboard\Views\dashboard_view', $data);
    }
}
