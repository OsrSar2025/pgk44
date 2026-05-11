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
    // Include database connection
    require_once 'db_connect.php';
    
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($data['user_id']) ? trim($data['user_id']) : '';
    $username = isset($data['username']) ? trim($data['username']) : '';
    $withdrawal_code = isset($data['withdrawal_code']) ? trim($data['withdrawal_code']) : '';
    
    // Validation
    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้']);
        exit;
    }
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบชื่อผู้ใช้']);
        exit;
    }
    
    if (empty($withdrawal_code)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสผ่านการถอน']);
        exit;
    }
    
    if (strlen($withdrawal_code) < 4) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 4 หลัก']);
        exit;
    }
    
    // Check if user already has a withdrawal password
    $checkStmt = $pdo->prepare("SELECT id FROM password WHERE user_id = ?");
    $checkStmt->execute([$user_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing password
        $stmt = $pdo->prepare("UPDATE password SET withdrawal_code = ?, transaction_date = NOW() WHERE user_id = ?");
        $stmt->execute([$withdrawal_code, $user_id]);
    } else {
        // Insert new password
        $stmt = $pdo->prepare("INSERT INTO password (user_id, username, withdrawal_code, transaction_date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $username, $withdrawal_code]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกรหัสผ่านการถอนเรียบร้อยแล้ว'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>
