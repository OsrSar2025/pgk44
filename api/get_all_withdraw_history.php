<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    // ดึงข้อมูลเฉพาะ type = "ถอนเงิน" จาก table history_payment พร้อม username จาก table user
    $stmt = $pdo->prepare("
        SELECT h.*, u.username 
        FROM history_payment h 
        LEFT JOIN user u ON h.user_id = u.id 
        WHERE h.type = ? 
        ORDER BY h.date DESC
    ");
    $stmt->execute(['ถอนเงิน']);
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
