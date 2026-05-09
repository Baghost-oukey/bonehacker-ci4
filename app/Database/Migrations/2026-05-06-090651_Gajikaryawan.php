<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Gajikaryawan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'terapis_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipe_gaji'      => ['type' => 'ENUM', 'constraint' => ['harian', 'bulanan'], 'default' => 'harian'],
            'nominal_gaji'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('terapis_id', 'terapis', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('gaji_karyawan');
    }

    public function down()
    {
        $this->forge->dropTable('gaji_karyawan');
    }
}
