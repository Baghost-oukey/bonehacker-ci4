<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_FixExistingAdminTerapisIds extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // 1. Ambil semua user dengan role admin yang terapis_id-nya masih kosong
        $users = $db->table('users')
            ->where('role', 'admin')
            ->groupStart()
                ->where('terapis_id IS NULL')
                ->orWhere('terapis_id', '')
            ->groupEnd()
            ->get()
            ->getResultArray();

        foreach ($users as $user) {
            $realname = $user['realname'];
            $username = $user['username'];
            $userId   = $user['id'];

            // 2. Cari apakah ada data terapis yang namanya cocok
            $terapis = $db->table('terapis')
                ->groupStart()
                    ->where('nama', $realname)
                    ->orWhere('nama', $username)
                ->groupEnd()
                ->get()
                ->getRowArray();

            if ($terapis) {
                // Jika sudah ada terapis yang cocok namanya, langsung sambungkan
                $db->table('users')
                    ->where('id', $userId)
                    ->update(['terapis_id' => $terapis['terapis_id']]);
            } else {
                // Jika belum ada, buatkan profil terapis baru otomatis dengan format ADM.xxxx
                $nextId = 1;
                $lastTerapis = $db->table('terapis')
                    ->where('terapis_id LIKE', 'ADM.%')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();
                if ($lastTerapis) {
                    $lastNum = (int) substr($lastTerapis['terapis_id'], 4);
                    $nextId = $lastNum + 1;
                }
                $gen_terapis_id = 'ADM.' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);

                // Cari default region
                $defaultRegionId = null;
                $firstRegion = $db->table('regions')->select('id')->get()->getRowArray();
                if ($firstRegion) {
                    $defaultRegionId = $firstRegion['id'];
                }

                // Cari default jabatan
                $defaultJabatanId = null;
                $firstJabatan = $db->table('jabatan')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
                if ($firstJabatan) {
                    $defaultJabatanId = $firstJabatan['id'];
                }

                // Masukkan data terapis baru
                $terapis_data = [
                    'terapis_id'      => $gen_terapis_id,
                    'nama'            => $realname,
                    'tempat_lahir'    => '-',
                    'tanggal_lahir'   => '2000-01-01',
                    'alamat'          => '-',
                    'region_id'       => $defaultRegionId,
                    'jabatan_id'      => $defaultJabatanId,
                    'rank'            => 'Junior',
                    'tgl_mulai_kerja' => date('Y-m-d H:i:s'),
                    'foto'            => null,
                    'is_active'       => 1
                ];

                $db->table('terapis')->insert($terapis_data);

                // Update users table
                $db->table('users')
                    ->where('id', $userId)
                    ->update(['terapis_id' => $gen_terapis_id]);
            }
        }

        // 3. Bersihkan/hapus relasi terapis untuk Owner dan Superadmin (jika ada yang terlanjur terhubung ke profil ADM.)
        $unwantedUsers = $db->table('users')
            ->whereIn('role', ['owner', 'superadmin'])
            ->where('terapis_id LIKE', 'ADM.%')
            ->get()
            ->getResultArray();

        foreach ($unwantedUsers as $unwanted) {
            $unwantedTerapisId = $unwanted['terapis_id'];
            
            // Unlink di tabel users
            $db->table('users')
                ->where('id', $unwanted['id'])
                ->update(['terapis_id' => null]);

            // Hapus di tabel terapis
            $db->table('terapis')
                ->where('terapis_id', $unwantedTerapisId)
                ->delete();
        }
    }

    public function down()
    {
        // Down migration is not strictly needed for data-fix migrations,
        // but we keep it empty to conform to standard CI4 migration structure.
    }
}
