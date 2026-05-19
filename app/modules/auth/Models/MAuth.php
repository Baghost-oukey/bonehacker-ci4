<?php

namespace App\modules\auth\Models;

use CodeIgniter\Model;

class MAuth extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['password', 'role', 'terapis_id', 'is_active', 'remember_token'];

    public function getUserSessionData($user)
    {
        $db = \Config\Database::connect();
        $get_region = $db->table('regions')->select('id, name')->where('is_active', 1)->get()->getResultArray();

        $user_region = $user->regions_patient;
        $final_region_for_session = null;
        $current_region_Id = null;

        if (!empty($user_region)) {
            $decoded = json_decode($user_region, true);
            if (is_array($decoded) && !empty($decoded)) {
                $current_region_Id = $decoded[0];
                $final_region_for_session = $decoded;
            } else {
                $current_region_Id = $user_region;
                $final_region_for_session = $user_region;
            }
        }

        if ($user->role === 'superadmin') {
            $final_region_for_session = 'all';
        }

        $regionDetail = null;
        if ($current_region_Id) {
            $regionDetail = $db->table('regions')->where('id', $current_region_Id)->get()->getRow();
        }

        $defaultActive = ($user->role === 'superadmin') ? 'all' : $current_region_Id;
        $defaultName   = ($user->role === 'superadmin') ? 'Semua Wilayah' : ($regionDetail ? $regionDetail->name : 'Cabang');

        $avatarUrl = null;
        $terapisIdInt = null;
        $terapisIdStr = $user->terapis_id;

        if (!empty($terapisIdStr)) {
            $terapis = $db->table('terapis')->select('id, foto')->where('terapis_id', $terapisIdStr)->get()->getRow();
            if ($terapis) {
                $terapisIdInt = $terapis->id;
                if ($terapis->foto) {
                    $avatarUrl = base_url('foto_karyawan/' . $terapis->foto);
                }
            }
        }

        // Smart fallback during login for Admin only with empty terapis_id
        if (empty($terapisIdStr) && $user->role === 'admin') {
            $realname = $user->realname;
            $username = $user->username;
            
            $terapis = $db->table('terapis')->select('id, terapis_id, foto')
                ->groupStart()
                    ->where('nama', $realname)
                    ->orWhere('nama', $username)
                ->groupEnd()
                ->get()->getRow();
                
            if (!$terapis) {
                $builder = $db->table('terapis')->select('id, terapis_id, foto');
                $hasCond = false;
                if (!empty($realname) && strlen($realname) >= 3) {
                    $builder->like('nama', $realname);
                    $hasCond = true;
                }
                if (!empty($username) && strlen($username) >= 3) {
                    if ($hasCond) {
                        $builder->orLike('nama', $username);
                    } else {
                        $builder->like('nama', $username);
                        $hasCond = true;
                    }
                }
                if ($hasCond) {
                    $terapis = $builder->get()->getRow();
                }
            }
            
            if ($terapis) {
                $terapisIdInt = $terapis->id;
                $terapisIdStr = $terapis->terapis_id;
                if ($terapis->foto) {
                    $avatarUrl = base_url('foto_karyawan/' . $terapis->foto);
                }
            }
        }

        return [
            'isLogin'         => true,
            'userId'          => $user->id,
            'username'        => $user->username,
            'realname'        => $user->realname,
            'role'            => $user->role,
            'avatar_url'      => $avatarUrl,
            'region_id'       => $current_region_Id,
            'region_name'     => $regionDetail ? $regionDetail->name : 'Cabang Tidak Terdeteksi',
            'region_patient' => $final_region_for_session,
            'region_patient_allowed' => $final_region_for_session,
            'list_regions_global' => $get_region,
            'active_region'       => $defaultActive,
            'active_region_name'  => $defaultName,
            'terapis_id'      => $terapisIdStr, // Tetap simpan string ID untuk kebutuhan lain
            'terapis_id_int'  => $terapisIdInt,    // Tambahkan integer ID untuk sinkronisasi database
        ];
    }

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // public function verifyLogin(string $username, string $password): ?object
    // {
    //     $username = trim($username);
    //     $password = trim($password);

    //     $user = $this->where('username', $username)->first();

    //     if ($user) {
    //         $dbpassword = trim($user->password);

    //         if (password_verify($password, $dbpassword)) {
    //             return $user;
    //         }

    //         if(md5($password) === $dbpassword){
    //             $newHash = password_hash($password, PASSWORD_BCRYPT);

    //             $this->update($user->id, ['password' => $newHash]);

    //             return $this->find($user->id);
    //         }
    //     }
    //     return null;
    // }
}
