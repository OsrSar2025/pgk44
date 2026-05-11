<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    // ดึงข้อมูลเฉพาะ type = "เติมเงิน" จาก table history_payment (สำหรับ admin)
    $stmt = $pdo->prepare("SELECT * FROM history_payment WHERE type = ? ORDER BY date DESC");
    $stmt->execute(['เติมเงิน']);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $records
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
