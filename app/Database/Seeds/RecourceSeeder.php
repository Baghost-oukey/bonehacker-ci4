<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RecourceSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'         => 1,
                'nama'       => 'Teman/Kerabat',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 2,
                'nama'       => 'Facebook',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 3,
                'nama'       => 'Instagram',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 4,
                'nama'       => 'Tiktok',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 5,
                'nama'       => 'WhatsApp',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 6,
                'nama'       => 'Media Sosial Lainya',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 7,
                'nama'       => 'Google Maps',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
            [
                'id'         => 8,
                'nama'       => 'Google',
                'created_at' => '2024-10-25 08:35:41',
                'updated_at' => '2024-10-25 08:35:41',
            ],
        ];

        $this->db->table('resources')->insertBatch($data);
    }
}
