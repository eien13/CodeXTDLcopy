<?php

$host = '127.0.0.1';
$dbname = 'task_db';
$username = 'YOUR_DATABASE_USERNAME'; // Replace with your database username
$password = 'YOUR_DATABASE_PASSWORD'; // Replace with your database password

try {
    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

?>