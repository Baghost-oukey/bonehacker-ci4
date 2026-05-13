<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNominalToMasterGaji extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tunjangan_karyawan', [
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'after'      => 'kategori',
                'comment'    => 'Nominal per bulan atau per hari',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['bulanan', 'harian'],
                'default'    => 'bulanan',
                'after'      => 'nominal',
                'comment'    => 'bulanan = tetap/bulan, harian = nominal x hari hadir',
            ],
            'terapis_ids' => [
                'type'    => 'TEXT',
                'null'    => true,
                'after'   => 'tipe',
                'comment' => 'JSON array ID terapis yang mendapat item ini',
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'terapis_ids',
                'comment'    => 'Cabang pemilik item ini (NULL = global)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tunjangan_karyawan', ['nominal', 'tipe', 'terapis_ids', 'region_id']);
    }
}
