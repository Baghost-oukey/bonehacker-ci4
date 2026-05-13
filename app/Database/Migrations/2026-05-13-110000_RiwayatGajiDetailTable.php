<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RiwayatGajiDetailTable extends Migration
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
            'riwayat_gaji_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'FK ke riwayat_gaji',
            ],
            'kelompok' => [
                'type'       => 'ENUM',
                'constraint' => ['take_home', 'benefit', 'potongan'],
                'comment'    => 'Kelompok komponen gaji',
            ],
            'nama_komponen' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Nama item (snapshot saat gaji diproses)',
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('riwayat_gaji_id');
        $this->forge->createTable('riwayat_gaji_detail');
    }

    public function down()
    {
        $this->forge->dropTable('riwayat_gaji_detail');
    }
}
