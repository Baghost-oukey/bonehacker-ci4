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
     * Search Patients with Pagination
     * GET /api/patients?search=...&region_id=...&page=...&terapis_id=...
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $regionIdInput = $this->request->getGet('region_id');
        $terapisId = $this->request->getGet('terapis_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Determine if user is superadmin to bypass region filter
        $isSuperAdmin = false;
        if ($terapisId) {
            $user = $this->db->table('users')->where('id', $terapisId)->get()->getRow();
            if ($user && ($user->role === 'superadmin' || $user->role === 'owner')) {
                $isSuperAdmin = true;
            }
        }

        $builder = $this->db->table('patients p')
            ->select('p.id, p.name, p.phone, p.address, p.age, r.name as region_name, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->where('p.is_delete', 0)
            ->orderBy('p.id', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.phone', $search)
                ->groupEnd();
        }

        // Only filter by region if NOT superadmin
        if (!$isSuperAdmin && !empty($regionIdInput) && $regionIdInput !== 'all') {
            $builder->where('p.region_id', $regionIdInput);
        }

        $patients = $builder->limit($limit, $offset)->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'page' => $page,
            'limit' => $limit,
            'data' => $patients
        ]);
    }

    /**
     * Get Patient Detail (Biodata)
     * GET /api/patients/show/{id}
     */
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID Pasien tidak boleh kosong');

        $patient = $this->db->table('patients p')
            ->select('p.*, r.name as region_name, pa.desa_nama, pa.kecamatan_nama, pa.kabupaten_nama')
            ->join('regions r', 'r.id = p.region_id', 'left')
            ->join('patient_address pa', 'pa.patient_id = p.id', 'left')
            ->where('p.id', $id)
            ->get()->getRow();

        if (!$patient) {
            return $this->failNotFound('Pasien tidak ditemukan');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $patient
        ]);
    }

    /**
     * Add existing patient to queue
     * POST /api/patients/add-to-queue
     */
    public function addToQueue()
    {
        $patientId = $this->request->getPost('patient_id');
        
        if (empty($patientId)) {
            return $this->fail('ID Pasien tidak boleh kosong');
        }

        $patient = $this->patientModel->find($patientId);

        if (!$patient) {
            return $this->failNotFound('Pasien dengan ID ' . $patientId . ' tidak ditemukan');
        }

        // Logic: Prioritize patient's region (like website version), then fallback to request's region
        $regionId = $patient->region_id;

        // If patient region is empty or invalid string like '[]', use request region_id
        if (empty($regionId) || $regionId === '[]' || $regionId === 0) {
            $regionId = $this->request->getPost('region_id');
        }

        // Clean up if it's still '[]'
        if ($regionId === '[]') $regionId = null;

        if (empty($regionId)) {
            return $this->fail('Wilayah tidak ditemukan. Pastikan pasien memiliki wilayah atau akun anda memiliki akses wilayah yang benar.');
        }

        // Validate that region exists in DB
        $checkRegion = $this->db->table('regions')->where('id', $regionId)->get()->getRow();
        if (!$checkRegion) {
            return $this->fail("ID Wilayah ($regionId) tidak valid atau tidak ditemukan. Mohon hubungi Admin untuk setting wilayah.");
        }

        $data = [
            'region_id'  => (int) $regionId,
            'patient_id' => (int) $patientId,
            'queue_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $insert = $this->db->table('patient_queues')->insert($data);
            
            if ($insert) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Pasien ' . $patient->name . ' berhasil ditambahkan ke antrean'
                ]);
            }
            
            $error = $this->db->error();
            return $this->fail('Gagal menyimpan ke database: ' . ($error['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            return $this->fail('Terjadi eksepsi: ' . $e->getMessage());
        }
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

    /**
     * Upload File for Patient
     * POST /api/patients/upload-file
     */
    public function uploadFile()
    {
        $id = $this->request->getPost('id');
        if (!$id) return $this->fail('ID Pasien tidak valid');

        $patient = $this->patientModel->find($id);
        if (!$patient) return $this->failNotFound('Pasien tidak ditemukan');

        $existingFiles = empty($patient->url) ? [] : json_decode($patient->url, true);
        $newFileUrls = [];
        $files = $this->request->getFileMultiple('userfiles');

        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
                    if (in_array($file->getMimeType(), $allowedTypes) && $file->getSizeByUnit('kb') <= 2048) {
                        $newName = $file->getRandomName();
                        if ($file->move(FCPATH . 'patient_file', $newName)) {
                            // Store relative path or full URL? Web uses base_url('patient_file/' . $newName) in some places
                            // To be safe and consistent, we'll store the filename and handle URL prefixing in the response or app
                            $newFileUrls[] = base_url('patient_file/' . $newName);
                        }
                    } else {
                        return $this->fail('Format file tidak didukung atau ukuran terlalu besar (Max 2MB)');
                    }
                }
            }
        }

        $finalFileUrls = array_merge($existingFiles, $newFileUrls);
        $updateData = [
            'url' => json_encode($finalFileUrls),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->request->getPost('user_id')
        ];

        if ($this->patientModel->update($id, $updateData)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'File berhasil diunggah',
                'data' => $finalFileUrls
            ]);
        }

        return $this->fail('Gagal memperbarui data file pasien');
    }

    /**
     * Delete File for Patient
     * POST /api/patients/delete-file
     */
    public function deleteFile()
    {
        $id = $this->request->getPost('id');
        $fileUrl = $this->request->getPost('file_url');

        if (!$id || !$fileUrl) return $this->fail('Data tidak lengkap');

        $patient = $this->patientModel->find($id);
        if (!$patient) return $this->failNotFound('Pasien tidak ditemukan');

        $existingFiles = empty($patient->url) ? [] : json_decode($patient->url, true);
        
        // Find and remove the file
        if (($key = array_search($fileUrl, $existingFiles)) !== false) {
            // Attempt to delete physical file
            $relativeInfo = parse_url($fileUrl);
            $filePath = FCPATH . ltrim($relativeInfo['path'], '/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            unset($existingFiles[$key]);
            $existingFiles = array_values($existingFiles);

            $updateData = [
                'url' => json_encode($existingFiles),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->request->getPost('user_id')
            ];

            if ($this->patientModel->update($id, $updateData)) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'File berhasil dihapus',
                    'data' => $existingFiles
                ]);
            }
        }

        return $this->fail('Gagal menghapus file atau file tidak ditemukan');
    }
}
