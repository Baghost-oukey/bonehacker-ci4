<?php

namespace App\Database;

use mysqli;

class CreateDatabase
{
  public static function run()
  {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'sample_migration_ci4';

    $conn = new mysqli($host, $user, $pass);

    if ($conn->connect_error) {
      die('Connection failed: ' . $conn->connect_error);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if ($conn->query($sql) === true) {
      echo "Database ensured.\n";
    } else {
      echo 'Error creating database: ' . $conn->error . "\n";
    }

    $conn->close();
  }
}
