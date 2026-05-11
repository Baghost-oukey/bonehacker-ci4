<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class JaspelSettingsTable extends Migration
{
    public function up()
    {
        // Tabel pengaturan jaspel per cabang
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID cabang/wilayah',
            ],
            'nominal_per_pasien' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Nominal jaspel per pasien (akan dibagi ke terapis)',
            ],
            'terapis_ids' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array ID terapis yang berhak menerima jaspel',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=aktif, 0=nonaktif',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('region_id');
        $this->forge->addForeignKey('region_id', 'regions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('jaspel_settings');
    }

    public function down()
    {
        $this->forge->dropTable('jaspel_settings');
    }
}
