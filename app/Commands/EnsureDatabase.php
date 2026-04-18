<?php

namespace App\Commands;

use App\Database\CreateDatabase;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EnsureDatabase extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Database';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'db:ensure';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Create the application database if it does not already exist.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'db:ensure';

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
        CreateDatabase::run();
        CLI::write('Done: database is ensured.', 'green');
    }
}
