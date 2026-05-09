<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Jasapelayanantable extends Migration
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
            'history_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'terapis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kategori_layanan' => [
                'type'       => 'ENUM',
                'constraint' => ['Reguler', 'Kejantanan'],
                'default'    => 'Reguler',
            ],
            'tanggal_layanan' => [
                'type' => 'DATE',
            ],
            'is_delete' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('history_id');
        $this->forge->addKey('kategori_layanan');
        $this->forge->createTable('jasa_pelayanan');
    }

    public function down()
    {
        $this->forge->dropTable('jasa_pelayanan');
    }
}
