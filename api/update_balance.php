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
    
    if (empty($user_id) || empty($user_name) || empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }
    
    // ตรวจสอบว่า user_id มีข้อมูลใน table balance หรือไม่
    $stmt = $pdo->prepare("SELECT * FROM balance WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existingBalance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingBalance) {
        // ถ้ามีข้อมูลแล้ว ให้บวก amount เข้าไป
        $newAmount = $existingBalance['amount'] + $amount;
        $stmt = $pdo->prepare("UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?");
        $result = $stmt->execute([$newAmount, $user_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'อัปเดตยอดเงินใน balance สำเร็จ',
                'action' => 'updated',
                'old_amount' => $existingBalance['amount'],
                'new_amount' => $newAmount,
                'added_amount' => $amount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดต balance ได้']);
        }
    } else {
        // ถ้ายังไม่มีข้อมูล ให้เพิ่มข้อมูลใหม่
        $stmt = $pdo->prepare("INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$user_id, $user_name, $amount]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'เพิ่มข้อมูลใน balance สำเร็จ',
                'action' => 'inserted',
                'new_amount' => $amount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถเพิ่มข้อมูลใน balance ได้']);
        }
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
