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
        helper('cookie'); // Load cookie helper
    }


    public function index()
    {
        // Cek apakah sudah login
        if (session()->get('isLogin')) {
            $agent = $this->request->getUserAgent();
            if ($agent->isMobile() || preg_match('/Mobile|Android|iPhone|Flutter|BoneHacker-App/i', $agent->getAgentString())) {
                return redirect()->to(base_url('menu'));
            }
            return redirect()->to(base_url('beranda'));
        }

        // Auto-login jika ada cookie "remember_me"
        $rememberCookie = get_cookie('remember_me');
        if ($rememberCookie) {
            $decoded = base64_decode($rememberCookie);
            $parts = explode(':', $decoded);

            if (count($parts) === 2) {
                $userId = $parts[0];
                $token = $parts[1];

                // Ambil user dari database
                $user = $this->authModel->find($userId);

                if ($user && $user->is_active == 1 && !empty($user->remember_token)) {
                    // Verifikasi token
                    if (password_verify($token, $user->remember_token)) {
                        // Token valid, auto-login
                        $this->setSession($user);
                        $agent = $this->request->getUserAgent();
                        if ($agent->isMobile() || preg_match('/Mobile|Android|iPhone|Flutter|BoneHacker-App/i', $agent->getAgentString())) {
                            return redirect()->to(base_url('menu'));
                        }
                        return redirect()->to(base_url('beranda'));
                    }
                }

                // Token tidak valid, hapus cookie
                delete_cookie('remember_me');
            }
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
            if (isset($user->is_active) && $user->is_active == 0) {
                session()->setFlashdata('message', ['error', 'Gagal Login', 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.']);
                return redirect()->to(base_url('auth'));
            }
            $isPasswordCorrect = false;
            if (password_verify($password, $user->password)) {
                $isPasswordCorrect = true;
            } elseif (strlen($user->password) === 32 && md5($password) === $user->password) {
                $isPasswordCorrect = true;

                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $this->authModel->update($user->id, ['password' => $newHash]);
            }

            if ($isPasswordCorrect) {
                $this->setSession($user);

                // Selalu simpan username untuk auto-fill form (60 hari)
                $cookie = [
                    'name'   => 'saved_username',
                    'value'  => $username,
                    'expire' => 3600 * 24 * 60, // 60 hari
                    'path'   => '/',
                    'secure' => false,
                    'httponly' => false, // Biar bisa diakses JavaScript jika perlu
                    'samesite' => 'Lax'
                ];
                $this->response->setCookie($cookie);

                // Remember Me Logic (Auto-login)
                if ($this->request->getPost('ingat_saya')) {
                    $token = bin2hex(random_bytes(32));
                    $rememberToken = password_hash($token, PASSWORD_DEFAULT);

                    $this->authModel->update($user->id, [
                        'remember_token' => $rememberToken
                    ]);

                    // Set cookie for 30 days
                    // Format: user_id:token
                    $cookieValue = base64_encode($user->id . ':' . $token);
                    $rememberCookie = [
                        'name'   => 'remember_me',
                        'value'  => $cookieValue,
                        'expire' => 3600 * 24 * 30, // 30 hari
                        'path'   => '/',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ];
                    $this->response->setCookie($rememberCookie);
                }

                $agent = $this->request->getUserAgent();
                if ($agent->isMobile() || preg_match('/Mobile|Android|iPhone|Flutter|BoneHacker-App/i', $agent->getAgentString())) {
                    return redirect()->to(base_url('menu'));
                }
                return redirect()->to(base_url('beranda'));
            }
        }

        $params = ['1', 'error', 'Nama pengguna dan kata sandi tidak sesuai', ''];
        session()->setFlashdata('pesan', $params);
        return redirect()->to(base_url('auth'));
    }

    private function setSession($user)
    {
        $sessionData = $this->authModel->getUserSessionData($user);
        session()->set($sessionData);
        session()->regenerate();
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
                $role = session()->get('role');
                if ($role === 'superadmin') {
                    session()->set('region_patient', 'all');
                } else {
                    $original_allowed = session()->get('region_patient_allowed');
                    session()->set('region_patient', $original_allowed);
                }
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
        $userId = session()->get('userId');
        if ($userId) {
            $this->authModel->update($userId, ['remember_token' => null]);
        }

        // Hapus cookie auto-login, tapi JANGAN hapus saved_username (untuk auto-fill)
        delete_cookie('remember_me');
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
