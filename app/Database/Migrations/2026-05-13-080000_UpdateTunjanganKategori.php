<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTunjanganKategori extends Migration
{
    public function up()
    {
        // Ubah enum kategori tunjangan
        $this->db->query("ALTER TABLE tunjangan_karyawan MODIFY COLUMN kategori ENUM('penerimaan','benefit','potongan') NOT NULL DEFAULT 'penerimaan' COMMENT 'penerimaan=masuk take home, benefit=ditanggung perusahaan, potongan=dipotong dari gaji'");

        // Update data lama
        $this->db->query("UPDATE tunjangan_karyawan SET kategori = 'penerimaan' WHERE kategori = 'pemasukan'");

        // Buat tabel potongan_rutin
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'terapis_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_potongan' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Contoh: BPJS Kesehatan 1%'],
            'nominal' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('terapis_id');
        $this->forge->createTable('potongan_rutin');
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tunjangan_karyawan MODIFY COLUMN kategori ENUM('pemasukan','potongan') NOT NULL DEFAULT 'pemasukan'");
        $this->forge->dropTable('potongan_rutin');
    }
}
