<?php

namespace App\modules\region\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\modules\region\Models\MRegion;

class Region extends BaseController
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
            'realname'        => session()->get('realname'),
            'role'            => session()->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Wilayah',
            'msg'             => session()->getFlashdata('message')
        ];

        return view('App/modules/region/Views/region', $data);
    }

    public function fetch()
    {
        $requestData = $this->request->getPost();
        $order = $this->request->getPost('order');
        $column = $this->request->getPost('column');

        $option = [];
        $option['order'] = !empty($order) && !empty($column) ? $column[$order[0]['column']]['data'] : 'name';
        $option['mode'] = !empty($order) ? $order[0]['dir'] : 'asc';

        $start = $this->request->getPost('start');
        $length            = $this->request->getPost('length');
        $options['offset'] = empty($start) ? 0 : $start;
        $options['limit']  = empty($length) ? 10 : $length;
        if (!empty($requestData['search']['value'])) {
            $options['where_like'] = [
                "name LIKE '%" . $requestData['search']['value'] . "%'"
            ];
        }

        $dataOutput    = $this->model_regions->getListData($options);
        $totalFiltered = $this->model_regions->getTotalData($options);
        $totalData     = $this->model_regions->getTotal();
        $no            = $options['offset'] + 1;

        if (!empty($dataOutput)) {
            foreach ($dataOutput as $value) {
                $value->no        = $no;
                $value->name_view = "<a href='" . base_url() . "?region=" . $value->id . "'>" . $value->name . "<br>(" . $value->jumlah . " Pasien)</a>";


                $value->created_at = !empty($value->created_at) ? $value->created_at : '-';
                $value->updated_at = !empty($value->updated_at) ? $value->updated_at : '-';

                $value->action = '
                    <button data-name="' . $value->name . '" data-href="' . base_url('region/update/' . $value->id) . '" class="btn btn-primary btn-action mr-1 btn_edit"><i class="fas fa-edit"></i></button>
                    <button type="button" data-href="' . base_url("region/destroy/" . $value->id) . '" class="btn btn-danger btn-action btn_delete"><i class="fas fa-trash"></i></button>';

                $no++;
            }
        }

        $response = [
            "draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $dataOutput
        ];


        return $this->response->setJSON($response);
    }

    public function store()
    {
        $data = [
            'name' => $this->request->getPost('name')
        ];

        if ($this->model_regions->insert($data)) {
            session()->setFlashdata('message', ['success', 'Data Berhasil diSimpan']);
        } else {
            session()->setFlashdata('message', ['error', 'Failed to save data']);
        }

        return redirect()->to(base_url('region'));
    }

    public function update($id)
    {
        $data = ['name' => $this->request->getPost('name')];

        if ($this->model_regions->update($id, $data)) {
            session()->setFlashdata('message', ['success', 'Data wilayah berhasil diubah']);
        } else {
            session()->setFlashdata('message', ['error', 'Data wilayah gagal diubah']);
        }

        return redirect()->to(base_url('region'));
    }

    public function destroy($id)
    {
        $db = \Config\Database::connect();
        $countPatient = $db->table('patients')->where("region_id", $id)->countAllResults();

        if ($countPatient > 0) {
            session()->setFlashdata('message', ['error', 'Data wilayah masih digunakan']);
            return redirect()->to(base_url('region'));
        }

        if ($this->model_regions->delete($id)) {
            session()->setFlashdata('message', ['success', 'Data wilayah berhasil dihapus']);
        } else {
            session()->setFlashdata('message', ['error', 'Data wilayah gagal dihapus']);
        }

        return redirect()->to(base_url('region'));
    }
}
