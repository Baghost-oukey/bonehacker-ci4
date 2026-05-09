<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Kastable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_harian' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_pengeluaran' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'nominal_default' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Non-Aktif'],
                'default'    => 'Aktif',
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
        
        $this->forge->addKey('id_harian', true);
        $this->forge->createTable('kas');
    }

    public function down()
    {
        $this->forge->dropTable('kas');
    }
}
