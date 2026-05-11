<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db_connect.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $adminId = trim($data['admin_id'] ?? '');
    $oldPassword = trim($data['old_password'] ?? '');
    $newPassword = trim($data['new_password'] ?? '');
    
    if (empty($adminId)) {
        throw new Exception('ไม่พบรหัสผู้ดูแลระบบ');
    }
    
    if (empty($oldPassword)) {
        throw new Exception('กรุณากรอกรหัสผ่านเดิม');
    }
    
    if (empty($newPassword)) {
        throw new Exception('กรุณากรอกรหัสผ่านใหม่');
    }
    
    if (strlen($newPassword) < 6) {
        throw new Exception('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
    }
    
    if ($oldPassword === $newPassword) {
        throw new Exception('รหัสผ่านใหม่ต้องแตกต่างจากรหัสผ่านเดิม');
    }
    
    $stmt = $pdo->prepare("SELECT id, username, password FROM user_admin WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        throw new Exception('ไม่พบผู้ดูแลระบบนี้ในระบบ');
    }
    
    if ($admin['password'] !== $oldPassword) {
        throw new Exception('รหัสผ่านเดิมไม่ถูกต้อง');
    }
    
    $stmt = $pdo->prepare("UPDATE user_admin SET password = ? WHERE id = ?");
    $result = $stmt->execute([$newPassword, $adminId]);
    
    if (!$result) {
        throw new Exception('ไม่สามารถเปลี่ยนรหัสผ่านได้');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
