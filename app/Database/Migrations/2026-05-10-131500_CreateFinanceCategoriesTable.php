<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinanceCategoriesTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['income', 'expense'],
                'default'    => 'expense',
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_default' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('finance_categories');

        // Insert Default Categories
        $db = \Config\Database::connect();
        $data = [
            ['name' => 'Pendapatan Layanan', 'type' => 'income', 'is_default' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Gaji Karyawan', 'type' => 'expense', 'is_default' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Sewa Gedung', 'type' => 'expense', 'is_default' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Listrik & Air', 'type' => 'expense', 'is_default' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Alat & Bahan Medis', 'type' => 'expense', 'is_default' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Lain-lain', 'type' => 'expense', 'is_default' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $db->table('finance_categories')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('finance_categories');
    }
}
