<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixGajiKaryawanColumn extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('potong_absen', 'gaji_karyawan')) {
            $fields = [
                'potong_absen' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'tipe_gaji'
                ]
            ];
            $this->forge->addColumn('gaji_karyawan', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('potong_absen', 'gaji_karyawan')) {
            $this->forge->dropColumn('gaji_karyawan', 'potong_absen');
        }
    }
}
