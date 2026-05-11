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
    
    // อัพเดตสถานะในตาราง history_payment เป็น failed
    $stmt = $pdo->prepare("UPDATE history_payment SET status = 'failed' WHERE user_id = ? AND amount = ? AND date = ?");
    $result = $stmt->execute([$userId, $amount, $date]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'ปฏิเสธการฝากเงินสำเร็จ'
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
