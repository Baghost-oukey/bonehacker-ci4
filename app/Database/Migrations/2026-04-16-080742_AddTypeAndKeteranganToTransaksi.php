<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeAndKeteranganToTransaksi extends Migration
{
    public function up()
    {
        $fields = [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['income', 'expense'],
                'default'    => 'income',
                'after'      => 'nominal', // Letakkan setelah kolom nominal
            ],
            'keterangan' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'type', // Letakkan setelah kolom type
            ],
        ];
        $this->forge->addColumn('transaksi', $fields);  
    }

    public function down()
    {
        $this->forge->dropColumn('transaksi', ['type', 'keterangan']);
    }
}
