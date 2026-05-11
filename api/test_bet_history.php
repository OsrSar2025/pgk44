<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';

    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    echo "Database connection: OK\n";
    
    // ตรวจสอบตาราง bet_history
    $stmt = $pdo->query("SHOW TABLES LIKE 'bet_history'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "Table bet_history does not exist\n";
        exit;
    }
    
    echo "Table bet_history exists\n";
    
    // ตรวจสอบโครงสร้างตาราง
    $stmt = $pdo->query("DESCRIBE bet_history");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // ตรวจสอบจำนวนข้อมูล
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bet_history");
    $count = $stmt->fetch();
    echo "Total records: " . $count['count'] . "\n";
    
    // แสดงข้อมูล 5 รายการล่าสุด
    $stmt = $pdo->query("SELECT * FROM bet_history ORDER BY date DESC LIMIT 5");
    $recentBets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent 5 records:\n";
    foreach ($recentBets as $bet) {
        echo "- ID: " . $bet['id'] . ", User: " . $bet['user_id'] . ", Red: " . $bet['betting_red'] . ", Blue: " . $bet['betting_blue'] . ", Status: " . $bet['status'] . ", Date: " . $bet['date'] . "\n";
    }
    
    // ทดสอบ query ที่ใช้ใน API
    $testUserId = 24551; // ใช้ user_id ที่เห็นในรูป
    $stmt = $pdo->prepare("
        SELECT 
            id,
            betting_red,
            betting_blue,
            status,
            jackpot,
            date,
            (betting_red + betting_blue) as amount
        FROM bet_history 
        WHERE user_id = ? 
        ORDER BY date DESC 
        LIMIT 50
    ");
    $stmt->execute([$testUserId]);
    $testResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Test query result for user_id " . $testUserId . ":\n";
    echo "Found " . count($testResult) . " records\n";
    
    if (count($testResult) > 0) {
        echo "First record: " . json_encode($testResult[0]) . "\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
