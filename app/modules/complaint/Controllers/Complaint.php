<?php

namespace App\modules\complaint\Controllers;

use App\Controllers\BaseController;
use App\Models\MComplaint;
use App\modules\complaint\Models\MComplaint as ModelsMComplaint;
use CodeIgniter\HTTP\ResponseInterface;

class Complaint extends BaseController
{
    protected $model_complaint;
    protected $session;

    public function __construct()
    {
        $this->model_complaint = new ModelsMComplaint();
        $this->session = \Config\Services::session();

        if ($this->session->get('role') !== 'superadmin') {
            $this->session->setFlashdata('error', 'You do not have access to this page');
            return redirect()->to(base_url())->send();
        }
    }
    public function index()
    {
        //
        $data = [
            'realname'        => $this->session->get('realname'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Pengaturan Tag Keluhan',
            'msg'             => $this->session->getFlashdata('message'),
            'role'            => $this->session->get('role')
        ];

        return view('App\modules\complaint\Views\views_complaint', $data);
    }

    public function fetch()
    {
        $queryBuilder = $this->model_complaint->getComplaintTags();
        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');
        $datatables->addColumn('no', function ($row, $index) {
            return $index + 1;
        });
        $datatables->addColumn('action', function ($row) {
            return '<button data-name="' . $row->nama . '" data-description="' . $row->deskripsi . '" data-id="' . $row->id . '" data-href="' . site_url('complaint/update/' . $row->id) . '" class="btn btn-primary btn-action mr-1 btn_edit"><i class="fas fa-edit"></i></button>' .
                '<button type="button" data-href="' . site_url("complaint/destroy/" . $row->id) . '" class="btn btn-danger btn-action btn_delete"><i class="fas fa-trash"></i></button>';
        });
        $datatables->asObject();
        return $datatables->generate();
    }

    public function check_name_exists()
    {
        $name = $this->request->getPost('name');
        $id   = $this->request->getPost('id');
        $exists = $this->model_complaint->checkNameExists($name, $id);

        return $this->response->setJSON(['exists' => $exists]);
    }

    public function store()
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_complaint->store($data)) {
            $this->session->setFlashdata('message', ['success', 'Tag keluhan berhasil ditambahkan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag keluhan gagal ditambahkan']);
        }

        return redirect()->to('complaint');
    }

    public function update($id)
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_complaint->update($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Tag keluhan berhasil diperbarui']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag keluhan gagal diperbarui']);
        }

        return redirect()->to('complaint');
    }

    public function destroy($id)
    {
        if ($this->model_complaint->destroy($id)) {
            $this->session->setFlashdata('message', ['success', 'Tag keluhan berhasil dihapus']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag keluhan gagal dihapus']);
        }
        return redirect()->to('complaint');
    }
}
