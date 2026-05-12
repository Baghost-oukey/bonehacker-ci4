<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLeaveManagement extends Migration
{
    public function up()
    {
        // 1. Update absensi_karyawan status enum
        $this->db->query("ALTER TABLE absensi_karyawan MODIFY COLUMN status ENUM('Hadir', 'Tidak Hadir', 'Izin', 'Cuti') NOT NULL DEFAULT 'Hadir'");

        // 2. Add jatah_cuti to terapis
        if (!$this->db->fieldExists('jatah_cuti', 'terapis')) {
            $this->forge->addColumn('terapis', [
                'jatah_cuti' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 12,
                    'after'      => 'tgl_mulai_kerja'
                ]
            ]);
        }

        // 3. Create cuti_karyawan table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'terapis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
            ],
            'jumlah_hari' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Disetujui', 'Ditolak', 'Menunggu'],
                'default'    => 'Disetujui',
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
        $this->forge->addForeignKey('terapis_id', 'terapis', 'id', 'CASCADE', 'CASCADE');
        
        // Cek apakah tabel sudah ada sebelum create
        if (!$this->db->tableExists('cuti_karyawan')) {
            $this->forge->createTable('cuti_karyawan');
        }
    }

    public function down()
    {
        $this->forge->dropTable('cuti_karyawan', true);
        $this->forge->dropColumn('terapis', 'jatah_cuti');
        $this->db->query("ALTER TABLE absensi_karyawan MODIFY COLUMN status ENUM('Hadir', 'Tidak Hadir') NOT NULL DEFAULT 'Hadir'");
    }
}
