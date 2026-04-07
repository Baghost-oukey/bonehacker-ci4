<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ComplaintTagsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'null'           => false,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('complaint_tags');
    }

    public function down()
    {
        $this->forge->dropTable('complaint_tags');
    }
}
