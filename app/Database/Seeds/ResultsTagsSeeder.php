<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResultsTagsSeeder extends Seeder
{
    public function run()
    {
        // Data Untuk Seeder Results Tag ada didalam Seeds > data > ResultsTags_data
        $filePath = APPPATH . 'Database/Seeds/data/resultsTags_data/result_tags.json';
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
            $this->db->table('result_tags')->insertBatch($batch);
            echo "Berhasil memasukkan Batch " . ($index + 1) . " (1000 data)\n";
        }

        echo "Selesai! berhasil masuk.\n";
    }
}
