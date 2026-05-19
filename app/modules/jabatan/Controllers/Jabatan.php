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
        $start = (int) ($this->request->getPost('start') ?? 0);
        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });

        $datatables->addColumn('action', function ($row) {
            $row = (object) $row;
            return '<button data-name="' . $row->nama_jabatan . '" data-description="' . $row->deskripsi . '" data-id="' . $row->id . '" data-href="' . site_url('jabatan/update/' . $row->id) . '" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600 mr-2 btn_edit"><i class="fas fa-edit text-xs"></i></button>' .
                '<button type="button" data-href="' . site_url("jabatan/destroy/" . $row->id) . '" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 btn_delete"><i class="fas fa-trash text-xs"></i></button>';
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

        if ($this->request->isAJAX()) {
            if ($this->model_jabatan->store($data)) {
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Data jabatan berhasil ditambahkan!',
                    'csrfHash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Gagal menambahkan data jabatan.',
                    'csrfHash' => csrf_hash()
                ]);
            }
        }

        if ($this->model_jabatan->store($data)) {
            return redirect()->to(base_url('jabatan'))->with('success', 'Data jabatan berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Data jabatan gagal ditambahkan');
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

        if ($this->request->isAJAX()) {
            if ($this->model_jabatan->update($id, $data)) {
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Data jabatan berhasil diperbarui!',
                    'csrfHash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Gagal memperbarui data jabatan.',
                    'csrfHash' => csrf_hash()
                ]);
            }
        }

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
                'status'   => 'success',
                'message'  => 'Data jabatan berhasil dihapus!',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => 'Gagal menghapus data jabatan.',
                'csrfHash' => csrf_hash()
            ]);
        }

        return redirect()->to('jabatan');
    }
}
