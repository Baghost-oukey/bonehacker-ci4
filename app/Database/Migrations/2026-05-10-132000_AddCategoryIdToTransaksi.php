<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryIdToTransaksi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transaksi', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'region_id',
            ],
        ]);
        $this->forge->addForeignKey('category_id', 'finance_categories', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropForeignKey('transaksi', 'transaksi_category_id_foreign');
        $this->forge->dropColumn('transaksi', 'category_id');
    }
}
