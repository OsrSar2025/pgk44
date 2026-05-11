<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // รับข้อมูลจาก JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($input['user_id']) ? trim($input['user_id']) : '';
    $user_name = isset($input['user_name']) ? trim($input['user_name']) : '';
    $type = isset($input['type']) ? trim($input['type']) : 'recharge';
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0;
    $payment_method = isset($input['payment_method']) ? trim($input['payment_method']) : '';
    $status = isset($input['status']) ? trim($input['status']) : 'pending';

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    if (empty($user_name)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        exit;
    }

    if (empty($payment_method)) {
        echo json_encode(['success' => false, 'message' => 'Payment method is required']);
        exit;
    }

    // บันทึกข้อมูลลง table history_payment
    $stmt = $pdo->prepare("INSERT INTO history_payment (user_id, user_name, type, date, status) VALUES (?, ?, ?, NOW(), ?)");
    $result = $stmt->execute([$user_id, $user_name, $type, $status]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'บันทึกข้อมูลการเติมเงินเรียบร้อยแล้ว',
            'data' => [
                'user_id' => $user_id,
                'user_name' => $user_name,
                'type' => $type,
                'status' => $status,
                'date' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถบันทึกข้อมูลได้'
        ]);
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
