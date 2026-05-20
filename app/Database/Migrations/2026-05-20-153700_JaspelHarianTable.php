<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class JaspelHarianTable extends Migration
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
            'tanggal' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Tanggal perhitungan jaspel',
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Cabang wilayah',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['reguler', 'kejantanan'],
                'default'    => 'reguler',
                'null'       => false,
                'comment'    => 'Jenis layanan',
            ],
            'total_pasien' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah pasien yang selesai ditangani hari ini',
            ],
            'nominal_per_pasien' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'comment'    => 'Nominal jaspel per pasien dari setting',
            ],
            'total_jaspel' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0,
                'comment'    => 'Total pool jaspel = total_pasien * nominal_per_pasien',
            ],
            'terapis_hadir_ids' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array ID terapis yang hadir dan berhak',
            ],
            'jumlah_terapis_hadir' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah terapis yang hadir pada hari ini',
            ],
            'jaspel_per_terapis' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0,
                'comment'    => 'Jaspel per terapis = total_jaspel / jumlah_terapis_hadir',
            ],
            'is_processed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=aktif, 1=sudah terproses ke gaji',
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
        $this->forge->addKey('tanggal');
        $this->forge->addKey('region_id');
        $this->forge->addUniqueKey(['tanggal', 'region_id', 'tipe']); // 1 record per hari per wilayah per tipe
        $this->forge->createTable('jaspel_harian');
    }

    public function down()
    {
        $this->forge->dropTable('jaspel_harian');
    }
}
