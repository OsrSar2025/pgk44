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
    // เริ่ม transaction
    $pdo->beginTransaction();
    
    // ดึงข้อมูล credit ปัจจุบัน
    $stmt = $pdo->prepare("SELECT number FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $pdo->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบข้อมูลผู้ใช้'
        ]);
        exit;
    }
    
    // คำนวณ credit ใหม่
    $currentCredit = intval($user['number'] ? $user['number'] : 0);
    $MAX_CREDIT = 80;
    $LOGIN_BONUS = 10;
    
    // ตรวจสอบว่าคะแนนเครดิตยังไม่ถึง 80 หรือไม่
    if ($currentCredit >= $MAX_CREDIT) {
        // ถ้าถึง 80 แล้วไม่ต้องบวกต่อ
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Credit ถึงขีดจำกัดแล้ว (80)',
            'old_credit' => $currentCredit,
            'new_credit' => $currentCredit,
            'credit_updated' => false
        ]);
        exit;
    }
    
    // คำนวณ credit ใหม่ (เพิ่ม 10 แต่ไม่ให้เกิน 80)
    $newCredit = min($currentCredit + $LOGIN_BONUS, $MAX_CREDIT);
    
    // อัพเดท credit ใหม่
    $updateStmt = $pdo->prepare("UPDATE user SET number = ? WHERE id = ?");
    $updateStmt->execute([$newCredit, $user_id]);
    
    // commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่ม Credit สำเร็จ',
        'old_credit' => $currentCredit,
        'new_credit' => $newCredit,
        'credit_updated' => true
    ]);
    
} catch (PDOException $e) {
    // rollback transaction ในกรณีที่เกิดข้อผิดพลาด
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
