<?php

namespace App\Modules\RekamMedis\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RekamMedisController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLogin')) {
            return redirect()->to(base_url('auth'));
        }

        $mRegion = model('App\modules\region\Models\MRegion');
        $mCountries = model('App\modules\countries\Models\MCountries');
        $mPatient = model('App\modules\patients\Models\MPatients');

        $role = session()->get('role');
        $activeRegion = session()->get('active_region');
        $regionProfile = session()->get('region_patient');
        $sessRegionName = session()->get('region_name');
        $sessRegionId = session()->get('region_id');

        // Tentukan filter wilayah
        if ($role === 'owner' || $role === 'superadmin') {
            $filter = ($activeRegion && $activeRegion !== 'all') ? $activeRegion : 'all';
        } else {
            $filter = $regionProfile;
        }

        $patient_query = $mPatient->getAllData($filter);
        $patients = $patient_query->getResult();

        $data = [
            'realname' => session()->get('realname'),
            'role' => $role,
            'title' => 'Rekam Medis',
            'msg' => session()->getFlashdata('message'),
            'wilayah' => $mRegion->getData() ?? [],
            'resources' => $mPatient->get_resources() ?? [],
            'negara' => $mCountries->getData() ?? [],
            'patients' => $patients,
            'patient_information' => null,
            'sess_region_name' => $sessRegionName,
            'sess_region_id' => $sessRegionId,
            'sess_role' => $role,
            'filter_region' => $filter,
        ];

        return view('App\Modules\RekamMedis\Views\index', $data);
    }
}
