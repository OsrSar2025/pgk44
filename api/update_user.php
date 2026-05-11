<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $credit = $data['credit'] ?? '0';
    $status = $data['status'] ?? 'active';
    
    if (empty($id) || empty($username) || empty($password)) {
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
            $stmt = $pdo->prepare("UPDATE user SET username = ?, password = ?, number = ?, status = ? WHERE id = ?");
            $stmt->execute([$username, $password, $credit, $status, $id]);
        } else {
            // ถ้ายังไม่มีคอลัมน์ status ให้ใช้ query แบบเดิม
            $stmt = $pdo->prepare("UPDATE user SET username = ?, password = ?, number = ? WHERE id = ?");
            $stmt->execute([$username, $password, $credit, $id]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'แก้ไขข้อมูลสำเร็จ'
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
?>
