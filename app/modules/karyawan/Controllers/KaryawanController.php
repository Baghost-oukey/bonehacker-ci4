<?php

namespace App\modules\karyawan\Controllers;

use App\Controllers\BaseController;
use App\modules\karyawan\Models\MKaryawan;
use App\modules\jabatan\Models\Mjabatan;
use App\modules\region\Models\MRegion;
use App\modules\rank_terapis\Models\MRankTerapis;
use CodeIgniter\Database\Exceptions\DatabaseException;
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
  protected $model_rank;
  protected $model_region;

  public function __construct()
  {
    $this->session = \Config\Services::session();
    $this->model_karyawan = new MKaryawan();
    $this->model_jabatan = new Mjabatan();
    $this->model_rank = new MRankTerapis();
    $this->model_region = new MRegion();
  }

  private function getRankOptions(): array
  {
    try {
      $ranks = $this->model_rank->getData(true);
      if (!empty($ranks)) {
        return $ranks;
      }
    } catch (DatabaseException $e) {
      // Keep personnel forms usable before the rank master migration is run.
    }

    return array_map(static fn($rank) => (object) ['name' => $rank], ['SS', 'S', 'A', 'B', 'C']);
  }

  public function index()
  {
    $role = $this->session->get('role');

    // Terapis tidak boleh akses halaman list karyawan
    if ($role === 'user' && !empty($this->session->get('terapis_id'))) {
      return redirect()->to('beranda')->with('message', ['error', 'Anda tidak memiliki akses ke halaman ini']);
    }

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
      'rank_options' => $this->getRankOptions(),
      'msg' => $this->session->getFlashdata('message'),
      'error_message' => $this->session->getFlashdata('error_message'),
    ];

    return view('App\modules\karyawan\Views\index', $data);
  }

  public function fetch()
  {
    if (!$this->request->isAJAX()) {
      return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
    }

    $draw = $this->request->getPost('draw') ?? 1;
    $search_value = $this->request->getPost('search')['value'] ?? '';

    $region_patient = $this->session->get('region_patient');
    $regionFilter = ($region_patient !== 'all' && !empty($region_patient)) ? $region_patient : null;

    $options = [
      'limit' => intval($this->request->getPost('length') ?? 25),
      'offset' => intval($this->request->getPost('start') ?? 0),
      'search' => $search_value,
      'region_filter' => $regionFilter,
    ];

    $dataOutput = $this->model_karyawan->getListData($options);
    $totalRecords = $this->model_karyawan->getTotalData();
    $totalFiltered = !empty($search_value) ? $this->model_karyawan->getTotalFiltered($search_value) : $totalRecords;

    $no = $options['offset'] + 1;

    // Region map — only needed for admin/owner users with regions_patient JSON
    // Terapis & superadmin already have region names from SQL JOINs
    $region_map = [];
    $all_region_ids = [];
    foreach ($dataOutput as $value) {
      $role = strtolower($value->role ?? '');
      if ($role !== 'superadmin' && $value->personnel_type !== 'Therapist') {
        $raw = $value->regions_patient;
        $ids = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
        if (is_array($ids)) $all_region_ids = array_merge($all_region_ids, $ids);
      }
    }
    $all_region_ids = array_unique(array_filter($all_region_ids, 'is_numeric'));
    if (!empty($all_region_ids)) {
      $rows = $this->model_karyawan->db->table('regions')->select('id, name')->whereIn('id', $all_region_ids)->get()->getResult();
      foreach ($rows as $r) $region_map[$r->id] = $r->name;
    }

    $cleanData = [];

    foreach ($dataOutput as $value) {
      $currentNo = $no++;

      // === 1. Preserve raw role for logic ===
      $role_raw = strtolower($value->role ?? '');
      $is_terapis = $value->personnel_type === 'Therapist';
      $type_label = $is_terapis 
        ? '<span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest bg-teal-100 text-teal-600 border border-teal-200">Terapis</span>' 
        : '<span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest bg-blue-100 text-blue-600 border border-blue-200">Admin</span>';
      $value->type_label = $type_label;

      // === 2. Display Username (fallback to terapis_id) ===
      $display_username = $value->username;
      if (empty($display_username) || $display_username === '-') {
        $display_username = $value->terapis_id;
      }

      // === 3. Display Role ===
      if ($role_raw === 'user') {
        $display_role = !empty($value->terapis_jabatan_name) ? $value->terapis_jabatan_name : 'Terapis';
      } else {
        $display_role = ucfirst($role_raw ?: '-');
      }

      // === 4. Region Name ===
      $regions_patient_ids = [];
      if ($role_raw === 'superadmin') {
        $display_region = 'Semua Wilayah';
      } else if ($is_terapis && !empty($value->terapis_region_name)) {
        $display_region = $value->terapis_region_name;
      } else {
        $raw = $value->regions_patient;
        $regions_patient_ids = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
        $names = [];
        if (is_array($regions_patient_ids)) {
          foreach ($regions_patient_ids as $rid) {
            if (isset($region_map[$rid])) $names[] = $region_map[$rid];
          }
        }
        $display_region = !empty($names) ? implode(', ', $names) : '-';
      }

      // === 5. Build action buttons ===
      $display_realname = esc($value->name);

      $action = '<div class="flex items-center justify-center gap-1.5">';

      if (!empty($value->user_id)) {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white transition-all btn_edit" 
                        data-id="' . $value->user_id . '" 
                        data-realname="' . $display_realname . '" 
                        data-username="' . esc($display_username ?: '-') . '" 
                        data-role="' . $role_raw . '" 
                        data-regions_patient="' . implode(',', (array)$regions_patient_ids) . '" 
                        data-href="' . base_url('karyawan/update_account/' . $value->user_id) . '" 
                        title="Edit Akun">
                        <i class="fas fa-user-cog text-[10px]"></i>
                    </button>';
      } else if ($is_terapis) {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-teal-600 hover:text-white transition-all btn_create_account" 
                        data-terapis_id="' . $value->terapis_id . '" 
                        data-realname="' . $display_realname . '" 
                        data-region_id="' . ($value->terapis_region_id ?? '') . '" 
                        title="Buat Akun">
                        <i class="fas fa-user-plus text-[10px]"></i>
                    </button>';
      }

      if ($is_terapis) {
        $action .= '<a href="' . base_url('karyawan/show/' . $value->id_terapis_table) . '" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Profil Terapis">

                      <i class="fas fa-id-card text-[10px]"></i>
                    </a>';
      }

      if (!empty($value->user_id) && $value->role !== 'user') {
        $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-all btn_add_patient" data-userid="' . $value->user_id . '" title="Akses Pasien">
                        <i class="fas fa-hospital-user text-[10px]"></i>
                    </button>';
      }


      // Action: Aktif/Nonaktif (Toggle)
      if ($value->role !== 'superadmin') {

        if ($value->is_active == 1) {
          $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all btn_toggle_status" data-status="0" data-id="' . ($is_terapis ? $value->id_terapis_table : $value->user_id) . '" data-type="' . ($is_terapis ? 'terapis' : 'user') . '" title="Nonaktifkan">
                        <i class="fas fa-toggle-on text-[10px]"></i>
                      </button>';
        } else {
          $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all btn_toggle_status" data-status="1" data-id="' . ($is_terapis ? $value->id_terapis_table : $value->user_id) . '" data-type="' . ($is_terapis ? 'terapis' : 'user') . '" title="Aktifkan">
                        <i class="fas fa-toggle-off text-[10px]"></i>
                      </button>';
        }
        // Tombol hapus — hanya tampil untuk terapis (bukan pure user akun)
        if ($is_terapis) {
          $action .= '<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all btn_delete" data-href="' . base_url('karyawan/destroy/' . $value->id_terapis_table) . '" title="Hapus Karyawan">
                        <i class="fas fa-trash-alt text-[10px]"></i>
                      </button>';
        }
      }
      $action .= '</div>';

      // === 6. Display Status (tindakan count for terapis) ===
      $jml_tindakan = (int)($value->jml_tindakan ?? 0);
      if ($is_terapis) {
        $display_status = $jml_tindakan . ' Tindakan';
      } else {
        $display_status = '-';
      }

      // === 7. Build clean array — exact keys that JS expects ===
      $cleanData[] = [
        'no'          => $currentNo,
        'realname'    => $display_realname,
        'username'    => esc($display_username ?: '-'),
        'role'        => $display_role,
        'region_name' => $display_region,
        'status'      => $display_status,
        'action'      => $action,
        'is_active'   => $value->is_active,
      ];
    }

    return $this->response->setJSON([
      "draw" => intval($draw),
      "recordsTotal" => intval($totalRecords),
      "recordsFiltered" => intval($totalFiltered),
      "data"            => $cleanData,
      "csrfHash"        => csrf_hash()
    ]);
  }
  public function store()
  {
    $post = $this->request->getPost();
    
    // Check username uniqueness
    if (!empty($post['username'])) {
      if ($this->model_karyawan->db->table('users')->where('username', $post['username'])->get()->getRow()) {
          return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan', 'csrfHash' => csrf_hash()]);
      }
    }

    // Check terapis_id uniqueness if adding a therapist
    if ($post['role'] === 'user' && !empty($post['terapis_id'])) {
        if ($this->model_karyawan->db->table('terapis')->where('terapis_id', $post['terapis_id'])->get()->getRow()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Terapis sudah digunakan', 'csrfHash' => csrf_hash()]);
        }

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
    } elseif ($post['role'] === 'admin') {
      // For Admin, Owner, Superadmin: Automatically create employee profile in the terapis table
      $nextId = 1;
      $lastTerapis = $this->model_karyawan->db->table('terapis')
          ->where('terapis_id LIKE', 'ADM.%')
          ->orderBy('id', 'DESC')
          ->get()->getRow();
      if ($lastTerapis) {
          $lastNum = (int) substr($lastTerapis->terapis_id, 4);
          $nextId = $lastNum + 1;
      }
      $gen_terapis_id = 'ADM.' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
      
      // Determine default region
      $defaultRegionId = null;
      if (!empty($post['regions_patient'])) {
          $regionsArr = is_array($post['regions_patient']) ? $post['regions_patient'] : json_decode($post['regions_patient'], true);
          if (is_array($regionsArr) && !empty($regionsArr)) {
              $defaultRegionId = intval($regionsArr[0]);
          }
      } else {
          $firstRegion = $this->model_karyawan->db->table('regions')->select('id')->get()->getRow();
          if ($firstRegion) {
              $defaultRegionId = $firstRegion->id;
          }
      }

      // Query default jabatan
      $defaultJabatanId = null;
      $firstJabatan = $this->model_karyawan->db->table('jabatan')->select('id')->orderBy('id', 'ASC')->get()->getRow();
      if ($firstJabatan) {
          $defaultJabatanId = $firstJabatan->id;
      }

      $terapis_data = [
        'terapis_id' => $gen_terapis_id,
        'nama' => $post['realname'],
        'tempat_lahir' => '-',
        'tanggal_lahir' => '2000-01-01',
        'alamat' => '-',
        'region_id' => $defaultRegionId,
        'jabatan_id' => $defaultJabatanId,
        'rank' => 'Junior',
        'tgl_mulai_kerja' => date('Y-m-d H:i:s'),
        'foto' => null,
        'is_active' => 1
      ];

      $this->model_karyawan->insert($terapis_data);
      $terapis_id = $gen_terapis_id;
    }

    $user_data = [
      'realname' => $post['realname'],
      'username' => $post['username'],
      'password' => password_hash($post['password'] ?? 'password123', PASSWORD_BCRYPT),
      'role' => $post['role'],
      'terapis_id' => $terapis_id,
      'is_active' => 1,
      'other_patient' => json_encode([]),
      'regions_patient' => json_encode([]),
    ];

    if ($post['role'] === 'superadmin') {
      $user_data['regions_patient'] = json_encode([]);
    } elseif ($post['role'] === 'user' && !empty($post['region_id'])) {
      $user_data['regions_patient'] = json_encode([intval($post['region_id'])]);
    } else {

      $regions = $post['regions_patient'] ?? [];
      $user_data['regions_patient'] = json_encode(array_map('intval', (array)$regions));
    }

    if ($this->model_karyawan->db->table('users')->insert($user_data)) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data', 'csrfHash' => csrf_hash()]);
  }

  public function show($id)
  {
    $role = $this->session->get('role');
    $terapisIdInt = $this->session->get('terapis_id_int');

    // Terapis hanya boleh lihat profil sendiri
    if ($role === 'user' && !empty($terapisIdInt)) {
      if (!$terapisIdInt || $terapisIdInt != $id) {
        return redirect()->to('terapis/profil_saya')->with('message', ['error', 'Anda hanya dapat melihat profil sendiri']);
      }
    }

    $karyawan = $this->model_karyawan->getById($id);
    if (!$karyawan) return redirect()->to('karyawan');

    $data = [
      'realname' => $this->session->get('realname'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Detail Terapis',
      'msg' => $this->session->getFlashdata('message'),
      'role' => $role,
      'karyawan' => $karyawan,
      'wilayah' => $this->model_region->getData(),
      'jabatan' => $this->model_karyawan->get_jabatan(),
      'rank_options' => $this->getRankOptions(),
      'connected_user' => $this->model_karyawan->db->table('users')->where('terapis_id', $karyawan->terapis_id)->get()->getRow(),
      'all_users' => $this->model_karyawan->db->table('users')->select('id, realname, username, terapis_id')->where('role !=', 'superadmin')->get()->getResult(),
      'is_own_profile' => ($role === 'user' && !empty($terapisIdInt) && $terapisIdInt == $id), // Flag untuk disable field yang tidak boleh diedit
    ];

    // QR Code
    $qrContent = base_url('karyawan/public_info/' . $karyawan->terapis_id);
    $writer = new PngWriter();
    $qrCode = QrCode::create($qrContent)->setSize(300)->setMargin(10)->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 255, 255));
    $data['qr_code_base64'] = $writer->write($qrCode)->getDataUri();

    return view('App\modules\karyawan\Views\DetailTerapis\index', $data);
  }

  public function generate_user()
  {
    if (!$this->request->isAJAX()) return redirect()->to(site_url('karyawan'));

    $karyawan_id = $this->request->getPost('karyawan_id');
    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');

    if (!$karyawan_id || !$username || !$password) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap', 'csrfHash' => csrf_hash()]);
    }

    if (strlen($password) < 6) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Password minimal 6 karakter', 'csrfHash' => csrf_hash()]);
    }

    $terapis = $this->model_karyawan->where('terapis_id', $karyawan_id)->first();
    if (!$terapis) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Terapis tidak ditemukan', 'csrfHash' => csrf_hash()]);
    }

    $existingUser = $this->model_karyawan->db->table('users')->where('username', $username)->get()->getRow();
    if ($existingUser) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan', 'csrfHash' => csrf_hash()]);
    }

    $user_data = [
      'realname' => $terapis->nama,
      'username' => $username,
      'password' => password_hash($password, PASSWORD_BCRYPT),
      'role' => 'user',
      'terapis_id' => $karyawan_id,
      'regions_patient' => json_encode([$terapis->region_id]),
      'other_patient' => json_encode([])
    ];

    if ($this->model_karyawan->db->table('users')->insert($user_data)) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Akun login berhasil dibuat', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat akun login', 'csrfHash' => csrf_hash()]);
  }

  public function link_user()
  {
    if (!$this->request->isAJAX()) return redirect()->to(site_url('karyawan'));

    $user_id = $this->request->getPost('user_id');
    $karyawan_id = $this->request->getPost('karyawan_id');

    if (!$user_id || !$karyawan_id) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap', 'csrfHash' => csrf_hash()]);
    }

    if ($this->model_karyawan->db->table('users')->where('id', $user_id)->update(['terapis_id' => $karyawan_id])) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'Akun berhasil dihubungkan', 'csrfHash' => csrf_hash()]);
    }
    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghubungkan akun', 'csrfHash' => csrf_hash()]);
  }

  public function update_profile()
  {
    $id = $this->request->getPost('id');

    $role = $this->session->get('role');
    $terapisIdInt = $this->session->get('terapis_id_int');

    // Terapis hanya boleh update profil sendiri
    if ($role === 'user' && !empty($terapisIdInt)) {
      if (!$terapisIdInt || $terapisIdInt != $id) {
        $this->session->setFlashdata('message', ['error', 'Anda hanya dapat mengubah profil sendiri']);
        return redirect()->back();
      }

      // Terapis HANYA boleh update foto, tidak boleh update field lain
      $file = $this->request->getFile('foto');
      if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'foto_karyawan', $newName);

        // Update HANYA foto
        $this->model_karyawan->update($id, ['foto' => $newName]);
        $this->session->setFlashdata('message', ['success', 'Foto profil berhasil diperbarui']);
      } else {
        $this->session->setFlashdata('message', ['error', 'Tidak ada perubahan yang dilakukan']);
      }

      return redirect()->back();
    }

    // Untuk admin/owner/superadmin, boleh update semua field
    $karyawan_id = $this->request->getPost('terapis_id');
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

    // Sync realname to users table if linked
    if ($karyawan_id) {
        $this->model_karyawan->db->table('users')
            ->where('terapis_id', $karyawan_id)
            ->update(['realname' => $data['nama']]);
    }

    if ($this->model_karyawan->update($id, $data)) {
      $this->session->setFlashdata('message', ['success', 'Profil berhasil diperbarui']);
    }
    return redirect()->back();
  }

  public function update_account($id)
  {
    $post = $this->request->getPost();
    // Check if username is already taken by ANOTHER user
    if (!empty($post['username'])) {
        $existing = $this->model_karyawan->db->table('users')
            ->where('username', $post['username'])
            ->where('id !=', $id)
            ->get()->getRow();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan oleh akun lain', 'csrfHash' => csrf_hash()]);
        }
    }

    $regions_patient = [];
    if ($post['role'] === 'superadmin') {
        $regions_patient = [];
    } elseif ($post['role'] === 'user') {
        // Terapis (user) maintains their single region from terapis_id
        $user = $this->model_karyawan->db->table('users')->where('id', $id)->get()->getRow();
        if ($user && $user->terapis_id) {
            $terapis = $this->model_karyawan->db->table('terapis')->where('terapis_id', $user->terapis_id)->get()->getRow();
            if ($terapis && $terapis->region_id) {
                $regions_patient = [intval($terapis->region_id)];
            }
        }
    } else {
        $regions_patient = array_map('intval', (array)($post['regions_patient'] ?? []));
    }

    $data = [
      'realname' => $post['realname'],
      'username' => $post['username'],
      'role' => $post['role'],
      'regions_patient' => json_encode($regions_patient),
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
    $userId = $this->session->get('userId');
    $terapisId = $this->session->get('terapis_id');
    
    if ($terapisId) {
      $terapis = $this->model_karyawan->detail($terapisId);
      if ($terapis) {
        return $this->show($terapis->id);
      }
    }

    return redirect()->to(base_url('beranda'))->with('message', ['info', 'Profil Akun Manajemen sedang dalam pengembangan.']);
  }

  public function active($id)
  {
    // Only admin/owner/superadmin can activate/deactivate
    $role = $this->session->get('role');
    if ($role === 'user' && !empty($this->session->get('terapis_id'))) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses', 'csrfHash' => csrf_hash()]);
    }

    $type = $this->request->getPost('type') ?? 'terapis';
    if ($type === 'terapis') {
      $terapis = $this->model_karyawan->find($id);
      $this->model_karyawan->isActive($id, 1);
      if ($terapis) {
        $this->model_karyawan->db->table('users')->where('terapis_id', $terapis->terapis_id)->update(['is_active' => 1]);
      }
    } else {
      $this->model_karyawan->db->table('users')->where('id', $id)->update(['is_active' => 1]);
    }
    return $this->response->setJSON(['status' => 'success', 'message' => 'Status berhasil diaktifkan', 'csrfHash' => csrf_hash()]);
  }

  public function nonActive($id)
  {
    // Only admin/owner/superadmin can activate/deactivate
    $role = $this->session->get('role');
    if ($role === 'user' && !empty($this->session->get('terapis_id'))) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses', 'csrfHash' => csrf_hash()]);
    }

    $type = $this->request->getPost('type') ?? 'terapis';
    if ($type === 'terapis') {
      $terapis = $this->model_karyawan->find($id);
      $this->model_karyawan->isActive($id, 0);
      if ($terapis) {
        $this->model_karyawan->db->table('users')->where('terapis_id', $terapis->terapis_id)->update(['is_active' => 0]);
      }
    } else {
      $this->model_karyawan->db->table('users')->where('id', $id)->update(['is_active' => 0]);
    }
    return $this->response->setJSON(['status' => 'success', 'message' => 'Status berhasil dinonaktifkan', 'csrfHash' => csrf_hash()]);
  }

  public function destroy($id)
  {
    // Only admin/owner/superadmin can delete
    $role = $this->session->get('role');
    if ($role === 'user' && !empty($this->session->get('terapis_id'))) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses', 'csrfHash' => csrf_hash()]);
    }

    // Get terapis data to find linked user_id
    $terapis = $this->model_karyawan->find($id);
    if ($terapis) {
      // Delete linked user account if exists
      $this->model_karyawan->db->table('users')->where('terapis_id', $terapis->terapis_id)->delete();

      if ($this->model_karyawan->delete($id)) {
        if ($this->request->isAJAX()) {
          return $this->response->setJSON(['status' => 'success', 'message' => 'Data karyawan dan akun berhasil dihapus', 'csrfHash' => csrf_hash()]);
        }
        $this->session->setFlashdata('message', ['success', 'Data karyawan berhasil dihapus']);
      } else {
        if ($this->request->isAJAX()) {
          return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data karyawan', 'csrfHash' => csrf_hash()]);
        }
        $this->session->setFlashdata('message', ['error', 'Gagal menghapus data karyawan']);
      }
    } else {
      if ($this->request->isAJAX()) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan', 'csrfHash' => csrf_hash()]);
      }
    }
    return redirect()->to('karyawan');
  }

  public function view_patient($user_id)
  {
    $user = $this->model_karyawan->db->table('users')->where('id', $user_id)->get()->getRow();
    if (!$user) return redirect()->to('karyawan');

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

    return view('App\modules\karyawan\Views\view_patient', $data);
  }

  public function fetch_patients()
  {
    $user_id = $this->request->getPost('user_id');
    $draw = $this->request->getPost('draw');
    $start = $this->request->getPost('start');
    $length = $this->request->getPost('length');
    $search = $this->request->getPost('search')['value'] ?? '';

    $builder = $this->model_karyawan->get_patients_by_user_region($user_id);
    if ($search) $builder->groupStart()->like('p.name', $search)->orLike('p.address', $search)->groupEnd();


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
    if ($search) $builder->groupStart()->like('p.name', $search)->orLike('p.address', $search)->groupEnd();


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
                        <button class="btn-send-wa w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all" data-patient-id="' . $row->id . '"><i class="fab fa-whatsapp"></i></button>
                        <button class="btn-delete-patient w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all" data-patient-id="' . $row->id . '" data-user-id="' . $user_id . '"><i class="fas fa-trash"></i></button>
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
      'title' => 'Info Publik Terapis',
      'karyawan' => $karyawan,
      'jabatan' => $this->model_karyawan->getJabatanById($id),
      'wilayah' => $this->model_karyawan->getRegionById($id),
    ];
    return view('App\modules\karyawan\Views\views_info_publik', $data);
  }
}
