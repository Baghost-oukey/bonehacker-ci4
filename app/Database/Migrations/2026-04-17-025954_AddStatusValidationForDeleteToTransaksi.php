<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusValidationForDeleteToTransaksi extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'canceled'],
                'default'    => 'active',
                'after'      => 'nominal',
            ],
            'cancel_reason' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'keterangan',
            ],
            'cancelled_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'created_by',
            ],
        ];
        $this->forge->addColumn('transaksi', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaksi', ['status', 'cancel_reason', 'cancelled_by']);
    }
}
