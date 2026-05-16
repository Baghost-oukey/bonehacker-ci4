<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalizeUserRoleRename extends Migration
{
    public function up()
    {
        // 1. First, expand the ENUM to include both 'terapis' and 'user'
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'admin', 'terapis', 'user') DEFAULT 'user'");
        
        // 2. Update all existing 'terapis' records to 'user'
        $this->db->table('users')->where('role', 'terapis')->update(['role' => 'user']);
        
        // 3. Now remove 'terapis' from the ENUM completely
        // Final allowed roles: superadmin, owner, admin, user
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'admin', 'user') DEFAULT 'user'");

        // 4. Double check terapis_id exists
        if (!$this->db->fieldExists('terapis_id', 'users')) {
            $this->forge->addColumn('users', [
                'terapis_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'password'
                ]
            ]);
        }
    }

    public function down()
    {
        // To revert, we have to add 'terapis' back and move users with terapis_id
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'admin', 'user', 'terapis') DEFAULT 'user'");
        
        $this->db->table('users')
            ->where('role', 'user')
            ->where('terapis_id IS NOT NULL')
            ->where('terapis_id !=', '')
            ->update(['role' => 'terapis']);
            
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'admin', 'terapis') DEFAULT 'terapis'");
    }
}
