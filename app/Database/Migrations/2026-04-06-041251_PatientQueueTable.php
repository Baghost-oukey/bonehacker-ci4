<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PatientQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'             => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'queue_date' => [
                'type'           => 'DATE',
            ],
            'region_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'created_at' => [
                'type'           => 'TIMESTAMP',
                'default'        => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'           => 'TIMESTAMP',
                'default'        => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('patient_queues');
    }

    public function down()
    {
        $this->forge->dropTable('patient_queues');
    }
}
