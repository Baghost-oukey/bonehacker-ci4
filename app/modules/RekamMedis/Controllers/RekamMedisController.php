<?php

namespace App\Modules\RekamMedis\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RekamMedisController extends BaseController
{
    public function index()
    {
        $mRegion = model('App\modules\region\Models\MRegion');
        $mCountries = model('App\modules\countries\Models\MCountries');
        $mPatient = model('App\modules\patients\Models\MPatients');

        $role = session()->get('role');
        $active_region = session()->get('active_region');
        $region_patient = session()->get('region_patient');
        $filter = ($active_region !== 'all') ? $active_region : $region_patient;
        $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

        $data = [
            'realname' => session()->get('realname'),
            'role' => $role,
            'title' => 'Rekam Medis',
            'msg' => session()->getFlashdata('message'),
            'wilayah' => $mRegion->getData(null, $allowed_regions) ?? [],
            'resources' => $mPatient->get_resources() ?? [],
            'negara' => $mCountries->getData() ?? [],
            'patient_information' => null,
            'sess_region_name' => session()->get('active_region_name'),
            'sess_region_id' => $active_region,
            'sess_role' => $role,
            'filter_region' => $filter,
        ];

        return view('App\Modules\RekamMedis\Views\index', $data);
    }
}
