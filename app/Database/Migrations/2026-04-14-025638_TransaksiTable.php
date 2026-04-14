<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransaksiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transaksi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'metode_pembayaran' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash', 'Transfer', 'QRIS'],
                'default'    => 'Cash',
            ],
            'rentang_usia' => [
                'type'       => 'ENUM',
                'constraint' => ['Anak', 'Remaja', 'Dewasa', 'Lansia'],
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id_transaksi', true);
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('transaksi');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi');
    }
}
