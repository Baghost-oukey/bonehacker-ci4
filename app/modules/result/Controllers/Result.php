<?php

namespace App\modules\result\Controllers;

use App\Controllers\BaseController;
use App\modules\result\Models\MResult;
use CodeIgniter\HTTP\ResponseInterface;

class Result extends BaseController
{

    protected $model_result;
    protected $session;

    public function __construct()
    {
        $this->model_result = new MResult();
        $this->session = \Config\Services::session();
    }
    public function index()
    {
        //
        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Tag Hasil Pemeriksaan',
            'msg'             => $this->session->getFlashdata('message')
        ];

        return view('App\modules\result\Views\views_result', $data);
    }

    public function fetch()
    {
        $queryBuilder = $this->model_result->getresultTags();

        // Menggunakan library DataTables untuk CI4
        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

        $datatables->addColumn('no', function ($row, $index) {
            return $index + 1;
        });

        $datatables->addColumn('action', function ($row) {
            return '<button data-name="' . $row->nama . '" data-description="' . $row->deskripsi . '" data-id="' . $row->id . '" data-href="' . site_url('result/update/' . $row->id) . '" class="btn btn-primary btn-action mr-1 btn_edit"><i class="fas fa-edit"></i></button>' .
                '<button type="button" data-href="' . site_url("result/destroy/" . $row->id) . '" class="btn btn-danger btn-action btn_delete"><i class="fas fa-trash"></i></button>';
        });

        // Mapping agar fitur pencarian DataTables bekerja pada kolom alias
        $datatables->addColumnAliases([
            'result_tags.name'        => 'nama',
            'result_tags.description' => 'deskripsi'
        ]);

        $datatables->asObject();
        return $datatables->generate();
    }

    public function get_tags()
    {
        $tags = $this->model_result->get_all_tags();

        $formatted_tags = array_map(function ($tag) {
            return [
                'value' => $tag->name,
                'id'    => $tag->id
            ];
        }, $tags);

        return $this->response->setJSON($formatted_tags);
    }

    public function check_name_exists()
    {
        $name   = $this->request->getPost('name');
        $id     = $this->request->getPost('id');
        $exists = $this->model_result->checkNameExists($name, $id);

        return $this->response->setJSON(['exists' => $exists]);
    }

    public function store()
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('deskripsi'),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_result->store($data)) {
            $this->session->setFlashdata('message', ['success', 'Tag hasil pemeriksaan berhasil ditambahkan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag hasil pemeriksaan gagal ditambahkan']);
        }

        return redirect()->to('result');
    }

    public function update($id)
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('deskripsi'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_result->updateTag($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Tag hasil pemeriksaan berhasil diperbarui']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag hasil pemeriksaan gagal diperbarui']);
        }

        return redirect()->to('result');
    }

    public function destroy($tagId)
    {
        if ($this->model_result->destroy($tagId)) {
            $this->session->setFlashdata('message', ['success', 'Tag hasil pemeriksaan berhasil dihapus']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag hasil pemeriksaan gagal dihapus']);
        }

        return redirect()->to('result');
    }
}
