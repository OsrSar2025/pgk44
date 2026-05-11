<?php
// Prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once 'db_connect.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log received data for debugging
    error_log('Update Bank API - Received data: ' . json_encode($input));
    
    // รองรับทั้ง 'id' และ 'bank_id'
    $id = (int)($input['bank_id'] ?? $input['id'] ?? 0);
    $user_id = trim($input['user_id'] ?? '');
    $bank_name = trim($input['bank_name'] ?? '');
    $account_number = trim($input['account_number'] ?? '');
    $recipient_name = trim($input['recipient_name'] ?? '');
    
    error_log("Update Bank - ID: $id, User ID: $user_id, Bank: $bank_name, Account: $account_number, Recipient: $recipient_name");
    
    // Validate required fields
    if (!$id || $id <= 0) {
        throw new Exception('Valid Bank ID is required');
    }
    
    if (empty($user_id)) {
        throw new Exception('User ID is required');
    }
    
    if (empty($bank_name)) {
        throw new Exception('Bank name is required');
    }
    
    if (empty($account_number)) {
        throw new Exception('Account number is required');
    }
    
    if (empty($recipient_name)) {
        throw new Exception('Recipient name is required');
    }
    
    // Check if bank exists and belongs to user
    $stmt = $pdo->prepare("SELECT id, user_id FROM bank WHERE id = ?");
    $stmt->execute([$id]);
    $existing_bank = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_bank) {
        throw new Exception('Bank account not found');
    }
    
    error_log("Found existing bank: " . json_encode($existing_bank));
    
    // Update bank record with transaction_date
    $sql = "UPDATE bank 
            SET bank_name = ?, 
                account_number = ?, 
                recipient_name = ?, 
                transaction_date = NOW() 
            WHERE id = ? AND user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $bank_name, 
        $account_number, 
        $recipient_name, 
        $id,
        $user_id
    ]);
    
    if (!$result) {
        throw new Exception('Failed to execute update query');
    }
    
    $rowCount = $stmt->rowCount();
    error_log("Update result - Rows affected: $rowCount");
    
    // Verify update by fetching the record
    $stmt = $pdo->prepare("SELECT * FROM bank WHERE id = ?");
    $stmt->execute([$id]);
    $updated_bank = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Updated bank data: " . json_encode($updated_bank));
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทข้อมูลธนาคารสำเร็จ',
        'rows_affected' => $rowCount,
        'data' => $updated_bank
    ]);
    
} catch(PDOException $e) {
    error_log('Update Bank PDO Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    error_log('Update Bank Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>