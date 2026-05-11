<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($input['user_id']) ? trim($input['user_id']) : '';
    $bankName = isset($input['bank_name']) ? trim($input['bank_name']) : '';
    $accountNumber = isset($input['account_number']) ? trim($input['account_number']) : '';
    $recipientName = isset($input['recipient_name']) ? trim($input['recipient_name']) : '';
    
    // Validate input
    if (empty($bankName) || empty($accountNumber) || empty($recipientName)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ]);
        exit;
    }
    
    try {
        // Insert bank account data (user_id is optional, can be NULL)
        $userIdValue = !empty($userId) ? $userId : null;
        $stmt = $pdo->prepare("INSERT INTO bank (user_id, bank_name, account_number, recipient_name, transaction_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userIdValue, $bankName, $accountNumber, $recipientName]);
        
        echo json_encode([
            'success' => true,
            'message' => 'บันทึกข้อมูลบัญชีธนาคารสำเร็จ',
            'bank_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
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
