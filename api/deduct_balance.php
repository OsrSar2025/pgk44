<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';

    // รับข้อมูลจาก POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? '';
    $amount = $input['amount'] ?? 0;

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($user_id) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'User ID and amount are required']);
        exit;
    }

    // หักยอดเงินจริงจากตาราง balance
    try {
        // ตรวจสอบยอดเงินปัจจุบัน
        $stmt = $pdo->prepare("SELECT amount FROM balance WHERE user_id = ? ORDER BY date DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $current_balance_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current_balance_data) {
            echo json_encode(['success' => false, 'message' => 'User balance not found']);
            exit;
        }
        
        $current_balance = $current_balance_data['amount'];
        
        // ตรวจสอบว่ายอดเงินเพียงพอหรือไม่
        if ($current_balance < $amount) {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance']);
            exit;
        }

        // หักเงินจริงจากตาราง balance
        $new_balance = $current_balance - $amount;
        $stmt = $pdo->prepare("UPDATE balance SET amount = ? WHERE user_id = ?");
        $result = $stmt->execute([$new_balance, $user_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Balance deducted successfully', 
                'new_balance' => $new_balance
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to deduct balance']);
        }
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }


} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
