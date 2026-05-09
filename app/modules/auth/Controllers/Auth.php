<?php

namespace App\modules\auth\Controllers;

use App\Controllers\BaseController;
use App\modules\auth\models\MAuth;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{

    protected $authModel;
    public function __construct()
    {
        $this->authModel = new MAuth();
    }


    public function index()
    {
        //
        if (session()->get('isLogin')) {
            return redirect()->to(base_url('beranda'));
        }

        $data = [
            'title' => 'Masuk',
            'msg' => session()->getFlashdata('pesan')
        ];
        return view('\App\modules\auth\Views\auth_views', $data);
    }

    public function authValidate(): RedirectResponse
    {
        // Lama
        // $username = $this->request->getPost('username');
        // $password = $this->request->getPost('password');

        $username = (string)$this->request->getPost('username');
        $password = (string)$this->request->getPost('password');

        // Lama
        // $user = $this->authModel->verifyLogin((string) $username, (string)$password);
        $user = $this->authModel->where('username', $username)->first();

        if ($user) {
            $isPasswordCorrect = false;
            if (password_verify($password, $user->password)) {
                $isPasswordCorrect = true;
            } elseif (strlen($user->password) === 32 && md5($password) === $user->password) {
                $isPasswordCorrect = true;

                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $this->authModel->update($user->id, ['password' => $newHash]);
            }

            if ($isPasswordCorrect) {
                $db = \Config\Database::connect();
                // Mengambil data berdasarkan berdasarkan superadmin
                $get_region = $db->table('regions')->select('id, name')->get()->getResultArray();
                // Mengambil Data region berdasarkan user

                $user_region = $user->regions_patient;
                $final_region_for_session = null;
                $current_region_Id = null;

                // $current_region_Id = str_contains($user_region, '[')
                //     ? (json_decode($user_region, true)[0] ?? null)
                //     : $user_region;

                if (!empty($user_region)) {
                    // Cek apakah isinya format JSON (misal: ["5"])
                    $decoded = json_decode($user_region, true);

                    if (is_array($decoded) && !empty($decoded)) {
                        // Jika dia array ["5"], ambil angka 5-nya saja untuk pencarian detail
                        $current_region_Id = $decoded[0];
                        $final_region_for_session = $decoded; // Simpan tetap sebagai array untuk whereIn
                    } else {
                        // Jika string biasa (misal: 5), ambil langsung
                        $current_region_Id = $user_region;
                        $final_region_for_session = $user_region;
                    }
                }

                // $regionDetail = $db->table('regions')->where('id', $current_region_Id)->get()->getRow();
                $regionDetail = null;
                if ($current_region_Id) {
                    $regionDetail = $db->table('regions')->where('id', $current_region_Id)->get()->getRow();
                }

                $defaultActive = ($user->role === 'owner' || $user->role === 'superadmin') ? 'all' : $current_region_Id;
                $defaultName   = ($user->role === 'owner' || $user->role === 'superadmin') ? 'Semua Wilayah' : ($regionDetail ? $regionDetail->name : 'Cabang');


                $sessionData = [
                    'isLogin'         => true,
                    'userId'          => $user->id,
                    'realname'        => $user->realname,
                    'role'            => $user->role,
                    // Untuk Data Pasien
                    'region_id'       => $current_region_Id,
                    'region_name'     => $regionDetail ? $regionDetail->name : 'Cabang Tidak Terdeteksi',
                    'region_patient' => $final_region_for_session,
                    'list_regions_global' => $get_region, // Untuk isi dropdown
                    'active_region'       => $defaultActive,        // Default awal saat login
                    'active_region_name'  => $defaultName

                ];
                session()->set($sessionData);
                session()->regenerate();

                return redirect()->to(base_url('beranda'));
            }
        }

        $params = ['1', 'error', 'Nama pengguna dan kata sandi tidak sesuai', ''];
        session()->setFlashdata('pesan', $params);
        return redirect()->to(base_url('auth'));
    }

    public function switch_region()
    {
        $currentRole = session()->get('role');
        $allowedRoles = ['owner', 'superadmin'];

        if (!in_array($currentRole, $allowedRoles)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki otoritas untuk berpindah wilayah!'
            ], 403);
        }

        // 2. Ambil data dari POST
        $targetId   = $this->request->getPost('region_id');
        $targetName = $this->request->getPost('region_name');

        if ($targetId) {
            // Simpan ke session untuk kacamata filter aplikasi
            session()->set('active_region', $targetId);

            $displayName = ($targetId === 'all') ? 'Semua Wilayah' : $targetName;
            session()->set('active_region_name', $displayName);

            if ($targetId === 'all') {
                session()->set('region_patient', 'all');
            } else {
                session()->set('region_patient', [(int)$targetId]); 
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Sekarang menampilkan data: ' . $displayName
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'ID Wilayah tidak valid!'
        ], 400);
    }

    public function destroy(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to(base_url('auth'));
    }

    public function get_csrf()
    {
        return $this->response->setJSON([
            'hash' => csrf_hash(),
        ]);
    }
}
