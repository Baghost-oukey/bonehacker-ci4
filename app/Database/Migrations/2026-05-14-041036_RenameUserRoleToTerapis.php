<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameUserRoleToTerapis extends Migration
{
    public function up()
    {
        // 1. Expand the ENUM to include 'terapis'
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'user', 'owner', 'admin', 'terapis') DEFAULT 'terapis'");
        
        // 2. Update existing 'user' roles to 'terapis'
        $this->db->table('users')->where('role', 'user')->update(['role' => 'terapis']);
        
        // 3. Remove 'user' from the ENUM constraint
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'owner', 'admin', 'terapis') DEFAULT 'terapis'");
    }

    public function down()
    {
        // 1. Expand the ENUM to include 'user' again
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'user', 'owner', 'admin', 'terapis') DEFAULT 'user'");
        
        // 2. Revert 'terapis' roles back to 'user'
        $this->db->table('users')->where('role', 'terapis')->update(['role' => 'user']);
        
        // 3. Remove 'terapis' from the ENUM constraint
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'user', 'owner', 'admin') DEFAULT 'user'");
    }
}
