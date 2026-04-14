<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnerTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'           => 'ENUM',
                'constraint'     => ['superadmin', 'user', 'owner'],
                'default'        => 'user',
            ],
        ]);
    }


    public function down()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'           => 'ENUM',
                'constraint'     => ['superadmin', 'user'],
                'default'        => 'user',
            ],
        ]);
    }
}
