<?php

namespace App\Modules\Users\Controllers;

use App\Controllers\BaseController;
use App\modules\auth\Models\MAuth;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends BaseController
{
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->userModel = new MAuth();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get current user account data for editing
     */
    public function edit_account()
    {
        $userId = session()->get('userId');
        
        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data user tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $user->id,
            'userId' => $user->id,
            'realname' => $user->realname,
            'username' => $user->username,
            'role' => $user->role
        ]);
    }

    /**
     * Show account management page
     */
    public function account()
    {
        $userId = session()->get('userId');
        
        if (!$userId) {
            return redirect()->to('auth')->with('message', ['error', 'Sesi Anda telah berakhir']);
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('auth')->with('message', ['error', 'User tidak ditemukan']);
        }

        $data = [
            'title' => 'Akun Saya',
            'user' => $user
        ];

        return view('App\Modules\Users\Views\account', $data);
    }

    /**
     * Update current user account (for profile.php component)
     */
    public function update_account()
    {
        $userId = $this->request->getPost('user_id');
        
        if (!$userId) {
            return redirect()->back()->with('message', ['error', 'User ID tidak ditemukan']);
        }

        $realname = $this->request->getPost('realname');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Validasi input
        if (empty($realname) || empty($username)) {
            return redirect()->back()->with('message', ['error', 'Nama lengkap dan username harus diisi']);
        }

        // Cek apakah username sudah digunakan oleh user lain
        $existingUser = $this->userModel->where('username', $username)
                                        ->where('id !=', $userId)
                                        ->first();
        
        if ($existingUser) {
            return redirect()->back()->with('message', ['error', 'Username sudah digunakan oleh user lain']);
        }

        $data = [
            'realname' => $realname,
            'username' => $username
        ];

        // Update password jika diisi
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $this->userModel->update($userId, $data);

            // Update session
            session()->set('realname', $realname);
            session()->set('username', $username);

            return redirect()->back()->with('message', ['success', 'Akun berhasil diperbarui']);
        } catch (\Exception $e) {
            return redirect()->back()->with('message', ['error', 'Gagal memperbarui akun: ' . $e->getMessage()]);
        }
    }

    /**
     * Update user account (for headers.php - legacy support)
     */
    public function update_acount_users()
    {
        $userId = $this->request->getPost('user_id');
        
        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User ID tidak ditemukan'
            ]);
        }

        $realname = $this->request->getPost('realname');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Validasi input
        if (empty($realname) || empty($username)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nama lengkap dan username harus diisi'
            ]);
        }

        // Cek apakah username sudah digunakan oleh user lain
        $existingUser = $this->userModel->where('username', $username)
                                        ->where('id !=', $userId)
                                        ->first();
        
        if ($existingUser) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Username sudah digunakan oleh user lain'
            ]);
        }

        $data = [
            'realname' => $realname,
            'username' => $username
        ];

        // Update password jika diisi
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $this->userModel->update($userId, $data);

            // Update session
            session()->set('realname', $realname);
            session()->set('username', $username);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Akun berhasil diperbarui',
                'realname' => $realname,
                'username' => $username,
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui akun: ' . $e->getMessage(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }
}
