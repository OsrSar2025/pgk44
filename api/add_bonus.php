<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

// รับข้อมูล JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$user_id = $data['user_id'] ?? null;
$amount = $data['amount'] ?? null;

if (!$user_id || !$amount) {
    echo json_encode([
        'success' => false,
        'message' => 'ข้อมูลไม่ครบถ้วน'
    ]);
    exit;
}

if (!is_numeric($amount) || floatval($amount) <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'จำนวนเงินต้องมากกว่า 0'
    ]);
    exit;
}

try {
    // เริ่ม transaction
    $pdo->beginTransaction();
    
    // ดึงข้อมูลผู้ใช้
    $stmt = $pdo->prepare("SELECT id, username FROM user WHERE id = ?");
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
    
    $user_name = $user['username'] ?? '';
    $bonus_amount = floatval($amount);
    
    // ตรวจสอบว่า user_id มีข้อมูลใน table balance หรือไม่
    $stmt = $pdo->prepare("SELECT * FROM balance WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existingBalance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $old_amount = 0;
    $new_amount = $bonus_amount;
    
    if ($existingBalance) {
        // ถ้ามีข้อมูลแล้ว ให้บวก amount เข้าไป
        $old_amount = floatval($existingBalance['amount']);
        $new_amount = $old_amount + $bonus_amount;
        $stmt = $pdo->prepare("UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?");
        $stmt->execute([$new_amount, $user_id]);
    } else {
        // ถ้ายังไม่มีข้อมูล ให้เพิ่มข้อมูลใหม่
        $stmt = $pdo->prepare("INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $user_name, $bonus_amount]);
    }
    
    // บันทึกข้อมูลลง table history_payment
    $stmt = $pdo->prepare("INSERT INTO history_payment (user_id, user_name, type, amount, date, status) VALUES (?, ?, 'โบนัส', ?, NOW(), 'completed')");
    $stmt->execute([$user_id, $user_name, $bonus_amount]);
    
    // commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มโบนัสสำเร็จ',
        'data' => [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'bonus_amount' => $bonus_amount,
            'old_balance' => $old_amount,
            'new_balance' => $new_amount
        ]
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
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

