<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportPatient extends BaseCommand
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
    protected $name = 'data:import-patient';

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

        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $filePath = APPPATH . 'Database/Seeds/data/Patients_data/patients.json';

        if (!file_exists($filePath)) {
            CLI::error("File tidak ditemukan di: $filePath");
            return;
        }

        CLI::write('--- Memulai Import Data Pasien ---', 'yellow');
        $jsonString = file_get_contents($filePath);
        $decoded = json_decode($jsonString, true);
        unset($jsonString);

        // Ambil data inti
        $rawData = (isset($decoded[0]['data'])) ? $decoded[0]['data'] : $decoded;
        unset($decoded);

        $finalData = [];
        foreach ($rawData as $row) {
            // Filter: Pastikan baris ini bukan header phpMyAdmin
            if (isset($row['id']) && is_numeric($row['id'])) {
                $finalData[] = $row;
            }
        }
        unset($rawData);
        // -------------------------

        $total = count($finalData);
        CLI::write("Total data pasien valid: $total", 'cyan');

        $chunks = array_chunk($finalData, 500);
        unset($finalData); // Segera hapus untuk bebaskan RAM

        $count = 0;
        $db = \Config\Database::connect();
        $builder = $db->table('patients');

        foreach ($chunks as $index => $batch) {
            if ($builder->insertBatch($batch)) {
                $count += count($batch);
                $percent = round(($count / $total) * 100);
                CLI::write("Progress: $percent% ($count / $total)", 'green');
            } else {
                CLI::error("Gagal memasukkan batch ke-" . ($index + 1));
            }
        }

        CLI::write('--- SELESAI! Seluruh Pasien Masuk ---', 'white', 'green');
    }
}
