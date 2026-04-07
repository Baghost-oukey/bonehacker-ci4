<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'patient_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // Harus unsigned agar match dengan PK patients
            'terapis_id'        => ['type' => 'TEXT', 'null' => true],
            'complaint'         => ['type' => 'TEXT', 'null' => true],
            'medhis'            => ['type' => 'TEXT', 'null' => true],
            'checkup'           => ['type' => 'TEXT', 'null' => true],
            'cervical'          => ['type' => 'TEXT', 'null' => true],
            'thoraxal'          => ['type' => 'TEXT', 'null' => true],
            'lumbar'            => ['type' => 'TEXT', 'null' => true],
            'sacrum'            => ['type' => 'TEXT', 'null' => true],
            'sacral'            => ['type' => 'TEXT', 'null' => true],
            'pelvis'            => ['type' => 'TEXT', 'null' => true],
            'plintiran'         => ['type' => 'TEXT', 'null' => true],
            'kompresi'          => ['type' => 'TEXT', 'null' => true],
            'verteba'           => ['type' => 'TEXT', 'null' => true],
            'thorax'            => ['type' => 'TEXT', 'null' => true],
            'visualfoot'        => ['type' => 'TEXT', 'null' => true],
            'other'             => ['type' => 'TEXT', 'null' => true],
            'results'           => ['type' => 'TEXT', 'null' => true],
            'measure'           => ['type' => 'TEXT', 'null' => true],
            'date'              => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'pubis'             => ['type' => 'TEXT', 'null' => true],
            'tensi'             => ['type' => 'TEXT', 'null' => true],
            'power'             => ['type' => 'TEXT', 'null' => true],
            'pr'                => ['type' => 'TEXT', 'null' => true],
            'keterangan_verteba'    => ['type' => 'TEXT', 'null' => true],
            'keterangan_thorax'     => ['type' => 'TEXT', 'null' => true],
            'keterangan_kompresi'   => ['type' => 'TEXT', 'null' => true],
            'keterangan_plintiran'  => ['type' => 'TEXT', 'null' => true],
            'keterangan_visualfoot' => ['type' => 'TEXT', 'null' => true],
            'date_modified'     => [
                'type' => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
            ],
            'is_delete'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => true],
            'created_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'kejantanan'        => ['type' => 'ENUM', 'constraint' => ['ya', 'tidak'], 'null' => true],
            'history_region'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'patient_queue_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'process_at'        => ['type' => 'DATETIME', 'null' => true],
            'finish_at'         => ['type' => 'DATETIME', 'null' => true],
            'type'              => ['type' => 'ENUM', 'constraint' => ['draft', 'posted'], 'default' => 'posted', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('history_region', 'regions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('patient_queue_id', 'patient_queues', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('histories');
    }

    public function down()
    {
        $this->forge->dropTable('histories');
    }
}
