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
    
    // Check regions table columns
    $stmt = $pdo->query("DESCRIBE regions");
    $columns = $stmt->fetchAll();
    
    echo "Columns in regions table:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
