<?php

namespace App\Modules\TagRekamMedis\Controllers;

use App\Controllers\BaseController;
use App\Modules\TagRekamMedis\Models\MMedis;

class MedisController extends BaseController
{
  protected $model_medis;
  protected $session;

  public function __construct()
  {
    $this->model_medis = new MMedis();
    $this->session = \Config\Services::session();

    if (!$this->session->get('role')) {
      redirect()->to(base_url('login'))->send();
      exit();
    }
  }

  public function index()
  {
    $data = [
      'realname' => $this->session->get('realname'),
      'role' => $this->session->get('role'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Tag Riwayat Medis',
      'msg' => $this->session->getFlashdata('message'),
    ];

    return view('App\Modules\TagRekamMedis\Views\index', $data);
  }

  public function fetch()
  {
    $queryBuilder = $this->model_medis->getMedisTags();

    $dataTables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

    $dataTables->addColumnAliases([
      'medhis_tags.name' => 'nama',
      'medhis_tags.description' => 'deskripsi',
    ]);

    $start = (int) $this->request->getPost('start');

    $dataTables->addColumn('no', function ($row) use (&$start) {
      return ++$start;
    });

    $dataTables->addColumn('action', function ($row) {
      return '<button data-name="' .
        $row->nama .
        '" data-description="' .
        $row->deskripsi .
        '" data-id="' .
        $row->id .
        '" data-href="' .
        site_url('tag-rekam-medis/update/' . $row->id) .
        '" class="btn btn-primary btn-action mr-1 btn_edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" data-href="' .
        site_url('tag-rekam-medis/destroy/' . $row->id) .
        '" class="btn btn-danger btn-action btn_delete">
                    <i class="fas fa-trash"></i>
                </button>';
    });

    $dataTables->asObject();
    return $dataTables->generate();
  }

  public function get_tags()
  {
    $tags = $this->model_medis->get_all_tags();

    $formatted = array_map(function ($tag) {
      return [
        'value' => $tag->name,
        'id' => $tag->id,
      ];
    }, $tags);

    return $this->response->setJSON($formatted);
  }

  public function check_name_exists()
  {
    $name = $this->request->getPost('name');
    $id = $this->request->getPost('id');

    $exists = $this->model_medis->checkNameExists($name, $id);

    return $this->response->setJSON(['exists' => $exists]);
  }

  public function store()
  {
    $data = [
      'name' => $this->request->getPost('name'),
      'description' => $this->request->getPost('deskripsi'),
    ];

    if ($this->model_medis->store($data)) {
      $this->session->setFlashdata('message', ['success', 'Tag berhasil ditambahkan']);
    } else {
      $this->session->setFlashdata('message', ['danger', 'Gagal menambahkan tag']);
    }

    return redirect()->to('tag-rekam-medis');
  }

  public function update($id)
  {
    $data = [
      'name' => $this->request->getPost('name'),
      'description' => $this->request->getPost('deskripsi'),
    ];

    if ($this->model_medis->update($id, $data)) {
      $this->session->setFlashdata('message', ['success', 'Berhasil diperbarui']);
    } else {
      $this->session->setFlashdata('message', ['danger', 'Gagal diperbarui']);
    }

    return redirect()->to('tag-rekam-medis');
  }

  public function destroy($id)
  {
    if ($this->model_medis->destroy($id)) {
      $this->session->setFlashdata('message', ['success', 'Berhasil dihapus']);
    } else {
      $this->session->setFlashdata('message', ['danger', 'Gagal dihapus']);
    }

    return redirect()->to('tag-rekam-medis');
  }
}
