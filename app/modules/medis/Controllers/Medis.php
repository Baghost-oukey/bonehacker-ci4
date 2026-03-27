<?php

namespace App\modules\medis\Controllers;

use App\Controllers\BaseController;
use App\modules\medis\Models\MMedis;
use CodeIgniter\HTTP\ResponseInterface;

class Medis extends BaseController
{

    protected $model_medis;
    protected $session;
    public function __construct()
    {
        $this->model_medis = new MMedis();
        $this->session = \Config\Services::session();
        // Proteksi Akses (Opsional, sesuaikan dengan kebijakan perusahaan)
        if (!$this->session->get('role')) {
            return redirect()->to(base_url('login'))->send();
        }
    }
    public function index()
    {
        //
        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Tag Riwayat Medis',
            'msg'             => $this->session->getFlashdata('message')
        ];

        // Karena sudah tidak menggunakan Blade, panggil view PHP biasa
        return view('App\modules\medis\Views\views_medis', $data);
    }

    public function fetch()
    {
        $queryBuilder = $this->model_medis->getmedhisTags();

        $dataTables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

        // Mapping alias untuk pencarian dan pengurutan
        $dataTables->addColumnAliases([
            'medhis_tags.name'        => 'nama',
            'medhis_tags.description' => 'deskripsi'
        ]);

        $start = (int)$this->request->getPost('start');

        $dataTables->addColumn('no', function ($row, $index = null) use (&$start) {
            return ++$start;
        });

        $dataTables->addColumn('action', function ($row, $index = null) {
            return '<button data-name="' . $row->nama . '" data-description="' . $row->deskripsi . '" data-id="' . $row->id . '" data-href="' . site_url('medis/update/' . $row->id) . '" class="btn btn-primary btn-action mr-1 btn_edit"><i class="fas fa-edit"></i></button>' .
                '<button type="button" data-href="' . site_url("medis/destroy/" . $row->id) . '" class="btn btn-danger btn-action btn_delete"><i class="fas fa-trash"></i></button>';
        });



        $dataTables->asObject();
        return $dataTables->generate();
    }

    public function get_tags()
    {
        $tags = $this->model_medis->get_all_tags();

        $formatted_tags = array_map(function ($tag) {
            return [
                'value' => $tag->name, // Menggunakan objek karena returnType di model adalah object
                'id'    => $tag->id
            ];
        }, $tags);

        return $this->response->setJSON($formatted_tags);
    }

    public function check_name_exists()
    {
        $name   = $this->request->getPost('name');
        $id     = $this->request->getPost('id');
        $exists = $this->model_medis->checkNameExists($name, $id);

        return $this->response->setJSON(['exists' => $exists]);
    }

    // data baru
    public function store()
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('deskripsi'),
            // 'created_at'  => date('Y-m-d H:i:s'),
            // 'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_medis->store($data)) {
            $this->session->setFlashdata('message', ['success', 'Tag riwayat medis berhasil ditambahkan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag riwayat medis gagal ditambahkan']);
        }

        return redirect()->to('medis');
    }

    // data lama
    public function update($id)
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('deskripsi'),

            // Kalo mau pakai keterangan di buat w
            // 'created_at'  => date('Y-m-d H:i:s'),
            // 'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($this->model_medis->update($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Tag riwayat medis berhasil diperbarui']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag riwayat medis gagal diperbarui']);
        }

        return redirect()->to('medis');
    }

    public function destroy($id)
    {
        if ($this->model_medis->destroy($id)) {
            $this->session->setFlashdata('message', ['success', 'Tag riwayat medis berhasil dihapus']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Tag riwayat medis gagal dihapus']);
        }

        return redirect()->to('medis');
    }
}
