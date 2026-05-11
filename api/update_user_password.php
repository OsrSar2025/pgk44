<?php
// Prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once 'db_connect.php';
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log received data
    error_log('Update Password API - Received data: ' . json_encode($data));
    
    $userId = trim($data['user_id'] ?? '');
    $oldPassword = trim($data['old_password'] ?? '');
    $newPassword = trim($data['new_password'] ?? '');
    
    error_log("Update Password - User ID: $userId");
    
    // Validation
    if (empty($userId)) {
        throw new Exception('ไม่พบรหัสผู้ใช้');
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
    
    // Get current user data
    $stmt = $pdo->prepare("SELECT id, username, password FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Found user: " . json_encode($user));
    
    if (!$user) {
        throw new Exception('ไม่พบผู้ใช้นี้ในระบบ');
    }
    
    // Verify old password
    if ($user['password'] !== $oldPassword) {
        error_log("Password mismatch - DB: {$user['password']}, Input: $oldPassword");
        throw new Exception('รหัสผ่านเดิมไม่ถูกต้อง');
    }
    
    // Update password
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
    $result = $stmt->execute([$newPassword, $userId]);
    
    if (!$result) {
        throw new Exception('ไม่สามารถเปลี่ยนรหัสผ่านได้');
    }
    
    $rowCount = $stmt->rowCount();
    error_log("Password updated - Rows affected: $rowCount");
    
    // Verify update
    $stmt = $pdo->prepare("SELECT id, username FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว',
        'rows_affected' => $rowCount
    ]);
    
} catch (PDOException $e) {
    error_log('Update Password PDO Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล'
    ]);
} catch (Exception $e) {
    error_log('Update Password Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
