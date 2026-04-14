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
            return redirect()->to(base_url('beranda_views'));
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

        // Lama
        // if ($user) {
        //     $sessionData = [
        //         'isLogin'         => true,
        //         'userId'          => $user->id,
        //         'realname'        => $user->realname,
        //         'role'            => $user->role,
        //         'regions_patient' => $user->regions_patient,
        //     ];
        //     session()->set($sessionData);

        //     session()->regenerate();

        //     return redirect()->to(base_url('beranda_views'));
        // } else {
        //     $params = ['1', 'error', 'Nama pengguna dan kata sandi tidak sesuai', ''];
        //     session()->setFlashdata('pesan', $params);

        //     return redirect()->to(base_url('auth'));
        // }
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
                $get_region = $db->table('regions')->select('id, name')->get()->getResultArray();
                $sessionData = [
                    'isLogin'         => true,
                    'userId'          => $user->id,
                    'realname'        => $user->realname,
                    'role'            => $user->role,
                    'regions_patient' => $user->regions_patient,
                    'list_regions_global' => $get_region, // Untuk isi dropdown
                    'active_region'       => 'all',        // Default awal saat login
                    'active_region_name'  => 'Semua Wilayah'

                ];
                session()->set($sessionData);
                session()->regenerate();

                return redirect()->to(base_url('beranda_views'));
            }
        }

        $params = ['1', 'error', 'Nama pengguna dan kata sandi tidak sesuai', ''];
        session()->setFlashdata('pesan', $params);
        return redirect()->to(base_url('auth'));
    }

 public function switch_region()
{
    // Cek Role: Hanya Owner dan Superadmin yang boleh switch
    $role = session()->get('role');
    if ($role !== 'owner') {
        return $this->response->setJSON([
            'status' => 'error', 
            'message' => 'Akses ditolak!'
        ], 403);
    }

    $regionId   = $this->request->getPost('region_id');
    $regionName = $this->request->getPost('region_name');

    if ($regionId) {
        session()->set('active_region', $regionId);
        $displayName = ($regionId === 'all') ? 'Semua Wilayah' : $regionName;
        session()->set('active_region_name', $displayName);

        return $this->response->setJSON(['status' => 'success']);
    }

    return $this->response->setJSON(['status' => 'error'], 400);
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
