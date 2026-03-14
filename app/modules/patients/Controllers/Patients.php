<?php

namespace App\modules\patients\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\address\Models\MAddress;
use App\modules\patients\Models;
use App\modules\patients\Models\MPatients;

class Patients extends BaseController
{


    protected $jenisKelamin = ['Man' => 'Laki-laki', 'Woman' => 'Perempuan'];
    protected $patientModel;
    protected $session;

    public function __construct()
    {
        $this->patientModel = new MPatients();
        $this->session = \Config\Services::session();
        helper(['url', 'form']);
    }

    public function store()
    {
        $files = $this->request->getFileMultiple('userfiles');
        $file_urls = [];

        // Handle Multiple Uploads
        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(ROOTPATH . 'public/patient_file/', $newName);
                    $file_urls[] = base_url('patient_file/' . $newName);
                }
            }
        }

        $domestic = ($this->request->getPost('domestic') === 'dalam_negeri') ? 1 : 0;
        $userId   = $this->session->get('userId');

        // Data Pasien
        $patientData = [
            'name'                => $this->request->getPost('name'),
            'gender'              => $this->request->getPost('gender'),
            'age'                 => $this->request->getPost('age') ?: null,
            'country_id'          => $this->request->getPost('country_id'),
            'address'             => $this->request->getPost('address') ?: null,
            'phone'               => $this->request->getPost('phone') ?: null,
            'region_id'           => $this->request->getPost('region_id'),
            'is_suspective'       => $this->request->getPost('is_suspective') === 'on' ? 1 : 0,
            'domestic'            => $domestic,
            'url'                 => json_encode($file_urls),
            'created_by'          => $userId,
            'patient_information' => $this->request->getPost('patient_information') ?: null,
            'ket_suspect'         => $this->request->getPost('ket_rentan') ?: null,
        ];

        $visitDate = $this->request->getPost('visit_date');
        if ($visitDate) {
            $formattedDate = date('Y-m-d', strtotime($visitDate));
            $patientData['created_at'] = $formattedDate . ' ' . date('H:i:s');
        } else {
            $patientData['created_at'] = date('Y-m-d H:i:s');
        }

        // Insert ke Database
        $patientId = $this->patientModel->insert($patientData);

        if ($patientId) {
            // Data Alamat
            $addressModel = new MAddress();
            $addressData = [
                'patient_id'     => $patientId,
                'desa_id'        => $this->request->getPost('desa_id'),
                'desa_nama'      => $this->request->getPost('desa_nama'),
                'kecamatan_id'   => $this->request->getPost('kecamatan_id'),
                'kecamatan_nama' => $this->request->getPost('kecamatan_nama'),
                'kabupaten_id'   => $this->request->getPost('kabupaten_id'),
                'kabupaten_nama' => $this->request->getPost('kabupaten_nama'),
                'provinsi_id'    => $this->request->getPost('provinsi_id'),
                'provinsi_nama'  => $this->request->getPost('provinsi_nama'),
            ];

            $addressModel->insert($addressData);
            session()->setFlashdata('msg', ['success', 'Data Berhasil Disimpan']);
        } else {
            session()->setFlashdata('msg', ['error', 'Gagal menyimpan data pasien']);
        }

        return redirect()->to(site_url('dashboard'));
    }

    public function fetch2()
    {
        $db = db_connect();

        $request = \Config\Services::request();

        $limit = $this->request->getPost('length') ?? 10;
        $start = $this->request->getPost('start') ?? 0;

        $region = $this->request->getPost('region');

        $builder = $db->table('patients p')
            ->select('
            p.*, 
            r.name as name_region, 
            pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama, pa.provinsi_nama,
            (
                SELECT MAX(date) 
                FROM histories h 
                WHERE h.patient_id = p.id AND h.is_delete = 0
            ) AS date,
            (
                SELECT COUNT(h.id)
                FROM histories h 
                WHERE h.patient_id = p.id AND h.is_delete = 0
            ) AS visit_count
        ')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left');
        // ->limit($limit, $start);

        if (!empty($region)) {
            $builder->where('p.region_id', $region);
        }

        $totalFiltered = $builder->countAllResults(false);
        $data = $builder->limit($limit, $start)->get()->getResult();
        $totalData = $db->table('patients')->countAllResults();

        // $data = $builder->get()->getResult();
        $output = [];


        foreach ($data as $row) {
            $addressParts = array_filter([
                $row->address,
                $row->desa_nama,
                $row->kecamatan_nama,
                $row->kabupaten_nama,
                $row->provinsi_nama
            ]);
            $fullAddress = implode(', ', $addressParts);

            $output[] = [
                "id"          => $row->id,
                "name"        => $row->name . ' (' . $row->phone . ')',
                "name_region" => $row->name_region ?? '-',
                "address"     => $fullAddress,
                "date"        => !empty($row->date) ? date('d-m-Y', strtotime($row->date)) : '-', // format_tanggal manual
                "visit_count" => $row->visit_count ?? 0,
                "action"      => '
                <a href="' . site_url('patient/show/' . $row->id) . '" class="btn btn-primary btn-sm mr-1"><i class="fas fa-eye"></i></a>
                <button type="button" class="btn btn-danger btn-sm" onclick="destroy(' . "'" . $row->id . "'" . ')"><i class="fas fa-trash"></i></button>
            ',
                "is_delete"   => $row->is_delete,
                "phone"       => $row->phone
            ];
        }

        return $this->response->setJSON([
            "draw" => intval($this->request->getPost('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $output
        ]);
    }

    public function destroy($id)
    {
        if ($this->patientModel->destroy($id)) {
            $this->session->setFlashdata('message', ['success', 'Data Berhasil dihapus']);
            return $this->response->setJSON(['status' => true]);
        }

        return $this->response->setJSON(['status' => false], 500);
    }

    public function check_phone()
    {
        $phone = $this->request->getPost('phone');
        if (!$phone) {
            return $this->response->setJSON(['exists' => false, 'patients' => []]);
        }

        $phone628 = preg_replace('/^08/', '628', $phone);
        $phone08  = preg_replace('/^628/', '08', $phone);


        $patients = $this->patientModel
            ->whereIn('phone', [$phone08, $phone628])
            ->findAll();

        return $this->response->setJSON([
            'exists'   => !empty($patients),
            'patients' => $patients
        ]);
    }




    public function index()
    {
        //
    }
}
