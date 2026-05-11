<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $credit = $data['credit'] ?? '0';
    $status = $data['status'] ?? 'active'; // ค่าเริ่มต้นเป็น 'active'
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ]);
        exit();
    }
    
    try {
        // ตรวจสอบว่าคอลัมน์ status มีอยู่หรือไม่
        $checkColumn = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'status'");
        $hasStatusColumn = $checkColumn->rowCount() > 0;
        
        if ($hasStatusColumn) {
            $stmt = $pdo->prepare("INSERT INTO user (username, password, number, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $credit, $status]);
        } else {
            // ถ้ายังไม่มีคอลัมน์ status ให้ใช้ query แบบเดิม
            $stmt = $pdo->prepare("INSERT INTO user (username, password, number) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $credit]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'เพิ่มผู้ใช้สำเร็จ',
            'id' => $pdo->lastInsertId()
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
?>
