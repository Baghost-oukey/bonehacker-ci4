<?php
$host = '127.0.0.1';
$db   = 'bonehacker';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("ALTER TABLE users ADD COLUMN terapis_id VARCHAR(50) DEFAULT NULL");
    echo "Success: Column terapis_id added to users table.\n";
} catch (\PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Info: Column terapis_id already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
