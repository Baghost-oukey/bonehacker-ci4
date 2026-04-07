<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KejantananTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'history_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'ereksi'                 => ['type' => 'ENUM', 'constraint' => ['ya', 'tidak'], 'null' => true],
            'porno'                  => ['type' => 'ENUM', 'constraint' => ['ya', 'tidak'], 'null' => true],
            'frekuensi_porno'        => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'frekuensi_porno_lain'   => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'onani'                  => ['type' => 'ENUM', 'constraint' => ['ya', 'tidak'], 'null' => true],
            'frekuensi_onani'        => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'frekuensi_onani_lain'   => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'ranjang'                => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'frekuensi_ranjang'      => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'frekuensi_ranjang_lain' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'obat_kuat'              => ['type' => 'ENUM', 'constraint' => ['ya', 'tidak'], 'null' => true],
            'penyebab'               => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],

            // Field Otot dan Titik Saraf (TEXT)
            'otot_dada_perut_kanan' => ['type' => 'TEXT', 'null' => true],
            'otot_dada_perut_kiri'  => ['type' => 'TEXT', 'null' => true],
            'vital_kanan'           => ['type' => 'TEXT', 'null' => true],
            'vital_kiri'            => ['type' => 'TEXT', 'null' => true],
            'kelenjar_kanan'        => ['type' => 'TEXT', 'null' => true],
            'kelenjar_kiri'         => ['type' => 'TEXT', 'null' => true],
            'hormon_kanan'          => ['type' => 'TEXT', 'null' => true],
            'hormon_kiri'           => ['type' => 'TEXT', 'null' => true],
            'tulang_kering_kanan'   => ['type' => 'TEXT', 'null' => true],
            'tulang_kering_kiri'    => ['type' => 'TEXT', 'null' => true],
            'femur_dalam_kanan'     => ['type' => 'TEXT', 'null' => true],
            'femur_dalam_kiri'      => ['type' => 'TEXT', 'null' => true],
            'lingkar_perut_atas'    => ['type' => 'TEXT', 'null' => true],
            'lingkar_perut_bawah'   => ['type' => 'TEXT', 'null' => true],
            'lingkar_perut_kanan'   => ['type' => 'TEXT', 'null' => true],
            'lingkar_perut_kiri'    => ['type' => 'TEXT', 'null' => true],
            'cv4_kanan'             => ['type' => 'TEXT', 'null' => true],
            'cv4_kiri'              => ['type' => 'TEXT', 'null' => true],
            'cv6_kanan'             => ['type' => 'TEXT', 'null' => true],
            'cv6_kiri'              => ['type' => 'TEXT', 'null' => true],
            'l1_kanan'              => ['type' => 'TEXT', 'null' => true],
            'l1_kiri'               => ['type' => 'TEXT', 'null' => true],
            'l3_kanan'              => ['type' => 'TEXT', 'null' => true],
            'l3_kiri'               => ['type' => 'TEXT', 'null' => true],
            'piriformis_kanan'      => ['type' => 'TEXT', 'null' => true],
            'piriformis_kiri'       => ['type' => 'TEXT', 'null' => true],
            'sendok_kanan'          => ['type' => 'TEXT', 'null' => true],
            'sendok_kiri'           => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('history_id', 'histories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kejantanan');
    }

    public function down()
    {
        $this->forge->dropTable('kejantanan');
    }
}
