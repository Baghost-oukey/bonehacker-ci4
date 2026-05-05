<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Revisitabletransaksi extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('transaksi', ['metode_pembayaran', 'rentang_usia']);

        $fields = [
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['pemasukan', 'pengeluaran', 'pengeluaran_harian'],
                'default'    => 'pemasukan',
                'after'      => 'type' 
            ],
        ];
        $this->forge->addColumn('transaksi', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaksi', 'kategori');
        $old_fields = [
            'metode_pembayaran' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash', 'Transfer', 'QRIS'],
                'null'       => true,
            ],
            'rentang_usia' => [
                'type'       => 'ENUM',
                'constraint' => ['Anak', 'Remaja', 'Dewasa', 'Lansia'],
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('transaksi', $old_fields);
    }
}
