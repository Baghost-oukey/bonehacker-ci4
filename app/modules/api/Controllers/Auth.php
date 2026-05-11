<?php

namespace App\Modules\api\Controllers;

use App\Controllers\BaseController;
use App\modules\auth\models\MAuth;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;

    protected $authModel;

    public function __construct()
    {
        $this->authModel = new MAuth();
    }

    /**
     * Login for Flutter Mobile App
     * POST /api/login
     */
    public function login()
    {
        $username = (string)$this->request->getPost('username');
        $password = (string)$this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return $this->fail('Username dan Password wajib diisi', 400);
        }

        $user = $this->authModel->where('username', $username)->first();

        if (!$user) {
            return $this->fail('User tidak ditemukan', 404);
        }

        $isPasswordCorrect = false;
        
        // Cek password (BCRYPT atau MD5 legacy)
        if (password_verify($password, $user->password)) {
            $isPasswordCorrect = true;
        } elseif (strlen($user->password) === 32 && md5($password) === $user->password) {
            $isPasswordCorrect = true;
            // Update to modern hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->authModel->update($user->id, ['password' => $newHash]);
        }

        if (!$isPasswordCorrect) {
            return $this->fail('Password salah', 401);
        }

        try {
            // Ambil data region untuk context aplikasi mobile
            $db = \Config\Database::connect();
            $user_region = $user->regions_patient ?? null;
            $current_region_Id = null;

            if (!empty($user_region)) {
                $decoded = json_decode($user_region, true);
                if (is_array($decoded)) {
                    $current_region_Id = !empty($decoded) ? $decoded[0] : null;
                } else {
                    // Jika bukan JSON array, cek apakah string '[]'
                    $current_region_Id = ($user_region === '[]') ? null : $user_region;
                }
            }

            $regionDetail = null;
            if ($current_region_Id) {
                $regionDetail = $db->table('regions')->where('id', $current_region_Id)->get()->getRow();
            }

            // Persiapkan data response
            $responseData = [
                'status' => 'success',
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => (int)$user->id,
                        'username' => $user->username,
                        'realname' => $user->realname,
                        'role' => $user->role ?? 'user',
                    ],
                    'region' => [
                        'id' => $current_region_Id ? (string)$current_region_Id : '',
                        'name' => $regionDetail ? $regionDetail->name : 'Cabang Tidak Terdeteksi',
                    ]
                ]
            ];

            return $this->respond($responseData, 200);
        } catch (\Exception $e) {
            return $this->fail('Terjadi kesalahan data: ' . $e->getMessage(), 500);
        }
    }
}
