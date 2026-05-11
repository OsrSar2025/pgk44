<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $userId = $input['user_id'] ?? '';
    $amount = $input['amount'] ?? 0;
    $date = $input['date'] ?? ''; // The original transaction date

    if (empty($userId) || empty($amount) || empty($date)) {
        echo json_encode([
            'success' => false,
            'message' => 'ข้อมูลไม่ครบถ้วน'
        ]);
        exit();
    }
    
    // อัพเดตสถานะในตาราง history_payment
    $stmt = $pdo->prepare("UPDATE history_payment SET status = 'completed' WHERE user_id = ? AND amount = ? AND date = ? AND type = 'ถอนเงิน'");
    $result = $stmt->execute([$userId, $amount, $date]);
    
    if ($result) {
        // ไม่ต้องอัพเดท number ในตาราง user (ตามที่ผู้ใช้ต้องการ)
        // ตรวจสอบว่าผู้ใช้มีข้อมูลในตาราง balance หรือไม่
        $stmt = $pdo->prepare("SELECT user_id FROM balance WHERE user_id = ?");
        $stmt->execute([$userId]);
        $existingBalance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingBalance) {
            // ถ้ามีข้อมูลแล้ว ให้ลดยอดเงินในตาราง balance เท่านั้น
            $stmt = $pdo->prepare("UPDATE balance SET amount = amount - ? WHERE user_id = ? AND amount >= ?");
            $stmt->execute([$amount, $userId, $amount]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'อนุมัติการถอนเงินสำเร็จ'
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
        'message' => $e->getMessage()
    ]);
}
?>
