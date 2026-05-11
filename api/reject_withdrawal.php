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
    
    // เริ่ม transaction
    $pdo->beginTransaction();
    
    try {
        // ดึงข้อมูล user_name จาก history_payment ก่อน
        $stmt = $pdo->prepare("SELECT user_name FROM history_payment WHERE user_id = ? AND amount = ? AND date = ? AND type = 'ถอนเงิน' AND status = 'pending' LIMIT 1");
        $stmt->execute([$userId, $amount, $date]);
        $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$paymentData) {
            $pdo->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบข้อมูลการถอนเงินที่รอดำเนินการ หรือได้ดำเนินการไปแล้ว'
            ]);
            exit();
        }
        
        $userName = $paymentData['user_name'] ?? 'user_' . $userId;
        
        // อัพเดตสถานะในตาราง history_payment เป็น 'failed'
        $stmt = $pdo->prepare("UPDATE history_payment SET status = 'failed' WHERE user_id = ? AND amount = ? AND date = ? AND type = 'ถอนเงิน' AND status = 'pending'");
        $result = $stmt->execute([$userId, $amount, $date]);
        
        if ($result && $stmt->rowCount() > 0) {
            // คืนเงินกลับไปในตาราง balance โดยบวกยอดเงินเข้าไป
            // ตรวจสอบว่าผู้ใช้มีข้อมูลในตาราง balance หรือไม่
            $stmt = $pdo->prepare("SELECT amount FROM balance WHERE user_id = ?");
            $stmt->execute([$userId]);
            $balanceData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($balanceData) {
                // ถ้ามีข้อมูลแล้ว ให้บวกยอดเงินกลับไป
                $newBalance = $balanceData['amount'] + $amount;
                $stmt = $pdo->prepare("UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?");
                $stmt->execute([$newBalance, $userId]);
            } else {
                // ถ้ายังไม่มีข้อมูล ให้สร้างใหม่
                $stmt = $pdo->prepare("INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$userId, $userName, $amount]);
            }
            
            // commit transaction
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'ปฏิเสธการถอนเงินสำเร็จ และคืนเงินกลับไปในบัญชีแล้ว'
            ]);
        } else {
            // rollback transaction
            $pdo->rollback();
            
            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถอัพเดตสถานะได้'
            ]);
        }
    } catch (Exception $e) {
        // rollback transaction
        $pdo->rollback();
        throw $e;
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
