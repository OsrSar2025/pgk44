<?php
/**
 * อัปเดตสถานะผู้ใช้ (Block/Unblock)
 */

header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? '';
    $status = $data['status'] ?? '';
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($id) || empty($status)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ]);
        exit();
    }
    
    // ตรวจสอบสถานะที่อนุญาต
    $allowedStatuses = ['active', 'inactive', 'banned'];
    if (!in_array($status, $allowedStatuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'สถานะไม่ถูกต้อง'
        ]);
        exit();
    }
    
    try {
        // ตรวจสอบว่าคอลัมน์ status มีอยู่หรือไม่
        $checkColumn = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'status'");
        $hasStatusColumn = $checkColumn->rowCount() > 0;
        
        if (!$hasStatusColumn) {
            echo json_encode([
                'success' => false,
                'message' => 'คอลัมน์ status ยังไม่มีในฐานข้อมูล กรุณารัน migration script ก่อน',
                'action_required' => 'run add_status_to_user_table.php'
            ]);
            exit();
        }
        
        // ตรวจสอบว่าผู้ใช้มีอยู่จริงหรือไม่
        $checkUser = $pdo->prepare("SELECT id, username FROM user WHERE id = ?");
        $checkUser->execute([$id]);
        $user = $checkUser->fetch();
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบผู้ใช้ที่ต้องการแก้ไข'
            ]);
            exit();
        }
        
        // อัปเดตสถานะ
        $stmt = $pdo->prepare("UPDATE user SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);
        
        if ($result) {
            $statusText = $status === 'banned' ? 'บล็อก' : ($status === 'active' ? 'ปลดบล็อก' : 'เปลี่ยนสถานะ');
            echo json_encode([
                'success' => true,
                'message' => "{$statusText}ผู้ใช้สำเร็จ",
                'user_id' => $id,
                'username' => $user['username'],
                'new_status' => $status
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถอัปเดตสถานะได้'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>

