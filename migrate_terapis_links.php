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
    
    // Get all therapists
    $stmt = $pdo->query("SELECT terapis_id FROM terapis");
    $terapis_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $count = 0;
    foreach ($terapis_ids as $id) {
        // Update user where username matches terapis_id
        $stmtUpdate = $pdo->prepare("UPDATE users SET terapis_id = ? WHERE username = ? AND terapis_id IS NULL");
        $stmtUpdate->execute([$id, $id]);
        $count += $stmtUpdate->rowCount();
    }
    
    echo "Success: Migrated $count users by matching username to terapis_id.\n";
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
