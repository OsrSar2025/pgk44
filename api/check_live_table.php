<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'db_connect.php';

echo "=== ตรวจสอบตาราง LIVE ===\n\n";

// 1. ดูโครงสร้างตาราง
echo "1. โครงสร้างตาราง:\n";
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

echo "\n2. ข้อมูลปัจจุบัน (ID=10):\n";
$stmt = $pdo->prepare("SELECT * FROM live WHERE id = ?");
$stmt->execute([10]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);
if ($current) {
    foreach($current as $key => $value) {
        echo "$key: $value (type: " . gettype($value) . ")\n";
    }
} else {
    echo "ไม่พบข้อมูล ID=10\n";
}

echo "\n3. ทดสอบ UPDATE โดยตรง:\n";
$test_views = 999.999;
$stmt = $pdo->prepare("UPDATE live SET views = ? WHERE id = ?");
$result = $stmt->execute([$test_views, 10]);
echo "Execute result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
echo "Rows affected: " . $stmt->rowCount() . "\n";
echo "Error info: " . json_encode($stmt->errorInfo()) . "\n";

echo "\n4. ตรวจสอบข้อมูลหลัง UPDATE:\n";
$stmt = $pdo->prepare("SELECT views FROM live WHERE id = ?");
$stmt->execute([10]);
$after = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Views after update: " . $after['views'] . "\n";

echo "\n5. ตรวจสอบ MySQL Version:\n";
$stmt = $pdo->query("SELECT VERSION()");
$version = $stmt->fetch(PDO::FETCH_COLUMN);
echo "MySQL Version: $version\n";

echo "\n6. ตรวจสอบ SQL Mode:\n";
$stmt = $pdo->query("SELECT @@sql_mode");
$sql_mode = $stmt->fetch(PDO::FETCH_COLUMN);
echo "SQL Mode: $sql_mode\n";
?>

