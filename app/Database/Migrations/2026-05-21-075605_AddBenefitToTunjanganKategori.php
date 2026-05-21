<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBenefitToTunjanganKategori extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE tunjangan_karyawan MODIFY COLUMN kategori ENUM('tunjangan','benefit','potongan') NOT NULL DEFAULT 'tunjangan'");
    }

    public function down()
    {
        // First delete any benefit rows to avoid foreign key/enum constraint violations on rollback
        $this->db->query("DELETE FROM tunjangan_karyawan WHERE kategori = 'benefit'");
        $this->db->query("ALTER TABLE tunjangan_karyawan MODIFY COLUMN kategori ENUM('tunjangan','potongan') NOT NULL DEFAULT 'tunjangan'");
    }
}
