<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

// รับข้อมูล JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID ไม่ถูกต้อง'
    ]);
    exit;
}

try {
    // ดึงข้อมูล credit (number) จาก table user
    $stmt = $pdo->prepare("SELECT number FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'credit' => $user['number'] ? $user['number'] : '0'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบข้อมูลผู้ใช้'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
