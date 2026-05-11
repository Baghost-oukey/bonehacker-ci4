<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPresensiToTerapis extends Migration
{
    public function up()
    {
        $this->forge->addColumn('terapis', [
            'is_presensi' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=ikut presensi, 0=tidak ikut presensi',
                'after'      => 'is_active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('terapis', 'is_presensi');
    }
}
