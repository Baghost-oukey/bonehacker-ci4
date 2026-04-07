<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TerapisTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'terapis_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nama' => [
                'type' => 'TEXT',
            ],
            'tempat_lahir' => [
                'type' => 'TEXT',
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jabatan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'rank' => [
                'type' => 'TEXT',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tgl_mulai_kerja' => [
                // 'type'    => 'TIMESTAMP',
                'type'    => 'DATETIME',
                'null' => true,

                // 'default' => 'CURRENT_TIMESTAMP',
                // 'on update' => 'CURRENT_TIMESTAMP',
            ],
            'foto' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id', true);

        // Index
        $this->forge->addKey('terapis_id');
        $this->forge->addKey('region_id');
        $this->forge->addKey('jabatan_id');

        // (Optional tapi recommended) Foreign Key
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('jabatan_id', 'jabatan', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('terapis');
    }

    public function down()
    {
        $this->forge->dropTable('terapis');
    }
}
