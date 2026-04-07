<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'         => 1,
                'nama_jabatan'       => 'TERAPIS', // Sesuaikan jika kolom Anda 'nama_jabatan' atau 'nama_jabatan'
                'deskripsi'  => null,
                'created_at' => '2024-10-27 13:32:19',
                'updated_at' => '2024-10-27 13:32:19',
            ],
            [
                'id'         => 3,
                'nama_jabatan'       => 'ASISTEN TERAPIS',
                'deskripsi'  => null,
                'created_at' => '2024-10-27 13:36:24',
                'updated_at' => '2024-10-27 13:36:24',
            ],
            [
                'id'         => 4,
                'nama_jabatan'       => 'PENGURUS CABANG & TERAPIS',
                'deskripsi'  => null,
                'created_at' => '2024-10-27 13:36:48',
                'updated_at' => '2024-10-27 13:36:48',
            ],
            [
                'id'         => 5,
                'nama_jabatan'       => 'ADMIN',
                'deskripsi'  => null,
                'created_at' => '2024-10-27 13:38:50',
                'updated_at' => '2024-10-27 13:38:50',
            ],
            [
                'id'         => 6,
                'nama_jabatan'       => 'KEPALA ADMIN',
                'deskripsi'  => null,
                'created_at' => '2024-10-27 13:38:58',
                'updated_at' => '2024-10-27 13:38:58',
            ],
            [
                'id'         => 7,
                'nama_jabatan'       => 'IT',
                'deskripsi'  => null,
                'created_at' => '2024-10-29 16:34:37',
                'updated_at' => '2024-10-29 16:34:37',
            ],
            [
                'id'         => 8,
                'nama_jabatan'       => 'TERAPIS & DIGITAL MARKETING',
                'deskripsi'  => null,
                'created_at' => '2024-10-28 15:43:09',
                'updated_at' => '2024-10-28 15:43:09',
            ],
            [
                'id'         => 9,
                'nama_jabatan'       => 'TERAPIS & KEPALA TRAINER',
                'deskripsi'  => null,
                'created_at' => '2024-10-29 16:42:19',
                'updated_at' => '2024-10-29 16:42:19',
            ],
            [
                'id'         => 26,
                'nama_jabatan'       => 'Sugeng',
                'deskripsi'  => null,
                'created_at' => '2026-03-30 14:41:05',
                'updated_at' => '2026-03-30 14:41:05', // Diubah dari 0000-00-00 agar tidak error di MySQL Strict Mode
            ],

        ];
        $this->db->table('jabatan')->insertBatch($data);
    }
}
