<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminRoleToUsers extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['superadmin', 'user', 'owner', 'admin'],
                'default'    => 'user',
            ],
        ]);

        // Data Migration: Move management 'user' to 'admin'
        $this->db->table('users')
            ->where('role', 'user')
            ->groupStart()
                ->where('terapis_id', null)
                ->orWhere('terapis_id', '')
            ->groupEnd()
            ->update(['role' => 'admin']);

        // Data Migration: Ensure therapists are 'user'
        $this->db->table('users')
            ->where('terapis_id IS NOT NULL')
            ->where('terapis_id !=', '')
            ->update(['role' => 'user']);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['superadmin', 'user', 'owner'],
                'default'    => 'user',
            ],
        ]);
    }
}
