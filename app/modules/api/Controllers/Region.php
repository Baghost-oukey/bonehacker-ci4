<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use CodeIgniter\API\ResponseTrait;

class Region extends BaseController
{
    use ResponseTrait;

    protected $regionModel;

    public function __construct()
    {
        $this->regionModel = new MRegion();
    }

    /**
     * Get list of regions/branches
     * GET /api/regions
     */
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $options = [
            'limit' => $limit,
            'offset' => $offset,
            'order' => 'r.name',
            'mode' => 'ASC'
        ];

        if (!empty($search)) {
            $options['where_like'] = ["r.name LIKE '%$search%'"];
        }

        $regions = $this->regionModel->getListData($options);
        
        return $this->respond([
            'status' => 'success',
            'data' => $regions
        ]);
    }

    /**
     * Store new region
     * POST /api/regions/store
     */
    public function store()
    {
        $name = strtoupper(trim($this->request->getPost('name')));
        if (empty($name)) return $this->fail('Nama cabang wajib diisi');

        $isExist = $this->regionModel->where('name', $name)->first();
        if ($isExist) return $this->fail('Cabang sudah terdaftar');

        $data = ['name' => $name];
        if ($this->regionModel->insert($data)) {
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Cabang berhasil ditambahkan',
                'id' => $this->regionModel->getInsertID()
            ]);
        }
        return $this->fail('Gagal menyimpan data');
    }

    /**
     * Update existing region
     * POST /api/regions/update/(:num)
     */
    public function update($id = null)
    {
        if (!$id) return $this->fail('ID Cabang diperlukan');

        $name = strtoupper(trim($this->request->getPost('name')));
        if (empty($name)) return $this->fail('Nama cabang wajib diisi');

        $isExist = $this->regionModel->where('name', $name)->where('id !=', $id)->first();
        if ($isExist) return $this->fail('Nama cabang sudah digunakan');

        $data = ['name' => $name];
        if ($this->regionModel->update($id, $data)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Cabang berhasil diperbarui'
            ]);
        }
        return $this->fail('Gagal memperbarui data');
    }

    /**
     * Delete region
     * POST /api/regions/delete/(:num)
     */
    public function delete($id = null)
    {
        if (!$id) return $this->fail('ID Cabang diperlukan');

        $db = \Config\Database::connect();
        $countPatient = $db->table('patients')->where("region_id", $id)->countAllResults();

        if ($countPatient > 0) {
            return $this->fail('Gagal! Cabang masih digunakan oleh data pasien.');
        }

        if ($this->regionModel->delete($id)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Cabang berhasil dihapus'
            ]);
        }
        return $this->fail('Gagal menghapus data');
    }
}
