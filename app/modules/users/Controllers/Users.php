<?php

namespace App\modules\users\Controllers;

use App\Controllers\BaseController;
use App\modules\users\Models\MUsers;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends BaseController
{
    protected $session;
    protected $model_users;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->model_users = new MUsers();
    }
    public function index()
    {
        //
        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Users',
            'regions'         => $this->model_users->get_regions(),
            'msg'             => $this->session->getFlashdata('message'),
            'error_message'   => $this->session->getFlashdata('error_message'),
        ];

        // Ganti path view sesuai struktur folder Anda
        return view('App\Modules\users\Views\views_users', $data);
    }

    public function fetch()
    {
        $draw         = $this->request->getPost('draw') ?? 1;
        $role         = $this->request->getPost('role');
        $search_value = $this->request->getPost('search_value') ?? '';
        $order        = $this->request->getPost('order');
        $columns      = $this->request->getPost('columns');

        $options = [
            'order'  => (!empty($order) && !empty($columns)) ? $columns[$order[0]['column']]['data'] : 'realname',
            'mode'   => (!empty($order)) ? $order[0]['dir'] : 'asc',
            'offset' => $this->request->getPost('start') ?? 0,
            'limit'  => $this->request->getPost('length') ?? 10,
            'where_like' => []
        ];

        if (!empty($search_value)) {
            $options['where_like'] = [
                "u.realname LIKE '%$search_value%'",
                "u.username LIKE '%$search_value%'"
                // Kalau mau role gak ikut terfilter 'pe', u.role jangan dimasukin di sini
            ];
        }

        $dataOutput    = $this->model_users->getListData($options);
        $totalFiltered = $this->model_users->getTotalData($options);
        $totalData     = $this->model_users->countAll();
        $no            = $options['offset'] + 1;

        foreach ($dataOutput as $value) {
            $value->no       = $no++;
            $value->realname = esc($value->realname);
            $value->username = esc($value->username);
            $value->role     = esc($value->role);

            if ($value->role === 'superadmin') {
                $value->region_name = 'Semua Wilayah';
                $regions_patient_ids = [];
            } else {
                $regions_patient_ids = json_decode($value->regions_patient, true) ?: [];
                $regions_patient_names = $this->model_users->get_region_names($regions_patient_ids);
                $value->region_name = (!empty($regions_patient_names)) ? implode(', ', $regions_patient_names) : '-';
            }

            $value->action = '
                <button data-realname="' . $value->realname . '" data-username="' . $value->username . '" data-role="' . $value->role . '" 
                    data-regions_patient="' . esc(json_encode($regions_patient_ids)) . '" 
                    data-href="' . base_url('users/update/' . $value->id) . '" 
                    class="btn btn-primary btn-sm btn_edit"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-info btn-sm btn_add_patient" data-userid="' . $value->id . '"><i class="fas fa-user-plus"></i></button>
                <button type="button" data-href="' . base_url("users/destroy/" . $value->id) . '" class="btn btn-danger btn-sm btn_delete"><i class="fas fa-trash"></i></button>';
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $dataOutput,
            "csrfHash"        => csrf_hash()
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();

        // Cek duplikasi username
        if ($this->model_users->where('username', $post['username'])->first()) {
            $this->session->setFlashdata('error_message', 'Username sudah digunakan');
            return redirect()->to('users');
        }

        $data = [
            'realname' => $post['realname'],
            'username' => $post['username'],
            'role'     => $post['role'],
            'password' => password_hash($post['password'], PASSWORD_BCRYPT),
            'other_patient' => json_encode([])
        ];

        if ($post['role'] === 'superadmin') {
            $data['regions_patient'] = json_encode([]);
        } else {
            $regions = $this->request->getPost('regions_patient') ?: [];
            $data['regions_patient'] = json_encode(array_map('intval', $regions));
        }

        if ($this->model_users->insert($data)) {
            $this->session->setFlashdata('message', ['success', 'Data berhasil disimpan']);
        } else {
            $this->session->setFlashdata('message', ['danger', 'Gagal menyimpan data']);
        }

        return redirect()->to('users');
    }

    public function update($id)
    {
        $post = $this->request->getPost();
        if ($this->model_users->username_exists_edit($post['username'], $id)) {
            $this->session->setFlashdata('error_message', 'Username already taken');
            return redirect()->to('users');
        }

        $data = [
            'realname' => $post['realname'],
            'username' => $post['username'],
            'role'     => $post['role']
        ];

        if (!empty($post['password'])) {
            $data['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        }

        $existing = $this->model_users->find($id);

        if ($post['role'] === 'superadmin') {
            $data['regions_patient'] = json_encode([]);
            $data['other_patient'] = json_encode([]);
        } else {
            $regions = $this->request->getPost('regions_patient') ?: [];
            $new_regions = array_map('intval', $regions);
            $data['regions_patient'] = json_encode($new_regions);

            // Reset other_patient jika region berubah (sesuai logic CI3 Anda)
            if (json_encode($new_regions) !== $existing->regions_patient) {
                $data['other_patient'] = json_encode([]);
            }
        }

        if ($this->model_users->update($id, $data)) {
            $this->session->setFlashdata('message', ['success', 'Data diperbarui']);
        }

        return redirect()->to('users');
    }

    public function destroy($id)
    {
        if ($this->model_users->delete($id)) {
            $this->session->setFlashdata('message', ['success', 'Data dihapus']);
        }
        return redirect()->to('users');
    }
}
