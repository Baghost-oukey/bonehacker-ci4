<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Absensikaryawantable extends Migration
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Hadir', 'Tidak Hadir'],
                'default'    => 'Hadir',
            ],
            'keterangan' => [
                'type'       => 'TEXT',
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
        $this->forge->addForeignKey('terapis_id', 'terapis', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['terapis_id', 'tanggal']);
        $this->forge->createTable('absensi_karyawan');
        }
        
    public function down()
    {
        $this->forge->dropTable('absensi_karyawan');
    }
}
