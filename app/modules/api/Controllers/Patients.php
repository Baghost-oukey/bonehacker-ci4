<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use App\modules\patients\Models\MPatients;
use CodeIgniter\API\ResponseTrait;

class Patients extends BaseController
{
    use ResponseTrait;

    protected $patientModel;
    protected $db;

    public function __construct()
    {
        $this->patientModel = new MPatients();
        $this->db = \Config\Database::connect();
    }

    /**
     * Search Patients
     * GET /api/patients?search=...&region_id=...
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $regionId = $this->request->getGet('region_id');

        $builder = $this->db->table('patients p')
            ->select('p.id, p.name, p.phone, p.address, p.age, r.name as region_name, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->where('p.is_delete', 0);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.phone', $search)
                ->groupEnd();
        }

        if (!empty($regionId) && $regionId !== 'all') {
            $builder->where('p.region_id', $regionId);
        }

        $patients = $builder->limit(20)->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'data' => $patients
        ]);
    }

    /**
     * Add existing patient to queue
     * POST /api/patients/add-to-queue
     */
    public function addToQueue()
    {
        $patientId = $this->request->getPost('patient_id');
        $patient = $this->patientModel->find($patientId);

        if (!$patient) {
            return $this->failNotFound('Pasien tidak ditemukan');
        }

        $insert = $this->db->table('patient_queues')->insert([
            'region_id' => $patient->region_id,
            'patient_id' => $patientId,
            'queue_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($insert) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Pasien berhasil ditambahkan ke antrean'
            ]);
        }

        return $this->fail('Gagal menambahkan ke antrean');
    }

    /**
     * Register New Patient
     * POST /api/patients/register
     */
    public function register()
    {
        $data = $this->request->getPost();

        // Validation
        if (empty($data['name'])) return $this->fail('Nama wajib diisi');

        $patientData = [
            'name' => $data['name'],
            'gender' => $data['gender'] ?? 'Man',
            'age' => $data['age'] ?? 0,
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
            'region_id' => $data['region_id'],
            'created_by' => $data['user_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'domestic' => 1,
        ];

        if ($this->patientModel->insert($patientData)) {
            $patientId = $this->patientModel->getInsertID();

            // Insert default address record
            $this->db->table('patient_address')->insert([
                'patient_id' => $patientId,
                'date_created' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Pasien berhasil didaftarkan',
                'data' => ['id' => $patientId]
            ]);
        }

        return $this->fail('Gagal mendaftarkan pasien');
    }
}
