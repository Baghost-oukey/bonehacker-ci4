<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToHistories extends Migration
{
    public function up()
    {
        // Menambahkan index pada kolom yang sering digunakan untuk filter dan agregasi
        $this->db->query("ALTER TABLE histories ADD INDEX idx_is_delete (is_delete)");
        $this->db->query("ALTER TABLE histories ADD INDEX idx_date (date)");
        $this->db->query("ALTER TABLE histories ADD INDEX idx_process_at (process_at)");
        $this->db->query("ALTER TABLE histories ADD INDEX idx_finish_at (finish_at)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE histories DROP INDEX idx_is_delete");
        $this->db->query("ALTER TABLE histories DROP INDEX idx_date");
        $this->db->query("ALTER TABLE histories DROP INDEX idx_process_at");
        $this->db->query("ALTER TABLE histories DROP INDEX idx_finish_at");
    }
}
