<?php

namespace App\Modules\Terapis\Controllers;

use App\Controllers\BaseController;
use App\modules\jabatan\Models\Mjabatan;
use App\modules\Region\Models\MRegion;
use App\modules\terapis\Models\MTerapis;
use App\modules\users\Models\MUsers;
use CodeIgniter\HTTP\ResponseInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TerapisController extends BaseController
{
  protected $model_terapis;
  protected $model_jabatan;
  protected $model_region;
  protected $model_users;
  protected $session;

  public function __construct()
  {
    $this->model_terapis = new MTerapis();
    $this->model_jabatan = new Mjabatan();
    $this->model_region = new MRegion();
    $this->model_users = new MUsers();
    $this->session = \Config\Services::session();

    $router = service('router');
    $method = $router->methodName();
    $role = $this->session->get('role');

    if ($method !== 'public_info' && $method !== 'profil_saya') {
      if ($role !== 'superadmin' && $role !== 'owner' && $role !== 'admin') {
        $this->session->setFlashdata('message', ['danger', 'You do not have access to this page']);
        return redirect()->to(base_url())->send();
      }
    }
  }

  public function index()
  {
    //

    $role = $this->session->get('role');
    $allowed_regions = ($role !== 'superadmin') ? $this->session->get('region_patient') : null;

    $data = [
      'realname' => $this->session->get('realname'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Terapis',
      'regions' => $this->model_terapis->get_regions($allowed_regions),
      'jabatan' => $this->model_jabatan->getData(),
      'msg' => $this->session->getFlashdata('message'),
      'role' => $role,
    ];
    $data['wilayah'] = $this->model_region->getData(null, $allowed_regions);

    return view('App\modules\terapis\Views\index', $data);
  }

  public function fetch()
  {
    $region = $this->request->getPost('region');
    $role = $this->session->get('role');
    $allowed_regions = ($role !== 'superadmin') ? $this->session->get('region_patient') : null;

    $queryBuilder = $this->model_terapis->getTerapis($region, $allowed_regions);

    $datatables = new \Ngekoding\CodeIgniterDataTables\DataTables($queryBuilder, '4');

    $start = $this->request->getPost('start') ?? 0;
    $datatables->addColumn('no', function ($row) use (&$start) {
      return ++$start;
    });

    $datatables->addColumn('jml_tindakan', function ($row) {
      $row = (object) $row;
      $jumlah = $row->jml_tindakan ?? 0;

      if ($jumlah == 0) {
        return '<span class="badge badge-danger">0 Tindakan</span>';
      } else {
        return '<span class="badge badge-info">' . $jumlah . ' Tindakan</span>';
      }
    });

    $datatables->addColumn('is_active', function ($row) {
      $row = (object) $row;
      if ($row->is_active == 1) {
        return '<span class="badge badge-success">Aktif</span>';
      } else {
        return '<span class="badge badge-danger">Tidak Aktif</span>';
      }
    });

    $datatables->addColumn('action', function ($row) {
      $row = (object) $row;
      $type = $row->is_active == 1 ? 'delete' : 'active';
      $btn_status = $row->is_active == 1 ? '<a href="javascript:void(0)" data-href="' . base_url('terapis/nonActive/' . $row->id) . '" data-type="delete" class="btn btn-danger btn_status"><i class="fas fa-window-close"></i></a>' : '<a href="javascript:void(0)" data-href="' . base_url('terapis/active/' . $row->id) . '" data-type="active" class="btn btn-primary btn_status mr-1"><i class="fas fa-check-square"></i></a>';

      return '
            <a href="' .
        base_url('terapis/detail-terapis/' . $row->terapis_id) .
        '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a> ' .
        '<button type="button" onclick="generateUser(\'' . $row->terapis_id . '\')" class="btn btn-warning btn-sm" title="Buat Akun Login"><i class="fas fa-user-plus"></i></button> ' .
        $btn_status;
      // 'button type="button" data-href="' . base_url("terapis/destroy/" . $row->id) . '" class="btn btn-danger btn-sm btn_delete"><i class="fas fa-trash"></i></button>';
    });

    $datatables->asObject();
    $output = $datatables->generate(false);
    $output['csrfHash'] = csrf_hash();
    return $this->response->setJSON($output);
  }

  public function detail_terapis($user_id)
  {
    $data = [
      'realname' => $this->session->get('realname'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Data Terapis',
      'msg' => $this->session->getFlashdata('message'),
      'role' => $this->session->get('role'),
      'terapis' => $this->model_terapis->detail($user_id),
      'wilayah' => $this->model_region->getData(),
      'jabatan' => $this->model_terapis->get_jabatan(),
      'connected_user' => $this->model_users->where('terapis_id', $user_id)->first(),
      'all_users' => $this->model_users->select('id, username, realname')->orderBy('realname', 'ASC')->findAll(),
    ];

    $qrContent = base_url('terapis/public_info/' . $user_id);

    $writer = new PngWriter();
    $qrCode = QrCode::create($qrContent)
      ->setEncoding(new Encoding('UTF-8'))
      ->setSize(300)
      ->setMargin(10)
      ->setForegroundColor(new Color(0, 0, 0)) // Warna QR (Hitam)
      ->setBackgroundColor(new Color(255, 255, 255)); // Warna Background (Putih)

    $result = $writer->write($qrCode);

    $data['qr_code_base64'] = $result->getDataUri();

    return view('App\modules\Terapis\Views\DetailTerapis\index', $data);
  }

  public function checkId()
  {
    $id = $this->request->getPost('terapis_id');
    $currentId = $this->request->getPost('currentId');
    $builder = $this->model_terapis;
    if (!empty($currentId)) {
      $builder->where('terapis_id !=', $currentId);
    }
    $exists = $builder->where('terapis_id', $id)->countAllResults() > 0;
    return $this->response->setJSON(['exists' => $exists]);
  }

  public function public_info($id)
  {
    $data = [
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Data Terapis',
      'terapis' => $this->model_terapis->detail($id),
      'jabatan' => $this->model_terapis->getJabatanById($id),
      'wilayah' => $this->model_terapis->getRegionById($id),
    ];

    return view('App\modules\terapis\Views\views_info_publik', $data);
  }

  public function store()
  {
    $file = $this->request->getFile('foto');
    $tgl_kerja = $this->request->getPost('tgl_kerja');

    $data = [
      'terapis_id' => $this->request->getPost('terapis_id'),
      'nama' => $this->request->getPost('nama'),
      'tempat_lahir' => $this->request->getPost('tempat_lahir'),
      'tanggal_lahir' => $this->request->getPost('tgl_lahir'),
      'alamat' => $this->request->getPost('alamat') ?: null,
      'region_id' => $this->request->getPost('region_id'),
      'jabatan_id' => $this->request->getPost('jabatan_id'),
      'rank' => $this->request->getPost('rank'),
      'tgl_mulai_kerja' => !empty($tgl_kerja) ? $tgl_kerja . ' ' . date('H:i:s') : null,
      'keterangan' => $this->request->getPost('keterangan'),
    ];

    if ($file && $file->isValid() && !$file->hasMoved()) {
      $newName = $file->getRandomName();
      $file->move(FCPATH . 'foto_terapis/', $newName);
      $data['foto'] = $newName;
    }

    if ($this->model_terapis->store($data)) {
      $this->session->setFlashdata('message', ['success', 'Data berhasil disimpan']);
    } else {
      $this->session->setFlashdata('message', ['danger', 'Gagal menyimpan data']);
    }

    return redirect()->to('terapis');
  }

  public function update()
  {
    $id = $this->request->getPost('id');
    $terapis_id = $this->request->getPost('terapis_id');
    $status = $this->request->getPost('status') === 'on' ? 1 : 0;
    $file = $this->request->getFile('foto');

    $data = [
      'terapis_id' => $terapis_id,
      'nama' => $this->request->getPost('nama'),
      'tempat_lahir' => $this->request->getPost('tempat_lahir'),
      'tanggal_lahir' => $this->request->getPost('tgl_lahir'),
      'alamat' => $this->request->getPost('alamat') ?: null,
      'region_id' => $this->request->getPost('region_id'),
      'jabatan_id' => $this->request->getPost('jabatan_id'),
      'rank' => $this->request->getPost('rank'),
      'tgl_mulai_kerja' => $this->request->getPost('tgl_kerja'),
      'keterangan' => $this->request->getPost('keterangan'),
      'is_active' => $status,
    ];

    if ($file && $file->isValid() && !$file->hasMoved()) {
      // Hapus foto lama
      $oldData = $this->model_terapis->getById($id);
      if ($oldData && $oldData->foto && file_exists(FCPATH . 'foto_terapis/' . $oldData->foto)) {
        unlink(FCPATH . 'foto_terapis/' . $oldData->foto);
      }

      $newName = $file->getRandomName();
      $file->move(FCPATH . 'foto_terapis/', $newName);
      $data['foto'] = $newName;
    }

    if ($this->model_terapis->update($id, $data)) {
      $this->session->setFlashdata('message', ['success', 'Data berhasil diperbarui']);
    }

    return redirect()->to('terapis/detail_terapis/' . $terapis_id);
  }

  public function destroy($id)
  {
    $terapis = $this->model_terapis->getById($id);
    if ($terapis) {
      if ($terapis->foto && file_exists(FCPATH . 'foto_terapis/' . $terapis->foto)) {
        unlink(FCPATH . 'foto_terapis/' . $terapis->foto);
      }
      $this->model_terapis->delete($id);
      $this->session->setFlashdata('message', ['success', 'Data berhasil dihapus']);
    }
    return redirect()->to('terapis');
  }

  public function active($id)
  {
    if ($this->request->isAJAX()) {
      if ($this->model_terapis->update($id, ['is_active' => 1])) {
        return $this->response->setJSON([
          'status' => 'success',
          'message' => 'Terapis berhasil diaktifkan',
          'csrfHash' => csrf_hash(), // Penting untuk update token di sisi JS
        ]);
      }

      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Gagal mengaktifkan terapis',
      ]);
    }

    // Fallback jika diakses manual tanpa AJAX (opsional)
    $this->model_terapis->update($id, ['is_active' => 1]);
    return redirect()->to('terapis');
  }

  public function nonActive($id)
  {
    if ($this->request->isAJAX()) {
      if ($this->model_terapis->update($id, ['is_active' => 0])) {
        return $this->response->setJSON([
          'status' => 'success',
          'message' => 'Terapis berhasil dinonaktifkan',
          'csrfHash' => csrf_hash(),
        ]);
      }

      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Gagal menonaktifkan terapis',
      ]);
    }

    $this->model_terapis->update($id, ['is_active' => 0]);
    return redirect()->to('terapis');
  }

  public function link_user()
  {
    if (!$this->request->isAJAX()) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    try {
      $user_id = $this->request->getPost('user_id');
      $terapis_id = trim($this->request->getPost('terapis_id'));

      $terapis = $this->model_terapis->detail($terapis_id);
      if (!$terapis) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data Terapis tidak ditemukan', 'csrfHash' => csrf_hash()]);
      }

      $user = $this->model_users->find($user_id);
      if (!$user) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data User tidak ditemukan', 'csrfHash' => csrf_hash()]);
      }

      // Cek apakah terapis_id sudah dipakai user lain
      $checkTerapis = $this->model_users->where('terapis_id', $terapis_id)->where('id !=', $user_id)->first();
      if ($checkTerapis) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'ID Terapis ini sudah terhubung dengan akun lain!', 'csrfHash' => csrf_hash()]);
      }

      // Update user
      $updateData = [
        'terapis_id' => $terapis_id,
        'realname' => $terapis->nama,
      ];

      if ($this->model_users->update($user_id, $updateData)) {
        return $this->response->setJSON([
          'status' => 'success',
          'message' => 'Akun berhasil dihubungkan!',
          'csrfHash' => csrf_hash()
        ]);
      }

      return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data user', 'csrfHash' => csrf_hash()]);
    } catch (\Exception $e) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage(), 'csrfHash' => csrf_hash()]);
    }
  }

  public function generate_user()
  {
    if (!$this->request->isAJAX()) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    try {
      $terapis_id = trim($this->request->getPost('terapis_id'));
      $custom_username = trim($this->request->getPost('username'));
      $custom_password = $this->request->getPost('password');

      $terapis = $this->model_terapis->detail($terapis_id);

      if (!$terapis) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Data Terapis tidak ditemukan (ID: ' . $terapis_id . ')', 'csrfHash' => csrf_hash()]);
      }

      // Check if user already exists (based on link)
      $existingUser = $this->model_users->where('terapis_id', $terapis->terapis_id)->first();
      if ($existingUser) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Akun User untuk Terapis ini sudah ada!', 'csrfHash' => csrf_hash()]);
      }

      // Final values
      $username = !empty($custom_username) ? $custom_username : $terapis->terapis_id;
      $password = !empty($custom_password) ? $custom_password : 'password123';

      // Check if username already taken by anyone
      $usernameTaken = $this->model_users->where('username', $username)->first();
      if ($usernameTaken) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan oleh orang lain!', 'csrfHash' => csrf_hash()]);
      }

      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      $userData = [
        'realname' => $terapis->nama,
        'username' => $username,
        'password' => $hashedPassword,
        'role' => 'user',
        'terapis_id' => $terapis->terapis_id,
        'regions_patient' => json_encode([$terapis->region_id]),
      ];

      if ($this->model_users->insert($userData)) {
        return $this->response->setJSON([
          'status' => 'success',
          'message' => 'Akun berhasil dibuat. Username: ' . $username . ' | Password: ' . $password,
          'csrfHash' => csrf_hash()
        ]);
      }

      $dbError = $this->model_users->errors();
      $errorMessage = !empty($dbError) ? implode(', ', (array)$dbError) : 'Gagal menyimpan data ke database';

      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Gagal membuat akun: ' . $errorMessage,
        'csrfHash' => csrf_hash()
      ]);
    } catch (\Exception $e) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Sistem Error: ' . $e->getMessage(),
        'csrfHash' => csrf_hash()
      ]);
    }
  }

  public function profil_saya()
  {
    $terapis_id = $this->session->get('terapis_id');
    
    if (!$terapis_id) {
        $this->session->setFlashdata('message', ['danger', 'Akun Anda tidak terhubung dengan data Terapis.']);
        return redirect()->to(base_url('beranda'));
    }

    $terapis = $this->model_terapis->detail($terapis_id);

    if (!$terapis) {
      $this->session->setFlashdata('message', ['danger', 'Data Profil Terapis tidak ditemukan.']);
      return redirect()->to(base_url('beranda'));
    }

    $data = [
      'realname' => $this->session->get('realname'),
      'base_url' => base_url(),
      'current_segment' => $this->request->getUri()->getSegment(1),
      'title' => 'Profil Saya',
      'msg' => $this->session->getFlashdata('message'),
      'role' => $this->session->get('role'),
      'terapis' => $terapis,
      'wilayah' => $this->model_region->getData(),
      'jabatan' => $this->model_terapis->get_jabatan(),
    ];

    // Generate QR Code as in detail_terapis
    $qrContent = base_url('terapis/public_info/' . $username);
    $writer = new PngWriter();
    $qrCode = QrCode::create($qrContent)
      ->setEncoding(new Encoding('UTF-8'))
      ->setSize(300)
      ->setMargin(10)
      ->setForegroundColor(new Color(0, 0, 0))
      ->setBackgroundColor(new Color(255, 255, 255));

    $result = $writer->write($qrCode);
    $data['qr_code_base64'] = $result->getDataUri();

    return view('App\modules\Terapis\Views\DetailTerapis\index', $data);
  }
}
