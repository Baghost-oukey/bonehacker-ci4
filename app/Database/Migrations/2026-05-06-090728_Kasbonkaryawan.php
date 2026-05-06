<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Kasbonkaryawan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'terapis_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal'         => ['type' => 'DATE'],
            'nominal'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'keterangan'      => ['type' => 'TEXT', 'null' => true],
            'status_potongan' => ['type' => 'ENUM', 'constraint' => ['belum_dipotong', 'sudah_dipotong'], 'default' => 'belum_dipotong'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('terapis_id', 'terapis', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kasbon_karyawan');
    }

    public function down()
    {
        $this->forge->dropTable('kasbon_karyawan');
    }
}
