<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? null;
    $user_name = $input['user_name'] ?? null;
    $amount = $input['amount'] ?? null;
    
    if (!$user_id || !$user_name || !$amount) {
        echo json_encode([
            'success' => false,
            'message' => 'ข้อมูลไม่ครบถ้วน'
        ]);
        exit;
    }
    
    if ($amount <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'จำนวนเงินต้องมากกว่า 0'
        ]);
        exit;
    }
    
    try {
        // ตรวจสอบยอดเงินใน balance ก่อน
        $balanceStmt = $pdo->prepare("SELECT amount FROM balance WHERE user_id = ?");
        $balanceStmt->execute([$user_id]);
        $balanceData = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$balanceData) {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบข้อมูลยอดเงินในระบบ'
            ]);
            exit;
        }
        
        $currentBalance = $balanceData['amount'];
        
        if ($currentBalance < $amount) {
            echo json_encode([
                'success' => false,
                'message' => 'ยอดเงินไม่เพียงพอสำหรับการบริจาค'
            ]);
            exit;
        }
        
        // เริ่ม transaction
        $pdo->beginTransaction();
        
        // ตรวจสอบว่าผู้ใช้มีข้อมูลใน table donate หรือไม่
        $checkStmt = $pdo->prepare("SELECT * FROM donate WHERE user_id = ?");
        $checkStmt->execute([$user_id]);
        $existingDonation = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingDonation) {
            // ถ้ามีข้อมูลแล้ว ให้อัพเดทจำนวนครั้งและยอดเงิน
            $newNumber = $existingDonation['number'] + 1;
            $newAmount = $existingDonation['amount'] + $amount;
            
            $updateStmt = $pdo->prepare("UPDATE donate SET number = ?, amount = ?, date = NOW() WHERE user_id = ?");
            $updateStmt->execute([$newNumber, $newAmount, $user_id]);
        } else {
            // ถ้าไม่มีข้อมูล ให้เพิ่มข้อมูลใหม่
            $insertStmt = $pdo->prepare("INSERT INTO donate (user_id, user_name, amount, number, date) VALUES (?, ?, ?, 1, NOW())");
            $insertStmt->execute([$user_id, $user_name, $amount]);
            $newAmount = $amount;
            $newNumber = 1;
        }
        
        // ลบยอดเงินจาก balance
        $newBalance = $currentBalance - $amount;
        $updateBalanceStmt = $pdo->prepare("UPDATE balance SET amount = ? WHERE user_id = ?");
        $updateBalanceStmt->execute([$newBalance, $user_id]);
        
        // commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'บริจาคสำเร็จ',
            'data' => [
                'user_id' => $user_id,
                'user_name' => $user_name,
                'donated_amount' => $amount,
                'total_donated' => $newAmount,
                'donation_count' => $newNumber,
                'remaining_balance' => $newBalance,
                'date' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (PDOException $e) {
        // rollback transaction ในกรณีที่เกิดข้อผิดพลาด
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
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
