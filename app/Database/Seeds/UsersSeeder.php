<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['id' => 1, 'realname' => 'Super Admin', 'username' => 'superadmin', 'password' => '0e22c744a120eb10b544e6df8bf70fa4', 'role' => 'superadmin', 'regions_patient' => '[]', 'other_patient' => '[]', 'created_at' => '2024-09-09 13:05:59', 'updated_at' => '2024-10-13 09:17:06'],
            ['id' => 2, 'realname' => 'KIKI', 'username' => 'MAGELANG', 'password' => '0e22c744a120eb10b544e6df8bf70fa4', 'role' => 'admin', 'regions_patient' => '[21]', 'other_patient' => '[]', 'created_at' => '2024-09-09 15:02:41', 'updated_at' => '2024-11-01 08:05:19'],
            ['id' => 3, 'realname' => 'EGA', 'username' => 'PURBALINGGA', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[20]', 'other_patient' => '[]', 'created_at' => '2024-09-09 15:03:50', 'updated_at' => '2024-10-13 12:36:36'],
            ['id' => 4, 'realname' => 'TATI', 'username' => 'KEDUNGREJA', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[23]', 'other_patient' => '[]', 'created_at' => '2024-09-09 15:04:33', 'updated_at' => '2024-09-09 15:04:33'],
            ['id' => 6, 'realname' => 'ALEYA', 'username' => 'SEMARANG', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[2]', 'other_patient' => '[]', 'created_at' => '2024-09-19 18:14:55', 'updated_at' => '2024-09-19 18:14:55'],
            ['id' => 7, 'realname' => 'LIA', 'username' => 'PEMALANG', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[24]', 'other_patient' => '[]', 'created_at' => '2024-09-30 14:20:08', 'updated_at' => '2024-09-30 14:45:17'],
            ['id' => 8, 'realname' => 'IRMA', 'username' => 'PURWOKERTO', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[3]', 'other_patient' => '[29, 77]', 'created_at' => '2024-09-30 14:24:22', 'updated_at' => '2026-03-07 12:12:06'],
            ['id' => 10, 'realname' => 'KIKI IRMA', 'username' => 'WONOSOBO', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[19]', 'other_patient' => '[]', 'created_at' => '2024-10-12 11:51:48', 'updated_at' => '2024-10-12 11:57:24'],
            ['id' => 16, 'realname' => 'AQILA', 'username' => 'TSI JOGJA', 'password' => 'cc5fee17c5b78fb49d948fc4765b6a9e', 'role' => 'admin', 'regions_patient' => '[25]', 'other_patient' => '[]', 'created_at' => '2024-11-22 19:58:33', 'updated_at' => '2024-11-22 19:58:33'],
            ['id' => 17, 'realname' => 'Cilacap', 'username' => 'cilacap', 'password' => 'b3b07d0b5d53d79c90d3baee5261b79f', 'role' => 'admin', 'regions_patient' => '[7]', 'other_patient' => '[77]', 'created_at' => '2024-11-26 17:27:07', 'updated_at' => '2026-03-07 12:16:39'],
            ['id' => 18, 'realname' => 'JUWAR', 'username' => 'BATANG', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[22]', 'other_patient' => '[]', 'created_at' => '2025-01-28 13:43:27', 'updated_at' => '2026-01-03 11:48:19'],
            ['id' => 20, 'realname' => 'Pekalongan', 'username' => 'pekalongan', 'password' => '3dbdb8d533b3a50cbabbbb94a0afb594', 'role' => 'admin', 'regions_patient' => '[28]', 'other_patient' => '[]', 'created_at' => '2025-02-04 13:22:58', 'updated_at' => '2025-02-04 13:22:58'],
            ['id' => 21, 'realname' => 'KANG DANI', 'username' => 'TASIKMALAYA', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[27]', 'other_patient' => '[]', 'created_at' => '2025-03-10 12:41:10', 'updated_at' => '2025-03-10 12:41:10'],
            ['id' => 22, 'realname' => 'NADIA', 'username' => 'SURYO', 'password' => 'a8f4a3b80a40785d415912294d5d092e', 'role' => 'admin', 'regions_patient' => '[29]', 'other_patient' => '[]', 'created_at' => '2025-06-01 11:39:04', 'updated_at' => '2025-09-30 17:15:26'],
            ['id' => 23, 'realname' => 'GUSTI', 'username' => 'KEBUMEN', 'password' => '9011348d18f39c73af25b037b20f00cb', 'role' => 'admin', 'regions_patient' => '[14]', 'other_patient' => '[]', 'created_at' => '2025-11-01 18:08:42', 'updated_at' => '2025-11-01 18:08:42'],
            ['id' => 24, 'realname' => 'ADMIN SLEMAN', 'username' => 'SLEMAN SURYO', 'password' => '698a86f9b17e8261c403485dc76e23f3', 'role' => 'admin', 'regions_patient' => '[30]', 'other_patient' => '[]', 'created_at' => '2025-11-10 12:33:03', 'updated_at' => '2025-12-07 14:48:37'],
            ['id' => 25, 'realname' => 'KIKI', 'username' => 'BUMIAYU', 'password' => '0905a7cd43dfd8c81fc2f0131162e1bd', 'role' => 'admin', 'regions_patient' => '[31]', 'other_patient' => '[]', 'created_at' => '2025-11-17 11:33:47', 'updated_at' => '2025-11-17 11:33:47'],
            ['id' => 26, 'realname' => 'Super Admin', 'username' => 'superadmin2', 'password' => '3525b9ce06b066c75b141e9cfa91861c', 'role' => 'superadmin', 'regions_patient' => '[]', 'other_patient' => '[]', 'created_at' => '2024-09-09 13:05:59', 'updated_at' => '2024-10-13 09:17:06'],
            ['id' => 27, 'realname' => 'TEGAL', 'username' => 'TEGAL', 'password' => '4402c75b33a8485016ff1668ba5effbb', 'role' => 'admin', 'regions_patient' => '[32]', 'other_patient' => '[]', 'created_at' => '2026-02-28 14:30:07', 'updated_at' => '2026-04-01 11:43:17'],
            ['id' => 28, 'realname' => 'Agus', 'username' => 'agus_hitam', 'password' => '$2y$10$gUPT2vOz5CuLa3AfbfPkg.9dXDPZV3FL9NwdeTEhz1Q8N9AvONkRK', 'role' => 'admin', 'regions_patient' => '[11]', 'other_patient' => '[11]', 'created_at' => '2026-03-05 13:49:09', 'updated_at' => '2026-03-07 10:55:28'],
            ['id' => 31, 'realname' => 'admin', 'username' => 'admin', 'password' => '$2y$10$dE5UfE63wNfZITFqzvjh3.hGGmndEa3kZ7h4ie8GHOmovIIAxbocC', 'role' => 'superadmin', 'regions_patient' => '[]', 'other_patient' => '[]', 'created_at' => '2026-03-05 14:22:30', 'updated_at' => '2026-03-06 11:09:08'],
            ['id' => 32, 'realname' => 'pengguna', 'username' => 'users', 'password' => '$2y$10$0fV.b/qDpbdmnVLBMjNq0u14seLt0VKclewJqdRy1vEsnm8Gk6IDW', 'role' => 'admin', 'regions_patient' => '[7]', 'other_patient' => '[]', 'created_at' => '2026-03-05 15:13:27', 'updated_at' => '2026-03-05 15:13:27'],
            ['id' => 33, 'realname' => 'Jack Why', 'username' => 'jkwhy', 'password' => '$2y$10$GRTKV1BHukfPOk0jufs3Y.xM1AJyC/0LdUikBE/yo8X7kQqzwzkb6', 'role' => 'admin', 'regions_patient' => '[3]', 'other_patient' => '[]', 'created_at' => '2026-03-27 14:56:20', 'updated_at' => '2026-03-27 14:56:20'],
            ['id' => 34, 'realname' => 'Ambapenat', 'username' => 'Ambarapis', 'password' => '$2y$10$KTMvxU/xl1wBqvSPCAx66.VIu.KFoN6kOeztXrbuG723eR7Yd1eMi', 'role' => 'admin', 'regions_patient' => '[3]', 'other_patient' => '[17, 23, 29, 624, 270, 24]', 'created_at' => '2026-03-28 10:51:14', 'updated_at' => '2026-03-30 13:27:07'],
        ];
        $this->db->table('users')->insertBatch($data);
    }
}
