<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$message = $data['message'] ?? '';

if (empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณากรอกข้อความแจ้งเตือน'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO notification (message, notification_date) VALUES (?, NOW())");
    $stmt->execute([$message]);
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มการแจ้งเตือนสำเร็จ'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
