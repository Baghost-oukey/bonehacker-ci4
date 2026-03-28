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

    public function view_patient($user_id)
    {
        $user = $this->model_users->find($user_id);
        if (!$user) return redirect()->to('users');

        $rawRegions = is_object($user) ? $user->regions_patient : $user['regions_patient'];
        $region_ids = json_decode($rawRegions, true) ?: [];
        $region_names = $this->model_users->get_region_names($region_ids);

        $data = [
            'realname'        => $this->session->get('realname'),
            'role'            => $this->session->get('role'),
            'base_url'        => base_url(),
            'current_segment' => $this->request->getUri()->getSegment(1),
            'title'           => 'Data Pasien',
            'msg'             => $this->session->getFlashdata('message'),
            'user_id'         => $user_id,
            'user_role'   => is_object($user) ? $user->role : $user['role'],
            'region_name' => (!empty($region_names)) ? implode(', ', $region_names) : '-',
            // CI4 style: kita kirim data awal kalau perlu
            'patients_luar'   => $this->model_users->get_other_patients($user_id)
        ];

        return view('App\Modules\users\Views\views_usersLuar', $data);
    }
    public function fetch_patients()
    {
        $user_id = $this->request->getPost('user_id');
        $queryBuilder = $this->model_users->get_patients_by_user_region($user_id);

        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');
        $start = $this->request->getPost('start') ?: 0;

        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });

        // 1. Ambil data mentah dari library
        $output = $datatables->generate();
        $rawData = is_string($output) ? json_decode($output, true) : (array)$output;

        // 2. Transformasi ke Objek Asli (stdClass)
        $finalData = [];
        if (isset($rawData['data']) && is_array($rawData['data'])) {
            foreach ($rawData['data'] as $row) {
                $obj = new \stdClass();
                $obj->no      = (string) ($row[0] ?? '-');
                $obj->nama    = (string) ($row[1] ?? '-');
                $obj->gender  = ($row[2] === 'Man') ? 'Laki-Laki' : 'Perempuan';
                $obj->age     = (string) ($row[3] ?? '-');
                $obj->address = (string) ($row[4] ?? '-');
                $obj->wilayah = (string) ($row[5] ?? '-');

                $finalData[] = $obj;
            }
        }

        return $this->response->setJSON([
            'draw'            => intval($rawData['draw'] ?? 1),
            'recordsTotal'    => intval($rawData['recordsTotal'] ?? 0),
            'recordsFiltered' => intval($rawData['recordsFiltered'] ?? 0),
            'data'            => $finalData, // Data sudah berlabel untuk JS kamu
            'csrfHash'        => csrf_hash()
        ]);
    }

    public function fetch_patients_luar()
    {
        $user_id = $this->request->getPost('user_id');
        $queryBuilder = $this->model_users->get_other_patients($user_id);

        // Jika library Ngekoding-mu sudah terinstall untuk CI4
        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

        $start = $this->request->getPost('start') ?: 0;

        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });

        $datatables->addColumn('gender', function ($row) {
            return ($row->gender === 'Man') ? 'Laki-Laki' : 'Perempuan';
        });

        $datatables->addColumn('aksi', function ($row) use ($user_id) {
            return '
                <button class="btn btn-danger btn-sm btn-delete-patient mr-1" data-patient-id="' . $row->id . '" data-user-id="' . $user_id . '"><i class="fas fa-trash"></i></button>
                <button class="btn btn-success btn-sm btn-send-wa" data-patient-id="' . $row->id . '"><i class="fab fa-whatsapp"></i></button>
            ';
        });

        $output = $datatables->generate();
        $data = is_string($output) ? json_decode($output, true) : (array)$output;
        $data['csrfHash'] = csrf_hash();

        return $this->response->setJSON($data);
    }

    public function add_outside_patient()
    {
        $user_id = $this->request->getPost('user_id');
        $patient_id = $this->request->getPost('patient_id');

        $user = $this->model_users->find($user_id);
        $user_regions = json_decode($user->regions_patient, true) ?: [];

        // Ambil region_id pasien (bisa via model patients atau db query langsung)
        $db = \Config\Database::connect();
        $patient = $db->table('patients')->select('region_id')->where('id', $patient_id)->get()->getRow();

        if (!in_array($patient->region_id, $user_regions)) {
            if ($this->model_users->append_patient_to_user($user_id, $patient_id)) {
                $this->session->setFlashdata('message', ['success', 'Pasien luar berhasil ditambahkan']);
            }
        } else {
            $this->session->setFlashdata('message', ['danger', 'Pasien sudah masuk wilayah user']);
        }

        return redirect()->to('users/view_patient/' . $user_id);
    }

    // --- FITUR EDIT AKUN SENDIRI (AJAX) ---

    public function edit_account()
    {
        $userId = $this->session->get('userId');
        $user = $this->model_users->find($userId);
        return $this->response->setJSON($user);
    }

    public function update_account()
    {
        $id = $this->session->get('userId');
        if (!$id) return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi berakhir']);

        $post = $this->request->getPost();

        // Cek duplikasi username (kecuali user sendiri)
        $existing = $this->model_users->where('username', $post['username'])->where('id !=', $id)->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan']);
        }

        $update_data = [
            'realname' => $post['realname'],
            'username' => $post['username']
        ];

        if (!empty($post['password'])) {
            $update_data['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        }

        if ($this->model_users->update($id, $update_data)) {
            // Update session realname jika berubah
            $this->session->set('realname', $post['realname']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Akun diperbarui', 'realname' => $post['realname']]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal update']);
    }
}
