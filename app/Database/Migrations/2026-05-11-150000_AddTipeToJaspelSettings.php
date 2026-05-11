<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipeToJaspelSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('jaspel_settings', [
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['reguler', 'kejantanan'],
                'default'    => 'reguler',
                'after'      => 'region_id',
                'comment'    => 'Tipe jaspel: reguler atau kejantanan',
            ],
        ]);

        // Drop unique key lama jika ada, lalu buat unique key baru (region_id + tipe)
        // Ini mencegah duplikat setting untuk kombinasi cabang + tipe yang sama
        $this->db->query('ALTER TABLE jaspel_settings ADD UNIQUE KEY unique_region_tipe (region_id, tipe)');
    }

    public function down()
    {
        $this->forge->dropColumn('jaspel_settings', 'tipe');
    }
}
