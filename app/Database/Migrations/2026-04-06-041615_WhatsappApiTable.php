<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WhatsappApiTable extends Migration
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
            'url_api' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'instance_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => true,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
            'message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('whatsapp_api');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_api');
    }
}
