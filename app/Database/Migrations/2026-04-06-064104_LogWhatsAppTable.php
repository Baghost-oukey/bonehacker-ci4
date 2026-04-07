<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LogWhatsAppTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'history_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'null'           => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'message' => [
                'type'       => 'TEXT',
            ],
            'is_sent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'time_sent' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('log_whatsapp_messages');

        $this->forge->addForeignKey('history_id', 'histories', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('log_whatsapp_messages');
    }
}
