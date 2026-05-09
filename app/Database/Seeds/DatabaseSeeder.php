<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('CountriesSeeder');
        $this->call('RegionSeeder');
        $this->call('JabatanSeeder');
        $this->call('RecourceSeeder');
        $this->call('UsersSeeder');
        $this->call('PatientsSeeder');
        $this->call('TerapisSeeder');
        $this->call('ComplaintTag');
        $this->call('MedhisTagsSeeder');
        $this->call('ResultsTagsSeeder');
    }
}
