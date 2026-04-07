<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PatientsTable extends Migration
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
                'constraint' => '50',
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['Man', 'Woman'],
                'default'    => 'Man',
            ],
            'age' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '14',
                'null'       => true,
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'is_suspective' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_delete' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'country_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'domestic' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'patient_information' => [
                'type'       => 'VARCHAR',
                'constraint' => '25',
                'null'       => true,
            ],
            'ket_suspect' => [
                'type' => 'TEXT',
                'null' => true,
            ],

        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('patients');
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'SET NULL', 'CASCADE');
$this->forge->addForeignKey('country_id', 'country', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('patients');
    }
}
