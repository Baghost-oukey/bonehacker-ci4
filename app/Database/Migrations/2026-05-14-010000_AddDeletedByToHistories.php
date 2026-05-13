<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedByToHistories extends Migration
{
    public function up()
    {
        $this->forge->addColumn('histories', [
            'deleted_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'updated_by',
                'comment'    => 'User ID yang menghapus rekam medis',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('histories', 'deleted_by');
    }
}
