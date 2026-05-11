<?php
require_once 'db_connect.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Testing Database Connection</h2>";
echo "Host: localhost<br>";
echo "Database: titkok<br>";
echo "Username: root<br><br>";

try {
    // Use the connection from db_connect.php
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='color: green; font-weight: bold;'>✓ Connection Successful!</div><br>";
    
    // Test tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Tables in database 'titkok':</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check songs table
    if (in_array('songs', $tables)) {
        echo "<h3>Columns in 'songs' table:</h3>";
        $columns = $pdo->query("DESCRIBE songs")->fetchAll();
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>{$col['Field']} ({$col['Type']})</li>";
        }
        echo "</ul>";
    }
    
    // Check user table  
    if (in_array('user', $tables)) {
        echo "<h3>Columns in 'user' table:</h3>";
        $columns = $pdo->query("DESCRIBE user")->fetchAll();
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>{$col['Field']} ({$col['Type']})</li>";
        }
        echo "</ul>";
    }
    
} catch(PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>✗ Connection Failed!</div><br>";
    echo "Error: " . $e->getMessage();
}
?>
