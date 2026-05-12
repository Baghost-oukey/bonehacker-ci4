<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPotongAbsenToGajiKaryawan extends Migration
{
    public function up()
    {
        $fields = [
            'potong_absen' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'tipe_gaji'
            ]
        ];
        
        // Pengecekan dilakukan agar tidak error jika kolom sudah terlanjur terbuat
        if (!$this->db->fieldExists('potong_absen', 'gaji_karyawan')) {
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
