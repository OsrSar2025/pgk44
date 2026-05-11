<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

try {
    // ดึงข้อมูลป้ายกำกับทั้งหมดจาก table labels
    $stmt = $pdo->query("SELECT user_id, label, label_color, created_at, updated_at FROM labels ORDER BY updated_at DESC");
    $labels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $labels
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
