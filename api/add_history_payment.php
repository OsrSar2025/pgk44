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

    if ($amount < 2000) {
        echo json_encode(['success' => false, 'message' => 'จำนวนเงินต้องไม่น้อยกว่า 2,000 บาท']);
        exit;
    }

    // บันทึกข้อมูลลง table history_payment
    $stmt = $pdo->prepare("INSERT INTO history_payment (user_id, user_name, type, amount, date, status) VALUES (?, ?, 'เติมเงิน', ?, NOW(), 'pending')");
    $result = $stmt->execute([$user_id, $user_name, $amount]);

    if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'บันทึกข้อมูลการเติมเงินเรียบร้อยแล้ว',
                    'data' => [
                        'user_id' => $user_id,
                        'user_name' => $user_name,
                        'type' => 'เติมเงิน',
                        'amount' => $amount,
                        'status' => 'pending',
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
