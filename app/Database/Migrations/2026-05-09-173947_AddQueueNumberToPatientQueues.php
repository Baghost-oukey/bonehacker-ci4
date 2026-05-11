<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQueueNumberToPatientQueues extends Migration
{
    public function up()
    {
        $fields = [
            'queue_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'region_id',
            ],
        ];
        $this->forge->addColumn('patient_queues', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('patient_queues', 'queue_number');
    }
}
