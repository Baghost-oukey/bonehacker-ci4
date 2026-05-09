<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run()
    {
        
        $data = [
            ['id' => 2,  'name' => 'SEMARANG', 'created_at' => '2024-01-06 15:46:50', 'updated_at' => '2024-01-06 15:46:50'],
            ['id' => 3,  'name' => 'PURWOKERTO', 'created_at' => '2024-01-06 15:47:41', 'updated_at' => '2024-01-06 15:47:41'],
            ['id' => 5,  'name' => 'PURBALINGGA', 'created_at' => '2024-01-06 15:56:52', 'updated_at' => '2024-01-06 15:57:44'],
            ['id' => 6,  'name' => 'BANJARNEGARA', 'created_at' => '2024-01-06 15:58:03', 'updated_at' => '2024-01-06 15:58:03'],
            ['id' => 7,  'name' => 'CILACAP', 'created_at' => '2024-01-06 15:58:11', 'updated_at' => '2024-01-06 15:58:11'],
            ['id' => 8,  'name' => 'PANGANDARAN', 'created_at' => '2024-01-06 15:58:18', 'updated_at' => '2024-01-06 15:58:18'],
            ['id' => 9,  'name' => 'SUMEDANG', 'created_at' => '2024-01-06 15:58:24', 'updated_at' => '2024-01-06 15:58:24'],
            ['id' => 10, 'name' => 'PEMALANG', 'created_at' => '2024-01-06 15:58:28', 'updated_at' => '2024-01-06 15:58:28'],
            ['id' => 11, 'name' => 'BREBES', 'created_at' => '2024-01-06 15:58:34', 'updated_at' => '2024-01-06 15:58:34'],
            ['id' => 12, 'name' => 'CIAMIS', 'created_at' => '2024-01-06 16:13:54', 'updated_at' => '2024-01-06 16:13:54'],
            ['id' => 13, 'name' => 'WONOSOBO', 'created_at' => '2024-01-06 16:22:22', 'updated_at' => '2024-01-06 16:22:22'],
            ['id' => 14, 'name' => 'KEBUMEN', 'created_at' => '2024-01-06 16:45:22', 'updated_at' => '2024-01-06 16:45:22'],
            ['id' => 15, 'name' => 'PESERTA TRAINING', 'created_at' => '2024-01-08 19:56:08', 'updated_at' => '2024-01-08 19:56:08'],
            ['id' => 16, 'name' => 'ROADSHOW KEDUNGREJA', 'created_at' => '2024-01-24 18:37:36', 'updated_at' => '2024-01-24 18:37:36'],
            ['id' => 17, 'name' => 'ROADSHOW BANJAR', 'created_at' => '2024-03-02 11:24:48', 'updated_at' => '2024-03-02 11:24:48'],
            ['id' => 18, 'name' => 'ROADSHOW SIDAREJA', 'created_at' => '2024-03-14 13:55:05', 'updated_at' => '2024-03-14 13:55:05'],
            ['id' => 19, 'name' => 'ROADSHOW WONOSOBO', 'created_at' => '2024-03-30 05:24:41', 'updated_at' => '2024-03-30 05:24:41'],
            ['id' => 20, 'name' => 'PRAKTEK MAS YOGI', 'created_at' => '2024-04-07 16:37:32', 'updated_at' => '2024-04-07 16:37:32'],
            ['id' => 21, 'name' => 'ROADSHOW MAGELANG', 'created_at' => '2024-04-23 19:27:13', 'updated_at' => '2024-04-23 19:27:13'],
            ['id' => 22, 'name' => 'BATANG', 'created_at' => '2024-07-28 15:26:21', 'updated_at' => '2024-07-28 15:26:21'],
            ['id' => 23, 'name' => 'KEDUNGREJA CILACAP', 'created_at' => '2024-09-03 09:23:00', 'updated_at' => '2024-09-03 10:16:55'],
            ['id' => 24, 'name' => 'PEMALANG YOGI', 'created_at' => '2024-09-30 14:20:23', 'updated_at' => '2024-10-01 11:46:12'],
            ['id' => 25, 'name' => 'TSI JOGJA', 'created_at' => '2024-11-22 19:56:42', 'updated_at' => '2024-11-22 19:56:42'],
            ['id' => 27, 'name' => 'TASIKMALAYA', 'created_at' => '2025-01-28 14:18:56', 'updated_at' => '2025-01-28 14:18:56'],
            ['id' => 28, 'name' => 'PEKALONGAN', 'created_at' => '2025-02-04 09:45:38', 'updated_at' => '2025-02-04 09:45:38'],
            ['id' => 29, 'name' => 'SURYO', 'created_at' => '2025-06-01 11:39:04', 'updated_at' => '2025-06-01 11:39:04'],
            ['id' => 31, 'name' => 'BUMIAYU', 'created_at' => '2025-11-17 11:33:47', 'updated_at' => '2025-11-17 11:33:47'],
        ];
        $this->db->table('regions')->insertBatch($data);
    }
}
