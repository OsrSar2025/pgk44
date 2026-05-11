<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? '';
$message = $data['message'] ?? '';

if (empty($id) || empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'ข้อมูลไม่ครบถ้วน'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE notification SET message = ?, notification_date = NOW() WHERE id = ?");
    $stmt->execute([$message, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขการแจ้งเตือนสำเร็จ'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
