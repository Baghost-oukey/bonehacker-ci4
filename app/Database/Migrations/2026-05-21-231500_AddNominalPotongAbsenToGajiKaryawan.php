<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNominalPotongAbsenToGajiKaryawan extends Migration
{
    public function up()
    {
        $fields = [
            'nominal_potong_absen' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'null'       => false,
                'after'      => 'potong_absen'
            ]
        ];
        
        if (!$this->db->fieldExists('nominal_potong_absen', 'gaji_karyawan')) {
            $this->forge->addColumn('gaji_karyawan', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('nominal_potong_absen', 'gaji_karyawan')) {
            $this->forge->dropColumn('gaji_karyawan', 'nominal_potong_absen');
        }
    }
}
