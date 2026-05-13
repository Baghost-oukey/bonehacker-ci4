<?php

namespace App\Modules\Karyawan\Controllers;

use App\Controllers\BaseController;
use App\Modules\Karyawan\Models\MKaryawan;
use App\modules\jabatan\Models\Mjabatan;
use App\modules\Region\Models\MRegion;
use CodeIgniter\HTTP\ResponseInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class KaryawanController extends BaseController
{
  protected $session;
  protected $model_karyawan;
  protected $model_jabatan;
  protected $model_region;

  public function __construct()
  {
    $this->session = \Config\Services::session();
    $this->model_karyawan = new MKaryawan();
    $this->model_jabatan = new Mjabatan();
    $this->model_region = new MRegion();
  }

  public function index()
  {
    $role = $this->session->get('role');
    $region_patient = $this->session->get('region_patient');
    $allowed_regions = ($region_patient !== 'all') ? $region_patient : null;

    $data = [
      'realname' => $this->session->get('realname'),
      'role' => $role,
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Manajemen Karyawan',
      'regions' => $this->model_karyawan->get_regions($allowed_regions),
      'jabatan' => $this->model_jabatan->getData(),
      'msg' => $this->session->getFlashdata('message'),
      'error_message' => $this->session->getFlashdata('error_message'),
    ];

    return view('App\Modules\Karyawan\Views\index', $data);
  }

  public function fetch()
  {
    $draw = $this->request->getPost('draw') ?? 1;
    $search_value = $this->request->getPost('search')['value'] ?? '';
    
    $options = [
      'limit' => intval($this->request->getPost('length') ?? 25),
      'offset' => intval($this->request->getPost('start') ?? 0),
      'search' => $search_value,
    ];

    $dataOutput = $this->model_karyawan->getListData($options);
    $totalFiltered = $this->model_karyawan->getTotalData($options);
    $totalData = $totalFiltered; 
    $no = $options['offset'] + 1;

    // Region map for display
    $all_region_ids = [];
    foreach ($dataOutput as $value) {
      if ($value->role !== 'superadmin') {
        $raw = $value->regions_patient;
        $ids = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
        if (is_array($ids)) $all_region_ids = array_merge($all_region_ids, $ids);
      }
    }
    $all_region_ids = array_unique(array_filter($all_region_ids, 'is_numeric'));
    $region_map = [];
    if (!empty($all_region_ids)) {
      $region_names_query = $this->model_karyawan->db->table('regions')->select('id, name')->whereIn('id', $all_region_ids)->get()->getResult();
      foreach ($region_names_query as $r) $region_map[$r->id] = $r->name;
    }

    foreach ($dataOutput as $value) {
      $value->no = $no++;
      $value->realname = esc($value->name);
      $value->username = esc($value->username ?? '-');
      $value->role = esc($value->role ?? '-');

      $is_terapis = $value->personnel_type === 'Therapist';
      $type_label = $is_terapis ? '<span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[9px] font-bold border border-blue-100">Karyawan</span>' : '<span class="px-2 py-0.5 rounded-full bg-slate-50 text-slate-500 text-[9px] font-bold border border-slate-100">Manajemen</span>';
      $value->type_label = $type_label;

      if ($value->role === 'superadmin') {
        $value->region_name = 'Semua Wilayah';
        $regions_patient_ids = [];
      } else {
        $raw = $value->regions_patient;
        $regions_patient_ids = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
        $names = [];
        if (is_array($regions_patient_ids)) {
          foreach ($regions_patient_ids as $rid) if (isset($region_map[$rid])) $names[] = $region_map[$rid];
        }
        $value->region_name = !empty($names) ? implode(', ', $names) : '-';
      }

      $action = '<div class="flex items-center justify-center gap-1.5">';
      if (!empty($value->user_id)) {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white transition-all btn_edit" 
                        data-id="' . $value->user_id . '" 
                        data-realname="' . $value->realname . '" 
                        data-username="' . $value->username . '" 
                        data-role="' . $value->role . '" 
                        data-regions_patient="' . (is_array($regions_patient_ids) ? implode(',', $regions_patient_ids) : '') . '" 
                        data-href="' . base_url('karyawan/update_account/' . $value->user_id) . '" 
                        title="Edit Akun">
                        <i class="fas fa-user-cog text-[10px]"></i>
                    </button>';
      } else {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-teal-600 hover:text-white transition-all btn_create_account" 
                        data-terapis_id="' . $value->terapis_id . '" 
                        data-realname="' . $value->realname . '" 
                        title="Buat Akun">
                        <i class="fas fa-user-plus text-[10px]"></i>
                    </button>';
      }

      if ($is_terapis) {
        $action .= '<a href="' . base_url('karyawan/show/' . $value->id_terapis_table) . '" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Profil Karyawan">
                      <i class="fas fa-id-card text-[10px]"></i>
                    </a>';
      }

      if (!empty($value->user_id)) {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all btn_add_patient" data-userid="' . $value->user_id . '" title="Akses Pasien">
                        <i class="fas fa-hospital-user text-[10px]"></i>
                    </button>';
      }

      if (!empty($value->user_id) && $value->role !== 'superadmin') {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all btn_delete" data-href="' . base_url('karyawan/delete_account/' . $value->user_id) . '" title="Hapus">
                      <i class="fas fa-trash-alt text-[10px]"></i>
                    </button>';
      }
      $action .= '</div>';
      $value->action = $action;
    }

    return $this->response->setJSON([
      "draw" => intval($draw),
      "recordsTotal" => intval($totalFiltered),
      "recordsFiltered" => intval($totalFiltered),
      "data" => $dataOutput,
      "csrfHash" => csrf_hash()
    ]);
  }

  public function store()
  {
    $post = $this->request->getPost();
    
    // Check username
    if ($this->model_karyawan->db->table('users')->where('username', $post['username'])->get()->getRow()) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan', 'csrfHash' => csrf_hash()]);
    }

    $terapis_id = null;
    if ($post['role'] === 'user') {
      $file = $this->request->getFile('foto');
      $foto = null;
      if ($file && $file->isValid() && !$file->hasMoved()) {
        $foto = $file->getRandomName();
        $file->move(FCPATH . 'foto_karyawan', $foto);
      }

      $terapis_data = [
        'terapis_id' => $post['terapis_id'],
        'nama' => $post['realname'],
        'tempat_lahir' => $post['tempat_lahir'] ?? null,
        'tanggal_lahir' => $post['tanggal_lahir'] ?? null,
        'alamat' => $post['alamat'] ?? null,
        'region_id' => $post['region_id'] ?? null,
        'jabatan_id' => !empty($post['jabatan_id']) ? $post['jabatan_id'] : null,
        'rank' => $post['rank'] ?? 'Junior',
        'tgl_mulai_kerja' => !empty($post['tgl_mulai_kerja']) ? $post['tgl_mulai_kerja'] . ' ' . date('H:i:s') : null,
        'foto' => $foto,
        'is_active' => 1
      ];

      $this->model_karyawan->insert($terapis_data);
      $terapis_id = $post['terapis_id'];
    }

    $user_data = [
      'realname' => $post['realname'],
      'username' => $post['username'],
      'password' => password_hash($post['password'], PASSWORD_BCRYPT),
      'role' => $post['role'],
      'terapis_id' => $terapis_id,
      'other_patient' => json_encode([]),
    ];

    if ($post['role'] === 'superadmin') {
      $user_data['regions_patient'] = json_encode([]);
    } else {
      $regions = $post['regions_patient'] ?? [];
      if ($post['role'] === 'user' && !empty($post['region_id'])) $regions = [$post['region_id']];
      $user_data['regions_patient'] = json_encode(array_map('intval', (array)$regions));
    }

    if ($this->model_karyawan->db->table('users')->insert($user_data)) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data', 'csrfHash' => csrf_hash()]);
  }

  public function show($id)
  {
    $karyawan = $this->model_karyawan->getById($id);
    if (!$karyawan) return redirect()->to('karyawan');

    $data = [
      'realname' => $this->session->get('realname'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Detail Karyawan',
      'msg' => $this->session->getFlashdata('message'),
      'role' => $this->session->get('role'),
      'karyawan' => $karyawan,
      'wilayah' => $this->model_region->getData(),
      'jabatan' => $this->model_karyawan->get_jabatan(),
      'connected_user' => $this->model_karyawan->db->table('users')->where('terapis_id', $karyawan->terapis_id)->get()->getRow(),
      'all_users' => [],
    ];

    // QR Code
    $qrContent = base_url('karyawan/public_info/' . $karyawan->terapis_id);
    $writer = new PngWriter();
    $qrCode = QrCode::create($qrContent)->setSize(300)->setMargin(10)->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 255, 255));
    $data['qr_code_base64'] = $writer->write($qrCode)->getDataUri();

    return view('App\Modules\Karyawan\Views\DetailTerapis\index', $data);
  }

  public function update_profile()
  {
    $id = $this->request->getPost('id');
    $karyawan_id = $this->request->getPost('terapis_id'); // Still using terapis_id from DB for now
    $data = [
      'terapis_id' => $karyawan_id,
      'nama' => $this->request->getPost('nama'),
      'tempat_lahir' => $this->request->getPost('tempat_lahir'),
      'tanggal_lahir' => $this->request->getPost('tgl_lahir'),
      'alamat' => $this->request->getPost('alamat') ?: null,
      'region_id' => $this->request->getPost('region_id'),
      'jabatan_id' => !empty($this->request->getPost('jabatan_id')) ? $this->request->getPost('jabatan_id') : null,
      'rank' => $this->request->getPost('rank'),
      'tgl_mulai_kerja' => $this->request->getPost('tgl_kerja'),
      'keterangan' => $this->request->getPost('keterangan'),
      'is_active' => $this->request->getPost('status') === 'on' ? 1 : 0,
      'is_presensi' => $this->request->getPost('is_presensi') === 'on' ? 1 : 0,
    ];

    $file = $this->request->getFile('foto');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'foto_karyawan', $newName);
        $data['foto'] = $newName;
    }

    if ($this->model_karyawan->update($id, $data)) {
        $this->session->setFlashdata('message', ['success', 'Profil berhasil diperbarui']);
    }
    return redirect()->back();
  }

  public function update_account($id)
  {
    $post = $this->request->getPost();
    $data = [
      'realname' => $post['realname'],
      'username' => $post['username'],
      'role' => $post['role'],
    ];

    if (!empty($post['password'])) {
      $data['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
    }

    if ($this->model_karyawan->db->table('users')->where('id', $id)->update($data)) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Akun berhasil diperbarui', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui akun', 'csrfHash' => csrf_hash()]);
  }

  public function delete_account($id)
  {
    if ($this->model_karyawan->db->table('users')->delete(['id' => $id])) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Akun berhasil dihapus', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus akun', 'csrfHash' => csrf_hash()]);
  }

  public function checkUsername()
  {
    $username = $this->request->getPost('username');
    $exists = $this->model_karyawan->db->table('users')->where('username', $username)->countAllResults() > 0;
    return $this->response->setJSON(['exists' => $exists]);
  }

  public function profil_saya()
  {
    $terapis_id = $this->session->get('terapis_id');
    if (!$terapis_id) return redirect()->to(base_url('beranda'));

    $terapis = $this->model_karyawan->detail($terapis_id);
    if (!$terapis) return redirect()->to(base_url('beranda'));

    return $this->show($terapis->id);
  }

  public function active($id)
  {
    $this->model_karyawan->isActive($id, 1);
    return $this->response->setJSON(['status' => 'success']);
  }

  public function nonActive($id)
  {
    $this->model_karyawan->isActive($id, 0);
    return $this->response->setJSON(['status' => 'success']);
  }

  public function destroy($id)
  {
    if ($this->model_karyawan->destroy($id)) {
      $this->session->setFlashdata('message', ['success', 'Data karyawan berhasil dihapus']);
    } else {
      $this->session->setFlashdata('message', ['error', 'Gagal menghapus data karyawan']);
    }
    return redirect()->to('karyawan');
  }

  public function view_patient($user_id)
  {
    $user = $this->model_karyawan->db->table('users')->where('id', $user_id)->get()->getRow();
    if (!$user) return redirect()->to('karyawan');

    // Get region name for title
    $region_name = '-';
    $region_ids = json_decode($user->regions_patient, true) ?: [];
    if (!empty($region_ids)) {
      $r = $this->model_karyawan->db->table('regions')->where('id', $region_ids[0])->get()->getRow();
      if ($r) $region_name = $r->name;
    }

    $data = [
      'realname' => $this->session->get('realname'),
      'title' => 'Akses Pasien - ' . $user->realname,
      'user_id' => $user_id,
      'user_role' => $user->role,
      'region_name' => $region_name,
      'current_segment' => 'karyawan',
    ];

    return view('App\Modules\Karyawan\Views\view_patient', $data);
  }

  public function fetch_patients()
  {
    $user_id = $this->request->getPost('user_id');
    $draw = $this->request->getPost('draw');
    $start = $this->request->getPost('start');
    $length = $this->request->getPost('length');
    $search = $this->request->getPost('search')['value'] ?? '';

    $builder = $this->model_karyawan->get_patients_by_user_region($user_id);
    
    if ($search) {
      $builder->groupStart()->like('p.name', $search)->orLike('p.address', $search)->groupEnd();
    }

    $total = $builder->countAllResults(false);
    $data = $builder->limit($length, $start)->get()->getResult();

    $output = [];
    $no = $start + 1;
    foreach ($data as $row) {
      $output[] = [
        'no' => $no++,
        'nama' => esc($row->nama),
        'gender' => esc($row->gender),
        'age' => esc($row->age),
        'address' => esc($row->address),
        'wilayah' => esc($row->wilayah),
      ];
    }

    return $this->response->setJSON([
      'draw' => intval($draw),
      'recordsTotal' => $total,
      'recordsFiltered' => $total,
      'data' => $output,
      'csrfHash' => csrf_hash()
    ]);
  }

  public function fetch_patients_luar()
  {
    $user_id = $this->request->getPost('user_id');
    $draw = $this->request->getPost('draw');
    $start = $this->request->getPost('start');
    $length = $this->request->getPost('length');
    $search = $this->request->getPost('search')['value'] ?? '';

    $builder = $this->model_karyawan->get_other_patients($user_id);
    
    if ($search) {
      $builder->groupStart()->like('p.name', $search)->orLike('p.address', $search)->groupEnd();
    }

    $total = $builder->countAllResults(false);
    $data = $builder->limit($length, $start)->get()->getResult();

    $output = [];
    $no = $start + 1;
    foreach ($data as $row) {
      $output[] = [
        'no' => $no++,
        'nama' => esc($row->nama),
        'gender' => esc($row->gender),
        'age' => esc($row->age),
        'address' => esc($row->address),
        'wilayah' => esc($row->wilayah),
        'aksi' => '<div class="flex items-center justify-center gap-2">
                        <button class="btn-send-wa w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all" data-patient-id="'.$row->id.'"><i class="fab fa-whatsapp"></i></button>
                        <button class="btn-delete-patient w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all" data-patient-id="'.$row->id.'" data-user-id="'.$user_id.'"><i class="fas fa-trash"></i></button>
                    </div>'
      ];
    }

    return $this->response->setJSON([
      'draw' => intval($draw),
      'recordsTotal' => $total,
      'recordsFiltered' => $total,
      'data' => $output,
      'csrfHash' => csrf_hash()
    ]);
  }

  public function get_outside_patients_select()
  {
    $user_id = $this->request->getPost('user_id');
    $term = $this->request->getPost('searchTerm');
    $data = $this->model_karyawan->search_outside_patients($user_id, $term);
    return $this->response->setJSON($data);
  }

  public function add_outside_patient()
  {
    $user_id = $this->request->getPost('user_id');
    $patient_id = $this->request->getPost('patient_id');
    
    if ($this->model_karyawan->append_patient_to_user($user_id, $patient_id)) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Pasien berhasil ditambahkan', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menambahkan pasien', 'csrfHash' => csrf_hash()]);
  }

  public function delete_outside_patient()
  {
    $user_id = $this->request->getPost('user_id');
    $patient_id = $this->request->getPost('patient_id');

    $user = $this->model_karyawan->db->table('users')->where('id', $user_id)->get()->getRow();
    if (!$user) return $this->response->setJSON(['success' => false]);

    $other_patients = json_decode($user->other_patient, true) ?: [];
    $other_patients = array_values(array_diff($other_patients, [(int)$patient_id]));

    if ($this->model_karyawan->db->table('users')->where('id', $user_id)->update(['other_patient' => json_encode($other_patients)])) {
      return $this->response->setJSON(['success' => true, 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['success' => false, 'csrfHash' => csrf_hash()]);
  }

  public function public_info($id)
  {
    $karyawan = $this->model_karyawan->detail($id);
    $data = [
      'base_url' => base_url(),
      'title' => 'Info Publik Karyawan',
      'karyawan' => $karyawan,
      'jabatan' => $this->model_karyawan->getJabatanById($id),
      'wilayah' => $this->model_karyawan->getRegionById($id),
    ];
    return view('App\Modules\Karyawan\Views\views_info_publik', $data);
  }
}
