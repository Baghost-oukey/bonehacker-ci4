<?php

namespace App\Modules\Region\Controllers;

use App\Controllers\BaseController;
use App\modules\region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;

class RegionController extends BaseController
{
    protected $model_regions;

    public function __construct()
    {
        $this->model_regions = new MRegion();
    }


    public function index()
    {
        //
        $data = [
            'realname' => session()->get('realname'),
            'role' => session()->get('role'),
            'base_url' => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title' => 'Cabang',
            'msg' => session()->getFlashdata('message')
        ];

        return view('App\Modules\Region\Views\index', $data);
    }

    public function fetch()
    {
        $requestData = $this->request->getPost();
        $order = $this->request->getPost('order');
        $column = $this->request->getPost('columns');

        $options = [];
        $options['order'] = !empty($order) && !empty($column) ? $column[$order[0]['column']]['data'] : 'r.id';
        $options['mode'] = !empty($order) ? $order[0]['dir'] : 'ASC';

        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $options['offset'] = empty($start) ? 0 : $start;
        $options['limit'] = empty($length) ? 10 : $length;
        if (!empty($requestData['search']['value'])) {
            $options['where_like'] = [
                "r.name LIKE '%" . $requestData['search']['value'] . "%'"
            ];
        }

        $dataOutput = $this->model_regions->getListData($options);
        $totalFiltered = $this->model_regions->getTotalData($options);
        $totalData = $this->model_regions->getTotal();
        $no = $options['offset'] + 1;

        if (!empty($dataOutput)) {
            foreach ($dataOutput as $value) {
                $value->no = $no;
                $value->name_view = "<a href='" . base_url() . "?region=" . $value->id . "'>" . $value->name . "<br>(" . $value->jumlah . " Pasien)</a>";


                $value->created_at = !empty($value->created_at) ? $value->created_at : '-';
                $value->updated_at = !empty($value->updated_at) ? $value->updated_at : '-';

                $value->action = '
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" 
                            data-name="' . htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8') . '" 
                            data-href="' . base_url('region/update/' . $value->id) . '" 
                            title="Edit Data" 
                            class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600 btn_edit">
                            <i class="fas fa-edit text-xs transition-transform group-hover:scale-110"></i>
                        </button>

                        <button type="button" 
                            data-href="' . base_url('region/destroy/' . $value->id) . '" 
                            title="Hapus Data" 
                            class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 btn_delete">
                            <i class="fas fa-trash text-xs transition-transform group-hover:scale-110"></i>
                        </button>
                    </div>';

                $no++;
            }
        }

        $response = [
            "draw" => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $dataOutput
        ];


        return $this->response->setJSON($response);
    }

    public function store()
    {
        $name = strtoupper(trim($this->request->getPost('name')));
        $isExist = $this->model_regions->where('name', $name)->first();
        $csrfToken = csrf_hash();
        if ($isExist) {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal! Cabang "' . $name . '" sudah terdaftar.',
                'new_token' => $csrfToken
            ]);
        }

        $data = [
            'name' => $name
        ];

        if ($this->model_regions->insert($data)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Data cabang berhasil disimpan!',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal menyimpan data ke database.',
                'new_token' => $csrfToken
            ]);
        }
    }

    public function update($id)
    {
        // $data = ['name' => $this->request->getPost('name')];

        // if ($this->model_regions->update($id, $data)) {
        //     session()->setFlashdata('message', ['success', 'Data Cabang berhasil diubah']);
        // } else {
        //     session()->setFlashdata('message', ['error', 'Data Cabang gagal diubah']);
        // }

        // return redirect()->to(base_url('region'));
        $name = strtoupper(trim($this->request->getPost('name')));
        $csrfToken = csrf_hash();
        $isExist = $this->model_regions->where('name', $name)->where('id !=', $id)->first();

        if ($isExist) {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal! Cabang "' . $name . '" sudah terdaftar.',
                'new_token' => $csrfToken
            ]);
        }

        $data = [
            'name' => $name
        ];

        if ($this->model_regions->update($id, $data)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Data cabang berhasil diubah!',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal mengubah data di database.',
                'new_token' => $csrfToken
            ]);
        }
    }

    public function destroy($id)
    {
        $csrfToken = csrf_hash();
        $db = \Config\Database::connect();
        $countPatient = $db->table('patients')->where("region_id", $id)->countAllResults();

        if ($countPatient > 0) {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Gagal! Cabang masih digunakan oleh data pasien.',
                'new_token' => $csrfToken
            ]);
        }

        if ($this->model_regions->delete($id)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Data Cabang berhasil dihapus!',
                'new_token' => $csrfToken
            ]);
        } else {
            return $this->response->setJSON([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan, gagal menghapus data.',
                'new_token' => $csrfToken
            ]);
        }

        // return redirect()->to(base_url('region'));
    }
}
