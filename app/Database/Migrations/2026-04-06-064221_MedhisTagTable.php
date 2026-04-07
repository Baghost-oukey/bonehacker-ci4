<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MedhisTagTable extends Migration
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
                'constraint' => '100',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('medhis_tags');
    }
    

    public function down()
    {
        $this->forge->dropTable('medhis_tags');
    }
}
