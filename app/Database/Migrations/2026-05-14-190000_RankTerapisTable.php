<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RankTerapisTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('rank_terapis', true);

        $now = date('Y-m-d H:i:s');
        $this->db->table('rank_terapis')->insertBatch([
            ['name' => 'SS', 'description' => 'Rank SS', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'S', 'description' => 'Rank S', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'A', 'description' => 'Rank A', 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B', 'description' => 'Rank B', 'sort_order' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'C', 'description' => 'Rank C', 'sort_order' => 5, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('rank_terapis', true);
    }
}
