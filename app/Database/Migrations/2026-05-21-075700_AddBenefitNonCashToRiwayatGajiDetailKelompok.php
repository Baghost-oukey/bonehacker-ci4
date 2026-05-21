<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBenefitNonCashToRiwayatGajiDetailKelompok extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE riwayat_gaji_detail MODIFY COLUMN kelompok ENUM('take_home','benefit','benefit_non_cash','potongan') NOT NULL");
    }

    public function down()
    {
        // First delete any benefit_non_cash rows to avoid enum constraint violations on rollback
        $this->db->query("DELETE FROM riwayat_gaji_detail WHERE kelompok = 'benefit_non_cash'");
        $this->db->query("ALTER TABLE riwayat_gaji_detail MODIFY COLUMN kelompok ENUM('take_home','benefit','potongan') NOT NULL");
    }
}
