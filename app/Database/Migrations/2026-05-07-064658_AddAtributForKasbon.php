<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAtributForKasbon extends Migration
{
    public function up()
    {
        $newFields = [
            'sisa_hutang' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'nominal'
            ],
        ];
        $this->forge->addColumn('kasbon_karyawan', $newFields);

        $modifyFields = [
            'status_potongan' => [
                'type'       => 'ENUM',
                'constraint' => ['belum_lunas', 'lunas'],
                'default'    => 'belum_lunas',
                'after'      => 'sisa_hutang'
            ],
        ];
        $this->forge->modifyColumn('kasbon_karyawan', $modifyFields);
    }

    public function down()
    {
        $modifyFields = [
            'status_potongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ];
        $this->forge->modifyColumn('kasbon_karyawan', $modifyFields);
        $this->forge->dropColumn('kasbon_karyawan', 'sisa_hutang');
    }
}
