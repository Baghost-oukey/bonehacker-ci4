<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportHistory extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Data Migration';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'data:import-history';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
       ini_set('memory_limit', '1024M'); 
        set_time_limit(0);

        $filePath = APPPATH . 'Database/Seeds/data/History_data/histories.json';

        if (!file_exists($filePath)) {
            CLI::error("File histories.json tidak ditemukan di: $filePath");
            return;
        }

        CLI::write('--- Membaca File History Besar (40MB+)... Mohon Tunggu ---', 'yellow');

        // 2. Baca file dan langsung bebaskan memori string-nya
        $jsonString = file_get_contents($filePath);
        $decoded = json_decode($jsonString, true);
        unset($jsonString);

        // 3. Ambil data inti dari pembungkus phpMyAdmin
        $rawData = (isset($decoded[0]['data'])) ? $decoded[0]['data'] : $decoded;
        unset($decoded);

        // 4. FILTERING: Buang metadata phpMyAdmin agar tidak terjadi error "Column count"
        $finalData = [];
        foreach ($rawData as $row) {
            // Hanya masukkan baris yang memiliki ID berupa angka (data asli)
            if (isset($row['id']) && is_numeric($row['id'])) {
                $finalData[] = $row;
            }
        }
        unset($rawData);

        $total = count($finalData);
        CLI::write("Total rekam medis valid ditemukan: $total", 'cyan');

        if ($total === 0) {
            CLI::error("Tidak ada data valid yang ditemukan untuk di-import.");
            return;
        }

        // 5. Eksekusi Batch Processing
        $db = \Config\Database::connect();
        $builder = $db->table('histories');

        // Batch per 500 baris karena kolom tabel history sangat banyak
        $chunks = array_chunk($finalData, 500);
        unset($finalData);

        $count = 0;
        CLI::write('--- Mulai Memindahkan Data ke Database ---', 'yellow');

        foreach ($chunks as $index => $batch) {
            // Gunakan ignore(true) untuk menghindari error jika ID sudah ada
            if ($builder->ignore(true)->insertBatch($batch)) {
                $count += count($batch);
                $percent = round(($count / $total) * 100);
                CLI::write("Progress: $percent% ($count / $total data)", 'green');
            } else {
                CLI::error("Gagal memasukkan batch ke-" . ($index + 1));
            }

            // Bersihkan siklus memori PHP setiap 5 kali batch
            if ($index % 5 == 0) {
                gc_collect_cycles();
            }
        }

        CLI::write('--- IMPORT HISTORY SELESAI DENGAN SUKSES! ---', 'white', 'green');
    
    }
}
