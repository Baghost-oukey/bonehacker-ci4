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

            if ($value->role === 'superadmin'  || $value->role === 'owner') {
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

        if ($post['role'] === 'superadmin' || $post['role'] === 'owner') {
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

        if ($post['role'] === 'superadmin' || $post['role'] === 'owner' ) {
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
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized access'])->setStatusCode(403);
        }
        $queryBuilder = $this->model_users->get_patients_by_user_region($user_id);
        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');
        $start = $this->request->getPost('start', FILTER_SANITIZE_NUMBER_INT) ?: 0;

        // $datatables->only(['name', 'address']);

        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });

        $datatables->addColumn('nama', function ($row) {
            return esc($row->nama);
        });

        $datatables->addColumn('gender', function ($row) {
            return ($row->gender === 'Man') ? 'Laki-Laki' : 'Perempuan';
        });

        $datatables->addColumn('address', function ($row) {
            return esc($row->address ?? '-');
        });

        $datatables->asObject();
        $output = $datatables->generate();

        $rawData = is_string($output) ? json_decode($output, true) : (array)$output;

        return $this->response->setJSON(array_merge($rawData, [
            'csrfHash' => csrf_hash()
        ]));
    }

    public function fetch_patients_luar()
    {
        $user_id = $this->request->getPost('user_id');
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'invalid User Id'])->setStatusCode(403);
        }

        $queryBuilder = $this->model_users->get_other_patients($user_id);
        $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

        $start = $this->request->getPost('start', FILTER_SANITIZE_NUMBER_INT) ?: 0;

        $datatables->addColumn('no', function ($row) use (&$start) {
            return ++$start;
        });

        $datatables->addColumn('nama', function ($row) {
            return esc($row->nama); // Proteksi XSS
        });

        $datatables->addColumn('gender_label', function ($row) {
            return ($row->gender === 'Man') ? 'Laki-Laki' : 'Perempuan';
        });

        $datatables->addColumn('address', function ($row) {
            return esc($row->address ?? '-'); // Proteksi XSS
        });

        $datatables->addColumn('aksi', function ($row) use ($user_id) {
            return '
            <button class="btn btn-danger btn-sm btn-delete-patient mr-1" data-patient-id="' . (int)$row->id . '" data-user-id="' . (int)$user_id . '"><i class="fas fa-trash"></i></button>
            <button class="btn btn-success btn-sm btn-send-wa" data-patient-id="' . (int)$row->id . '"><i class="fab fa-whatsapp"></i></button>
        ';
        });

        $datatables->asObject();
        $output = $datatables->generate();

        $rawData = is_string($output) ? json_decode($output, true) : (array)$output;
        $response = is_array($rawData) ? $rawData : [];

        return $this->response->setJSON(array_merge($response, [
            'csrfHash' => csrf_hash()
        ]));
    }


    public function add_outside_patient()
    {
        $user_id = $this->request->getPost('user_id');
        $patient_id = $this->request->getPost('patient_id');

        $user = $this->model_users->find($user_id);
        $user_regions = json_decode($user->regions_patient, true) ?: [];
        $db = \Config\Database::connect();
        $patient = $db->table('patients')->select('region_id')->where('id', $patient_id)->get()->getRow();

        if (!in_array($patient->region_id, $user_regions)) {
            if ($this->model_users->append_patient_to_user($user_id, $patient_id)) {
                // $this->session->setFlashdata('message', ['success', 'Pasien luar berhasil ditambahkan']);
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Pasien luar berhasil ditambahkan',
                    'csrfHash' => csrf_hash()
                ]);
            }
        } else {
            // $this->session->setFlashdata('message', ['danger', 'Pasien sudah masuk wilayah user']);
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => 'Pasien sudah masuk wilayah user atau terjadi kesalahan',
                'csrfHash' => csrf_hash()
            ]);
        }

        return redirect()->to('users/view_patient/' . $user_id);
    }

    public function get_outside_patients_select()
    {
        $user_id = $this->request->getPost('user_id');
        $search_term = $this->request->getPost('searchTerm');

        $patients = $this->model_users->search_outside_patients($user_id, $search_term, 20);

        $data = [];
        foreach ($patients as $patient) {
            $gender = ($patient->gender === 'Man') ? 'Laki-Laki' : 'Perempuan';
            $data[] = [
                'id'      => $patient->id,
                'text'    => esc($patient->nama . ' - ' . $gender . ' - ' . $patient->age . ' - ' . $patient->wilayah),
                'nama'    => esc($patient->nama),
                'gender'  => $gender,
                'age'     => $patient->age,
                'address' => esc($patient->address),
                'wilayah' => esc($patient->wilayah)
            ];
        }

        return $this->response->setJSON($data);
    }

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
            $this->session->set('realname', $post['realname']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Akun diperbarui', 'realname' => $post['realname']]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal update']);
    }

    public function delete_outside_patient()
    {
        $patient_id = $this->request->getPost('patient_id');
        $user_id = $this->request->getPost('user_id');
        $user = $this->model_users->find($user_id);
        if (!$user) return $this->response->setJSON(['success' => false]);
        $other_patients = json_decode($user->other_patient, true) ?: [];
        if (($key = array_search($patient_id, $other_patients)) !== false) {
            unset($other_patients[$key]);
            $other_patients = array_values($other_patients);
            $updated = $this->model_users->update($user_id, [
                'other_patient' => json_encode($other_patients)
            ]);

            if ($updated) {
                session()->setFlashdata('message', ['success', 'Data pasien luar berhasil dihapus']);
            }
            return $this->response->setJSON(['success' => (bool)$updated]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan']);
    }

    public function check_username_exists()
    {
        $username = $this->request->getPost('username');
        $exists = $this->model_users->where('username', $username)->countAllResults() > 0;
        return $this->response->setJSON(['exists' => $exists, 'csrfHash' => csrf_hash()]);
    }

    public function check_username_exists_edit()
    {
        $username = $this->request->getPost('username');
        $user_id = $this->request->getPost('user_id');
        $exists = $this->model_users->where('username', $username)
            ->where('id !=', $user_id)
            ->countAllResults() > 0;
        return $this->response->setJSON(['exists' => $exists]);
    }

    public function send_notif_patients($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Forbidden']);
        }

        $db = \Config\Database::connect();
        $patient = $db->table('patients')->where('id', $id)->get()->getRow();

        if (!$patient) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pasien tidak ditemukan']);
        }
        $is_sent = true;

        return $this->response->setJSON([
            'status'   => $is_sent ? 'success' : 'error',
            'message'  => $is_sent ? 'Notifikasi berhasil dikirim ke ' . esc($patient->nama) : 'Gagal mengirim.',
            'csrfHash' => csrf_hash()
        ]);
    }
}
