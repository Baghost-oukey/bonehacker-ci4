<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FindDuplicates extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'find:duplicates';
    protected $description = 'Find duplicate patients by name or phone';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Finding Duplicate Patients ===', 'yellow');
        CLI::newLine();

        // Find patients with similar names (TURSINA)
        CLI::write('Searching for patients with "TURSINA" in name...', 'green');
        $tursina = $db->query("SELECT id, name, phone, region_id, created_at, updated_at FROM patients WHERE name LIKE '%TURSINA%' ORDER BY id")->getResultArray();
        
        if ($tursina) {
            foreach ($tursina as $p) {
                CLI::write("ID: {$p['id']} | Name: {$p['name']} | Phone: {$p['phone']} | Region: {$p['region_id']}");
            }
        } else {
            CLI::write('No patients found', 'red');
        }
        CLI::newLine();

        // Find patients with similar phone numbers
        CLI::write('Searching for patients with phone starting with "62838"...', 'green');
        $phones = $db->query("SELECT id, name, phone, region_id FROM patients WHERE phone LIKE '62838%' ORDER BY phone")->getResultArray();
        
        if ($phones) {
            foreach ($phones as $p) {
                CLI::write("ID: {$p['id']} | Name: {$p['name']} | Phone: {$p['phone']} | Region: {$p['region_id']}");
            }
        } else {
            CLI::write('No patients found', 'red');
        }
        CLI::newLine();

        // Check for duplicate names
        CLI::write('=== Checking for Duplicate Names ===', 'yellow');
        $duplicateNames = $db->query("
            SELECT name, COUNT(*) as count, GROUP_CONCAT(id) as ids 
            FROM patients 
            WHERE is_delete = 0 
            GROUP BY name 
            HAVING count > 1 
            ORDER BY count DESC 
            LIMIT 20
        ")->getResultArray();

        if ($duplicateNames) {
            CLI::write('Found ' . count($duplicateNames) . ' duplicate names:', 'red');
            foreach ($duplicateNames as $dup) {
                CLI::write("  Name: {$dup['name']} | Count: {$dup['count']} | IDs: {$dup['ids']}");
            }
        } else {
            CLI::write('No duplicate names found', 'green');
        }
        CLI::newLine();

        // Check for duplicate phones
        CLI::write('=== Checking for Duplicate Phone Numbers ===', 'yellow');
        $duplicatePhones = $db->query("
            SELECT phone, COUNT(*) as count, GROUP_CONCAT(id) as ids 
            FROM patients 
            WHERE is_delete = 0 AND phone IS NOT NULL AND phone != '' 
            GROUP BY phone 
            HAVING count > 1 
            ORDER BY count DESC 
            LIMIT 20
        ")->getResultArray();

        if ($duplicatePhones) {
            CLI::write('Found ' . count($duplicatePhones) . ' duplicate phone numbers:', 'red');
            foreach ($duplicatePhones as $dup) {
                CLI::write("  Phone: {$dup['phone']} | Count: {$dup['count']} | IDs: {$dup['ids']}");
            }
        } else {
            CLI::write('No duplicate phone numbers found', 'green');
        }
    }
}
