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

        // Tentukan filter wilayah (Logika yang sama dengan Beranda)
        if ($role === 'owner' || $role === 'superadmin') {
            $filter = ($activeRegion && $activeRegion !== 'all') ? $activeRegion : 'all';
        } else {
            $filter = $regionProfile;
        }


        $patient_query = $mPatient->getAllData($filter);
        $patients = $patient_query->getResult();



        $data = [
            'realname' => session()->get('realname'),
            'role' => session()->get('role'),
            // 'regions_patient' => $regions_patient,
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title' => 'Rekam Medis',
            'msg' => session()->getFlashdata('message'),
            'wilayah' => $mRegion->getData() ?? [],
            'resources' => $mPatient->get_resources() ?? [],
            'negara' => $mCountries->getData() ?? [],
            'patients' => $patients,
            'patient_information' => null,

        ];

        return view('App\Modules\RekamMedis\Views\index', $data);
    }
}
