<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'db_connect.php';

echo "=== ตาราง LIVE Structure ===\n\n";

$stmt = $pdo->query('DESCRIBE live');
printf("%-15s %-25s %-10s %-10s %-15s\n", 'Field', 'Type', 'Null', 'Key', 'Default');
echo str_repeat('-', 80) . "\n";

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("%-15s %-25s %-10s %-10s %-15s\n", 
        $row['Field'], 
        $row['Type'], 
        $row['Null'], 
        $row['Key'], 
        $row['Default'] ?? 'NULL'
    );
}

echo "\n=== Sample data (first 3 rows) ===\n\n";
$stmt = $pdo->query('SELECT id, title, views, image FROM live LIMIT 3');
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($data as $row) {
    echo "ID: {$row['id']}\n";
    echo "Title: {$row['title']}\n";
    echo "Views: {$row['views']} (type: " . gettype($row['views']) . ")\n";
    echo "Image: {$row['image']}\n";
    echo str_repeat('-', 50) . "\n";
}
?>

