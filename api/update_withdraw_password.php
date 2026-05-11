<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // รับข้อมูลจาก POST request
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? '';
    $oldPassword = $input['old_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        exit;
    }
    
    if (empty($oldPassword)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสผ่านเดิม']);
        exit;
    }
    
    if (empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสผ่านใหม่']);
        exit;
    }
    
    if (strlen($newPassword) < 4) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 4 หลัก']);
        exit;
    }
    
    if ($oldPassword === $newPassword) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ต้องแตกต่างจากรหัสผ่านเดิม']);
        exit;
    }
    
    // ตรวจสอบรหัสผ่านเดิม
    $stmt = $pdo->prepare("SELECT withdrawal_code FROM password WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentData || $currentData['withdrawal_code'] !== $oldPassword) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
        exit;
    }
    
    // อัปเดตรหัสผ่านใหม่
    $stmt = $pdo->prepare("UPDATE password SET withdrawal_code = ? WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $result = $stmt->execute([$newPassword, $userId]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'เปลี่ยนรหัสผ่านการถอนเรียบร้อยแล้ว'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>
