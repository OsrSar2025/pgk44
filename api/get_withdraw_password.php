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
    $stmt = $pdo->prepare("SELECT withdrawal_code FROM password WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data && $data['withdrawal_code']) {
        echo json_encode([
            'success' => true, 
            'has_password' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'has_password' => false,
            'message' => 'No withdrawal code found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
