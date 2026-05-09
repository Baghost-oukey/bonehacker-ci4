<?php
require 'vendor/autoload.php';
// Bootstrapping CI4
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$loader = require FCPATH . 'vendor/autoload.php';
$app = Config\Services::codeigniter();
$app->initialize();

$db = Config\Database::connect();
try {
    $db->query('ALTER TABLE users ADD COLUMN terapis_id VARCHAR(50) DEFAULT NULL');
    echo "Success: Column terapis_id added to users table.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}
