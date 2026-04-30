<?php

namespace App\Modules\TagPemeriksaan\Controllers;

use App\Controllers\BaseController;
use App\Modules\TagPemeriksaan\Models\MTagPemeriksaan;
use CodeIgniter\HTTP\ResponseInterface;

class TagPemeriksaanController extends BaseController
{
  protected $model_result;
  protected $session;

  public function __construct()
  {
    $this->model_result = new MTagPemeriksaan();
    $this->session = \Config\Services::session();
  }


  public function index()
  {
    $data = [
      'realname' => $this->session->get('realname'),
      'role' => $this->session->get('role'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Tag Hasil Pemeriksaan',
      'msg' => $this->session->getFlashdata('message'),
    ];
    return view('App\Modules\TagPemeriksaan\Views\index', $data);
  }


  public function fetch()
  {
    $queryBuilder = $this->model_result->getresultTags();
    $search = $this->request->getPost('search');
    $searchValue = isset($search['value']) ? $search['value'] : '';

    if (!empty($searchValue)) {
      $queryBuilder->groupStart()
        ->like('name', $searchValue)
        ->orLike('description', $searchValue)
        ->groupEnd();
    }
    $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');
    $start = (int) $this->request->getPost('start');
    $datatables->addColumn('no', function ($row, $index = null) use (&$start) {
      return ++$start;
    });
    $datatables->addColumn('action', function ($row, $index = null) {
      return '
    <div class="flex items-center justify-center gap-2">
      <button type="button" 
        data-id="' . $row->id . '"
        data-name="' . htmlspecialchars($row->nama, ENT_QUOTES, 'UTF-8') . '" 
        data-description="' . htmlspecialchars($row->deskripsi, ENT_QUOTES, 'UTF-8') . '" 
        data-href="' . site_url('tag-pemeriksaan/update/' . $row->id) . '" 
        title="Edit Data" 
        class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600 btn_edit">
        <i class="fas fa-edit text-xs transition-transform group-hover:scale-110"></i>
      </button>

      <button type="button" 
        data-href="' . site_url('tag-pemeriksaan/destroy/' . $row->id) . '" 
        title="Hapus Data" 
        class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 btn_delete">
        <i class="fas fa-trash text-xs transition-transform group-hover:scale-110"></i>
      </button>
   </div>';
    });
    $datatables->addColumnAliases([
      'result_tags.name' => 'nama',
      'result_tags.description' => 'deskripsi',
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
        'id' => $tag->id,
      ];
    }, $tags);
    return $this->response->setJSON($formatted_tags);
  }


  public function check_name_exists()
  {
    $name = $this->request->getPost('name');
    $id = $this->request->getPost('id');
    $exists = $this->model_result->checkNameExists($name, $id);

    return $this->response->setJSON(['exists' => $exists]);
  }


  public function store()
  {
    $data = [
      'name' => $this->request->getPost('name'),
      'description' => $this->request->getPost('deskripsi'),
    ];
    if ($this->model_result->store($data)) {
      return $this->response->setJSON([
        'status' => true,
        'message'   => 'Tag Hasil Pemeriksaan berhasil ditambahkan',
        'csrf_hash' => csrf_hash(),
      ]);
    } else {
      return $this->response->setJSON([
        'status' => false,
        'message'   => 'Tag Hasil Pemeriksaan gagal ditambahkan',
        'csrf_hash' => csrf_hash(),
      ]);
    }
  }


  public function update($id)
  {
    $data = [
      'name' => $this->request->getPost('name'),
      'description' => $this->request->getPost('deskripsi'),
    ];
    if ($this->model_result->updateTag($id, $data)) {
      return $this->response->setJSON([
        'status' => true,
        'message'   => 'Tag Hasil Pemeriksaan berhasil diperbarui',
        'csrf_hash' => csrf_hash(),
      ]);
    } else {
      return $this->response->setJSON([
        'status' => false,
        'message'   => 'Tag Hasil Pemeriksaan gagal diperbarui',
        'csrf_hash' => csrf_hash(),
      ]);
    }
  }


  public function destroy($tagId)
  {
    if ($this->model_result->destroy($tagId)) {
      return $this->response->setJSON([
        'status' => true,
        'message'   => 'Tag Hasil Pemeriksaan berhasil dihapus',
        'csrf_hash' => csrf_hash(),
      ]);
    } else {
      return $this->response->setJSON([
        'status' => false,
        'message'   => 'Tag Hasil Pemeriksaan gagal dihapus',
        'csrf_hash' => csrf_hash(),
      ]);
    }
  }
}
