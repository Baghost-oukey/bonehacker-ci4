<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tunjangankaryawan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_tunjangan' => ['type' => 'VARCHAR', 'constraint' => 100],
            'kategori'       => ['type' => 'ENUM', 'constraint' => ['pemasukan', 'potongan'], 'default' => 'pemasukan'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tunjangan_karyawan');
    }

    public function down()
    {
        $this->forge->dropTable('tunjangan_karyawan');
    }
}
