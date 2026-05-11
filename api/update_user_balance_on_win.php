<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $user_id = $input['user_id'] ?? null;
    $profit_amount = $input['profit_amount'] ?? 0;
    $bet_status = $input['bet_status'] ?? null;
    
    if (!$user_id || $profit_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }
    
    // ตรวจสอบสถานะก่อนอัปเดต balance
    if ($bet_status !== 'red win' && $bet_status !== 'blue win') {
        echo json_encode([
            'success' => false, 
            'message' => 'Status is not winning status, skipping balance update',
            'bet_status' => $bet_status
        ]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Check if user exists in balance table
        $check_sql = "SELECT user_id, amount FROM balance WHERE user_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$user_id]);
        $existing_user = $check_stmt->fetch();
        
        if ($existing_user) {
            // User exists - update balance
            $new_amount = $existing_user['amount'] + $profit_amount;
            
            $update_sql = "UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$new_amount, $user_id]);
            
            $pdo->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Balance updated successfully for winning bet',
                'user_id' => $user_id,
                'old_amount' => $existing_user['amount'],
                'profit_added' => $profit_amount,
                'new_amount' => $new_amount,
                'action' => 'updated',
                'bet_status' => $bet_status
            ]);
            
        } else {
            // User doesn't exist - create new record
            $user_name = "user_$user_id";
            
            $insert_sql = "INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([$user_id, $user_name, $profit_amount]);
            
            $pdo->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'New balance record created successfully for winning bet',
                'user_id' => $user_id,
                'user_name' => $user_name,
                'amount' => $profit_amount,
                'action' => 'created',
                'bet_status' => $bet_status
            ]);
        }
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error updating user balance: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
