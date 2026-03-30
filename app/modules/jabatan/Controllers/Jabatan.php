<?php

namespace App\modules\jabatan\Controllers;

use App\Controllers\BaseController;
use App\modules\jabatan\Models\Mjabatan;
use CodeIgniter\HTTP\ResponseInterface;

class Jabatan extends BaseController
{

    protected $model_jabatan;
    protected $session;

    public function __construct()
    {
        $this->model_jabatan = new Mjabatan();
        $this->session = \Config\Services::session();

        if ($this->session->get('role') !== 'superadmin') {
            $this->session->setFlashdata('error', 'You do not have access to this page');
            return redirect()->to(base_url())->send();
        }
    }

    public function index()
    {
        $data = [
            'realname'        => $this->session->get('realname'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Pengaturan Jabatan',
            'msg'             => $this->session->getFlashdata('message'),
            'role'            => $this->session->get('role')
        ];

        return view('App\modules\jabatan\Views\views_jabatan', $data);
    }

    public function fetch()
    {
        $queryBuilder = $this->model_jabatan->getJabatan();
        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');
        $datatables->addColumn('no', function ($row) {
            static $no = 0; 
            return ++$no;
        });

        $datatables->addColumn('action', function ($row) {
            $row = (object) $row;
            return '<button data-name="' . $row->nama_jabatan . '" data-description="' . $row->deskripsi . '" data-id="' . $row->id . '" data-href="' . site_url('jabatan/update/' . $row->id) . '" class="btn btn-primary btn-action mr-1 btn_edit"><i class="fas fa-edit"></i></button>' .
                '<button type="button" data-href="' . site_url("jabatan/destroy/" . $row->id) . '" class="btn btn-danger btn-action btn_delete"><i class="fas fa-trash"></i></button>';
        });

        $datatables->asObject();
        $output = $datatables->generate(false);
        $output['csrfHash'] = csrf_hash();
        return $this->response->setJSON($output);
    }

    public function check_name_exists()
    {
        $name = $this->request->getPost('name');
        $id   = $this->request->getPost('id');
        $exists = $this->model_jabatan->checkNameExists($name, $id);

        return $this->response->setJSON(['exists' => $exists]);
    }

    public function store()
    {
        $data = [
            'nama_jabatan' => $this->request->getPost('name'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->model_jabatan->store($data)) {
            $this->session->setFlashdata('message', ['success', 'Data jabatan berhasil ditambahkan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Data jabatan gagal ditambahkan']);
        }

        return redirect()->to('jabatan');
    }

    public function update($id)
    {
        $data = [
            'nama_jabatan' => $this->request->getPost('name'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->model_jabatan->update($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Data jabatan berhasil diperbarui']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Data jabatan gagal diperbarui']);
        }

        return redirect()->to('jabatan');
    }

   public function destroy($id)
{
    if ($this->model_jabatan->delete($id)) {
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data jabatan berhasil dihapus!'
        ]);
    } else {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal menghapus data jabatan.'
        ]);
    }

    return redirect()->to('jabatan');
}
}
