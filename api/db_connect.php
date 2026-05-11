<?php
// Database connection
$host = 'localhost';
$dbname = 'titkok';
$username = 'root';
$password = '';

if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Set timezone to Asia/Bangkok (Thailand, UTC+7)
        $pdo->exec("SET time_zone = '+07:00'");
    } catch(PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}
?>
