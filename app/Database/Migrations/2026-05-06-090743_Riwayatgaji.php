<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Riwayatgaji extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'terapis_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'periode_bulan'     => ['type' => 'INT', 'constraint' => 2],
            'periode_tahun'     => ['type' => 'INT', 'constraint' => 4],
            'total_kehadiran'   => ['type' => 'INT', 'constraint' => 3, 'default' => 0],
            'gaji_pokok_total'  => ['type' => 'DECIMAL', 'constraint' => '15,2'], // Hasil kali hari jika harian
            'total_tunjangan'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'total_potongan'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'gaji_bersih'       => ['type' => 'DECIMAL', 'constraint' => '15,2'], // Take Home Pay
            'tanggal_bayar'     => ['type' => 'DATETIME', 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['draft', 'lunas'], 'default' => 'draft'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('terapis_id', 'terapis', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('riwayat_gaji');
    }

    public function down()
    {
        $this->forge->dropTable('riwayat_gaji');
    }
}
