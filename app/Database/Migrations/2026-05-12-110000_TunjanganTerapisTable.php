<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TunjanganTerapisTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'terapis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tunjangan_karyawan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Nominal per bulan atau per hari tergantung tipe',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['bulanan', 'harian'],
                'default'    => 'bulanan',
                'comment'    => 'bulanan = tetap per bulan, harian = nominal x hari hadir',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['terapis_id', 'tunjangan_karyawan_id']);
        $this->forge->createTable('tunjangan_terapis');
    }

    public function down()
    {
        $this->forge->dropTable('tunjangan_terapis');
    }
}
