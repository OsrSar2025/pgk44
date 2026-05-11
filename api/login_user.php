<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($input['username']) ? trim($input['username']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'
        ]);
        exit;
    }
    
    try {
        // ตรวจสอบว่าคอลัมน์ status มีอยู่หรือไม่
        $checkColumn = $pdo->query("SHOW COLUMNS FROM `user` LIKE 'status'");
        $hasStatusColumn = $checkColumn->rowCount() > 0;
        
        // Check if user exists - ดึงข้อมูลทั้งหมดรวมถึง status (ถ้ามี)
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['password'] === $password) {
            // ตรวจสอบสถานะผู้ใช้ก่อน login (ถ้ามีคอลัมน์ status)
            if ($hasStatusColumn) {
                $status = $user['status'] ?? 'active';
                
                if ($status === 'banned') {
                    // ผู้ใช้ถูกบล็อก - ไม่อนุญาตให้ login
                    echo json_encode([
                        'success' => false,
                        'message' => 'บัญชีของคุณถูกบล็อก กรุณาติดต่อผู้ดูแลระบบ',
                        'banned' => true
                    ]);
                    exit;
                }
            }
            
            // Login successful
            echo json_encode([
                'success' => true,
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ]);
        } else {
            // Login failed
            echo json_encode([
                'success' => false,
                'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
            ]);
        }
    } catch (PDOException $e) {
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
