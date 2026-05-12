<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KalenderTable extends Migration
{
    public function up()
    {
        // Tabel master kalender libur
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type'    => 'DATE',
                'comment' => 'Tanggal hari libur',
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Nama/keterangan hari libur',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['libur_rutin', 'libur_khusus'],
                'default'    => 'libur_khusus',
                'comment'    => 'libur_rutin = berulang tiap minggu, libur_khusus = tanggal tertentu',
            ],
            'hari_rutin' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'comment'    => '0=Minggu, 1=Senin, ..., 5=Jumat, 6=Sabtu (untuk libur_rutin)',
            ],
            'region_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL = global (superadmin), isi = kalender cabang',
            ],
            'tahun' => [
                'type'       => 'YEAR',
                'comment'    => 'Tahun berlaku kalender',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey(['region_id', 'tahun']);
        $this->forge->addKey('tanggal');
        $this->forge->createTable('kalender');
    }

    public function down()
    {
        $this->forge->dropTable('kalender');
    }
}
