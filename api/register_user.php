<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
    ]);
    exit;
}

// Check if username already exists
try {
    $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว'
        ]);
        exit;
    }
    
    // Insert new user (number = เครดิตเริ่มต้น 0, status = active)
    $checkColumn = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'status'");
    $hasStatusColumn = $checkColumn->rowCount() > 0;
    
    if ($hasStatusColumn) {
        $stmt = $pdo->prepare("INSERT INTO user (username, password, number, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, '0', 'active']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user (username, password, number) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, '0']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'ลงทะเบียนสำเร็จ'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>
