<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $input['user_id'] ?? '';
    $amount = $input['amount'] ?? '';
    $date = $input['date'] ?? '';
    
    if (empty($userId) || empty($amount) || empty($date)) {
        echo json_encode([
            'success' => false,
            'message' => 'ข้อมูลไม่ครบถ้วน'
        ]);
        exit;
    }
    
    // อัพเดตสถานะในตาราง history_payment
    $stmt = $pdo->prepare("UPDATE history_payment SET status = 'completed' WHERE user_id = ? AND amount = ? AND date = ?");
    $result = $stmt->execute([$userId, $amount, $date]);
    
    if ($result) {
        // ตรวจสอบว่าผู้ใช้มีข้อมูลในตาราง balance หรือไม่
        $stmt = $pdo->prepare("SELECT user_id FROM balance WHERE user_id = ?");
        $stmt->execute([$userId]);
        $existingBalance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingBalance) {
            // ถ้ามีข้อมูลแล้ว ให้บวกยอดเงินในตาราง balance เท่านั้น
            $stmt = $pdo->prepare("UPDATE balance SET amount = amount + ? WHERE user_id = ?");
            $stmt->execute([$amount, $userId]);
        } else {
            // ถ้าไม่มีข้อมูล ให้เพิ่มข้อมูลใหม่ในตาราง balance
            // ดึงชื่อผู้ใช้จากตาราง user
            $stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $userName = $user ? $user['username'] : 'Unknown';
            
            $stmt = $pdo->prepare("INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $userName, $amount]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'อนุมัติการฝากเงินสำเร็จ'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถอัพเดตสถานะได้'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
