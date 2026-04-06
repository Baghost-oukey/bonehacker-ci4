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
                $sessionData = [
                    'isLogin'         => true,
                    'userId'          => $user->id,
                    'realname'        => $user->realname,
                    'role'            => $user->role,
                    'regions_patient' => $user->regions_patient,
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
