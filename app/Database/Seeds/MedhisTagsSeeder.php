<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MedhisTagsSeeder extends Seeder
{
    public function run()
    {
        // Data Untuk Seeder Medhis Tag ada didalam Seeds > data > MedhisTags_data

        $filePath = APPPATH . 'Database/Seeds/data/medhisTags_data/medhis_tags.json';
        $jsonString = file_get_contents($filePath);
        $decoded = json_decode($jsonString, true);

        if (isset($decoded[0]['data'])) {
            $allData = $decoded[0]['data'];
        } else {
            $allData = $decoded;
        }

        if (empty($allData)) {
            die("Gagal mengambil data. Pastikan struktur JSON benar.");
        }

        $chunks = array_chunk($allData, 1000);

        foreach ($chunks as $index => $batch) {
            $this->db->table('medhis_tags')->insertBatch($batch);
            echo "Berhasil memasukkan Batch " . ($index + 1) . " (1000 data)\n";
        }

        echo "Selesai! berhasil masuk.\n";
    }
}
