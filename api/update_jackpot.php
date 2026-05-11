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
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $userId = $input['user_id'] ?? '';
    $userName = $input['user_name'] ?? '';
    $date = $input['date'] ?? '';
    $jackpotAmount = floatval($input['jackpot'] ?? 0);
    
    // Validate required fields
    if (empty($userId) || empty($date) || $jackpotAmount <= 0) {
        throw new Exception('Missing required fields: user_id, date, and jackpot must be greater than 0');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update jackpot in bet_history table for specific user and date with status Pending
        $updateBetSql = "UPDATE bet_history 
                        SET jackpot = ? 
                        WHERE user_id = ? 
                        AND date = ?
                        AND (status = 'Pending' OR status = 'pending')";
        
        $updateBetStmt = $pdo->prepare($updateBetSql);
        $updateBetStmt->execute([$jackpotAmount, $userId, $date]);
        
        if ($updateBetStmt->rowCount() === 0) {
            throw new Exception('ไม่พบข้อมูลการเดิมพันที่มีสถานะ Pending');
        }
        
        // เพิ่มยอดเงินแจ็คพอตเข้า table balance
        // ตรวจสอบว่า user มีข้อมูลใน balance หรือไม่
        $checkBalanceSql = "SELECT amount FROM balance WHERE user_id = ?";
        $checkBalanceStmt = $pdo->prepare($checkBalanceSql);
        $checkBalanceStmt->execute([$userId]);
        $existingBalance = $checkBalanceStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingBalance) {
            // ถ้ามีอยู่แล้ว ให้บวกยอดเงิน
            $updateBalanceSql = "UPDATE balance SET amount = amount + ?, date = NOW() WHERE user_id = ?";
            $updateBalanceStmt = $pdo->prepare($updateBalanceSql);
            $updateBalanceStmt->execute([$jackpotAmount, $userId]);
        } else {
            // ถ้ายังไม่มี ให้สร้างใหม่
            $insertBalanceSql = "INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())";
            $insertBalanceStmt = $pdo->prepare($insertBalanceSql);
            $insertBalanceStmt->execute([$userId, $userName, $jackpotAmount]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'บันทึกยอดเงินแจ็คพอตและเพิ่มยอดเงินในบัญชีสำเร็จ',
            'user_id' => $userId,
            'user_name' => $userName,
            'date' => $date,
            'jackpot_amount' => $jackpotAmount
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
