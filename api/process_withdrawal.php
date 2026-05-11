<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_POST['user_id'] ?? '';
    $user_name = $_POST['user_name'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $withdrawal_password = $_POST['withdrawal_password'] ?? '';
    
    if (empty($user_id) || empty($user_name) || empty($amount) || empty($withdrawal_password)) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }
    
    // ตรวจสอบขั้นต่ำถอน 100 บาท
    if ($amount < 100) {
        echo json_encode(['success' => false, 'message' => 'ยอดถอนขั้นต่ำ 100 บาท']);
        exit;
    }
    
    // ตรวจสอบรหัสผ่านการถอน
    $stmt = $pdo->prepare("SELECT withdrawal_code FROM password WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $passwordData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$passwordData || $passwordData['withdrawal_code'] !== $withdrawal_password) {
        echo json_encode(['success' => false, 'message' => 'รหัสผ่านการถอนไม่ถูกต้อง']);
        exit;
    }
    
    // ตรวจสอบยอดเงินใน balance
    $stmt = $pdo->prepare("SELECT amount FROM balance WHERE user_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $balanceData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$balanceData) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลยอดเงินในบัญชี']);
        exit;
    }
    
    $currentBalance = $balanceData['amount'];
    
    // ตรวจสอบยอดเงินเพียงพอหรือไม่
    if ($currentBalance < $amount) {
        echo json_encode(['success' => false, 'message' => 'ยอดเงินในบัญชีไม่เพียงพอ']);
        exit;
    }
    
    // เริ่ม transaction
    $pdo->beginTransaction();
    
    try {
        // ลบยอดเงินจาก balance
        $newBalance = $currentBalance - $amount;
        $stmt = $pdo->prepare("UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?");
        $stmt->execute([$newBalance, $user_id]);
        
        // บันทึกข้อมูลการถอนใน history_payment
        $stmt = $pdo->prepare("INSERT INTO history_payment (user_id, user_name, type, amount, date, status) VALUES (?, ?, 'ถอนเงิน', ?, NOW(), 'pending')");
        $stmt->execute([$user_id, $user_name, $amount]);
        
        // commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'ส่งคำขอถอนเงินสำเร็จ',
            'data' => [
                'old_balance' => $currentBalance,
                'new_balance' => $newBalance,
                'withdrawal_amount' => $amount
            ]
        ]);
        
    } catch (Exception $e) {
        // rollback transaction
        $pdo->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
