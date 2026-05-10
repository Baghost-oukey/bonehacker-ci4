<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimeConsumeToHistories extends Migration
{
    public function up()
    {
        $fields = [
            'time_consume' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'finish_at',
            ],
        ];
        $this->forge->addColumn('histories', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('histories', 'time_consume');
    }
}
