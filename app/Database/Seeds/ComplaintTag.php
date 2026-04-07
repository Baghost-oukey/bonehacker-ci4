<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ComplaintTag extends Seeder
{
    // Data Untuk Seeder Complaint Tag ada didalam Seeds > data > complaintTags_data
    public function run()
    {
        $filePath = APPPATH . 'Database/Seeds/data/complaintTags_data/complaint_tags.json';
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
            $this->db->table('complaint_tags')->insertBatch($batch);
            echo "Berhasil memasukkan Batch " . ($index + 1) . " (1000 data)\n";
        }

        echo "Selesai! 14.000 data complaint_tags berhasil masuk.\n";
    }
}
