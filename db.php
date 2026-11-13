<?php

$config = require __DIR__ . '/db.local.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port=3306;dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password']
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

?>