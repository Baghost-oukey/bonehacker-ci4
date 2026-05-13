<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameMasterTunjanganToMasterGaji extends Migration
{
    public function up()
    {
        // Ubah enum kategori: tunjangan | potongan
        $this->db->query("ALTER TABLE tunjangan_karyawan MODIFY COLUMN kategori ENUM('tunjangan','potongan') NOT NULL DEFAULT 'tunjangan'");
        // Tambah kolom nama_tampil untuk label di UI
        // (tabel tetap bernama tunjangan_karyawan agar tidak perlu rename FK)
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tunjangan_karyawan MODIFY COLUMN kategori ENUM('penerimaan','benefit','potongan') NOT NULL DEFAULT 'penerimaan'");
    }
}
