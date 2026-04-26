<?php

namespace App\Modules\TagComplaint\Controllers;

use App\Controllers\BaseController;
use App\Modules\TagComplaint\Models\MComplaint;
use CodeIgniter\HTTP\ResponseInterface;

class TagComplaintController extends BaseController
{
  protected $model_complaint;
  protected $session;

  public function __construct()
  {
    $this->model_complaint = new MComplaint();
    $this->session = \Config\Services::session();

    if ($this->session->get('role') !== 'superadmin') {
      $this->session->setFlashdata('error', 'You do not have access to this page');
    }
  }
  public function index()
  {
    //
    $data = [
      'realname' => $this->session->get('realname'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Pengaturan Tag Keluhan',
      'msg' => $this->session->getFlashdata('message'),
      'role' => $this->session->get('role'),
    ];

    return view('App\Modules\TagComplaint\Views\index', $data);
  }

  public function fetch()
  {
    $queryBuilder = $this->model_complaint->getComplaintTags();
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
        data-href="' . site_url('TagComplaint/update/' . $row->id) . '" 
        title="Edit Data" 
        class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600 btn_edit">
        
        <i class="fas fa-edit text-xs transition-transform group-hover:scale-110"></i>
      </button>

      <button type="button" 
        data-href="' . site_url('TagComplaint/destroy/' . $row->id) . '" 
        title="Hapus Data" 
        class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 btn_delete">
        
        <i class="fas fa-trash text-xs transition-transform group-hover:scale-110"></i>
      </button>

    </div>';
    });
    $datatables->asObject();
    return $datatables->generate();
  }

  public function get_tags()
  {
    $tags = $this->model_complaint->get_all_tags();
    $formatted_tags = array_map(function ($tag) {
      if (is_object($tag)) {
        return [
          'value' => $tag->name,
          'id' => $tag->id,
        ];
      } else {
        return [
          'value' => $tag['name'],
          'id' => $tag['id'],
        ];
      }
    }, $tags);
    return $this->response->setJSON($formatted_tags);
  }

  public function check_name_exists()
  {
    $name = $this->request->getPost('name');
    $id = $this->request->getPost('id');
    $exists = $this->model_complaint->checkNameExists($name, $id);

    return $this->response->setJSON(['exists' => $exists, 'csrf_hash' => csrf_hash()]);
  }

  public function store()
  {
    $data = [
      'name' => $this->request->getPost('name'),
      'description' => $this->request->getPost('description'),
      // 'created_at'  => date('Y-m-d H:i:s'),
      // 'updated_at'  => date('Y-m-d H:i:s')
    ];

    if ($this->model_complaint->store($data)) {
      return $this->response->setJSON([
        'status' => true,
        // 'message'   => 'Tag keluhan berhasil ditambahkan',
        'csrf_hash' => csrf_hash(),
      ]);
    } else {
      return $this->response->setJSON([
        'status' => true,
        // 'message'   => 'Tag keluhan berhasil ditambahkan',
        'csrf_hash' => csrf_hash(),
      ]);
    }

    return redirect()->to('complaint');
  }

  public function update($id)
  {
    $data = [
      'name' => $this->request->getPost('name'),
      'description' => $this->request->getPost('description'),
      // Kalo mai di tambahkan keterangan waktu
      // 'created_at'  => date('Y-m-d H:i:s'),
      // 'updated_at'  => date('Y-m-d H:i:s')
    ];

    if ($this->model_complaint->update($id, $data)) {
      $this->session->setFlashdata('message', ['success', 'Tag keluhan berhasil diperbarui']);
    } else {
      $this->session->setFlashdata('message', ['danger', 'Tag keluhan gagal diperbarui']);
    }

    return redirect()->to('TagComplaint');
  }

  public function destroy($id)
  {
    if ($this->model_complaint->destroy($id)) {
      $this->session->setFlashdata('message', ['success', 'Tag keluhan berhasil dihapus']);
    } else {
      $this->session->setFlashdata('message', ['danger', 'Tag keluhan gagal dihapus']);
    }
    return redirect()->to('TagComplaint');
  }
}
