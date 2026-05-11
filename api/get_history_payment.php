<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

// รับข้อมูลจาก POST request
$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['user_id']) ? trim($input['user_id']) : '';

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'No user id']);
    exit;
}

try {
    // ดึงข้อมูลจาก table history_payment
    $stmt = $pdo->prepare("SELECT * FROM history_payment WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$userId]);
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
