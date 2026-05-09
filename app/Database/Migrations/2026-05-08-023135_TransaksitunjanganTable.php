<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransaksitunjanganTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'terapis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tunjangan_karyawan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'nominal' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status_pembayaran' => [
                'type'       => 'ENUM',
                'constraint' => ['Belum Dibayar', 'Terbayar'],
                'default'    => 'Belum Dibayar',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tunjangan_karyawan_id', 'tunjangan_karyawan', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('transaksi_tunjangan');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi_tunjangan');
    }
}
