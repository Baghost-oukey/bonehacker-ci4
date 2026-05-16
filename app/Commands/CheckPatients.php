<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckPatients extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'check:patients';
    protected $description = 'Check patients 120 and 7719 data';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Checking Patients 120 and 7719 ===', 'yellow');
        CLI::newLine();

        // Get patient 120
        $patient120 = $db->query("SELECT * FROM patients WHERE id = 120")->getRowArray();
        CLI::write('Patient ID 120:', 'green');
        if ($patient120) {
            foreach ($patient120 as $key => $value) {
                CLI::write("  $key: $value");
            }
        } else {
            CLI::write('Not found', 'red');
        }
        CLI::newLine();

        // Get patient 7719
        $patient7719 = $db->query("SELECT * FROM patients WHERE id = 7719")->getRowArray();
        CLI::write('Patient ID 7719:', 'green');
        if ($patient7719) {
            foreach ($patient7719 as $key => $value) {
                CLI::write("  $key: $value");
            }
        } else {
            CLI::write('Not found', 'red');
        }
        CLI::newLine();

        // Check if they have the same patient_id (string ID)
        if ($patient120 && $patient7719) {
            CLI::write('=== Comparison ===', 'yellow');
            CLI::write('Patient 120 - patient_id: ' . ($patient120['patient_id'] ?? 'NULL'));
            CLI::write('Patient 7719 - patient_id: ' . ($patient7719['patient_id'] ?? 'NULL'));
            
            if (isset($patient120['patient_id']) && isset($patient7719['patient_id']) && $patient120['patient_id'] === $patient7719['patient_id']) {
                CLI::write('⚠️ WARNING: Both patients have the same patient_id!', 'red');
            }
            CLI::newLine();
        }

        // Check histories for both patients
        CLI::write('=== Histories Count ===', 'yellow');
        $hist120 = $db->query("SELECT COUNT(*) as count FROM histories WHERE patient_id = 120")->getRow();
        CLI::write('Patient 120 has ' . $hist120->count . ' history records');

        $hist7719 = $db->query("SELECT COUNT(*) as count FROM histories WHERE patient_id = 7719")->getRow();
        CLI::write('Patient 7719 has ' . $hist7719->count . ' history records');
        CLI::newLine();

        // Check patient_queues
        CLI::write('=== Patient Queues Count ===', 'yellow');
        $queue120 = $db->query("SELECT COUNT(*) as count FROM patient_queues WHERE patient_id = 120")->getRow();
        CLI::write('Patient 120 has ' . $queue120->count . ' queue records');

        $queue7719 = $db->query("SELECT COUNT(*) as count FROM patient_queues WHERE patient_id = 7719")->getRow();
        CLI::write('Patient 7719 has ' . $queue7719->count . ' queue records');
    }
}
