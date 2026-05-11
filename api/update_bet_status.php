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
    
    // Debug log
    error_log('Update bet status request: ' . json_encode($input));
    
    $bet_id = $input['bet_id'] ?? '';
    $status = $input['status'] ?? '';
    $profit = $input['profit'] ?? 0;

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($bet_id) || empty($status)) {
        error_log('Missing bet_id or status: bet_id=' . $bet_id . ', status=' . $status);
        echo json_encode(['success' => false, 'message' => 'Bet ID and status are required', 'received' => $input]);
        exit;
    }

    // อัปเดตสถานะการเดิมพันและกำไรเฉพาะแถวล่าสุดของ user_id นั้น
    $stmt = $pdo->prepare("UPDATE bet_history SET status = ?, profit = ? WHERE user_id = ? AND status = 'pending' ORDER BY date DESC LIMIT 1");
    $result = $stmt->execute([$status, $profit, $bet_id]);
    
    // ตรวจสอบจำนวนแถวที่ถูกอัปเดต
    $affected_rows = $stmt->rowCount();
    
    error_log('Update result: affected_rows=' . $affected_rows . ', bet_id=' . $bet_id . ', status=' . $status);

    if ($result && $affected_rows > 0) {
        // ถ้าสถานะเป็น red win หรือ blue win และมีกำไร ให้อัปเดตยอดเงินในตาราง balance
        if (($status === 'red win' || $status === 'blue win') && $profit > 0) {
            error_log('Updating user balance for winning bet: user_id=' . $bet_id . ', profit=' . $profit);
            
            try {
                // ดึงข้อมูลการเดิมพันเพื่อคำนวณยอดเงินที่ต้องคืน
                $bet_sql = "SELECT betting_red, betting_blue FROM bet_history WHERE user_id = ? AND status = ? ORDER BY date DESC LIMIT 1";
                $bet_stmt = $pdo->prepare($bet_sql);
                $bet_stmt->execute([$bet_id, $status]);
                $bet_data = $bet_stmt->fetch();
                
                if ($bet_data) {
                    // คำนวณยอดเงินที่ต้องคืน (ต้นทุน + กำไร)
                    $total_return = 0;
                    if ($status === 'red win' && $bet_data['betting_red'] > 0) {
                        $total_return = $bet_data['betting_red'] + $profit; // ต้นทุนแดง + กำไร
                    } else if ($status === 'blue win' && $bet_data['betting_blue'] > 0) {
                        $total_return = $bet_data['betting_blue'] + $profit; // ต้นทุนน้ำเงิน + กำไร
                    }
                    
                    error_log('Calculated total return: betting_red=' . $bet_data['betting_red'] . ', betting_blue=' . $bet_data['betting_blue'] . ', profit=' . $profit . ', total_return=' . $total_return);
                    
                    if ($total_return > 0) {
                        // ตรวจสอบว่าผู้ใช้มีข้อมูลในตาราง balance หรือไม่
                        $check_sql = "SELECT user_id, amount FROM balance WHERE user_id = ?";
                        $check_stmt = $pdo->prepare($check_sql);
                        $check_stmt->execute([$bet_id]);
                        $existing_user = $check_stmt->fetch();
                        
                        if ($existing_user) {
                            // ผู้ใช้มีข้อมูลอยู่แล้ว - อัปเดตยอดเงิน
                            $new_amount = $existing_user['amount'] + $total_return;
                            
                            $update_sql = "UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?";
                            $update_stmt = $pdo->prepare($update_sql);
                            $update_result = $update_stmt->execute([$new_amount, $bet_id]);
                            
                            if ($update_result) {
                                error_log('Balance updated successfully: user_id=' . $bet_id . ', old_amount=' . $existing_user['amount'] . ', total_return=' . $total_return . ', new_amount=' . $new_amount);
                            } else {
                                error_log('Failed to update balance for user_id=' . $bet_id);
                            }
                        } else {
                            // ผู้ใช้ไม่มีข้อมูล - สร้างข้อมูลใหม่
                            $user_name = "user_" . $bet_id;
                            
                            $insert_sql = "INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())";
                            $insert_stmt = $pdo->prepare($insert_sql);
                            $insert_result = $insert_stmt->execute([$bet_id, $user_name, $total_return]);
                            
                            if ($insert_result) {
                                error_log('New balance record created: user_id=' . $bet_id . ', user_name=' . $user_name . ', amount=' . $total_return);
                            } else {
                                error_log('Failed to create new balance record for user_id=' . $bet_id);
                            }
                        }
                    }
                }
            } catch (Exception $balance_error) {
                error_log('Error updating user balance: ' . $balance_error->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Bet status updated successfully',
            'affected_rows' => $affected_rows,
            'bet_id' => $bet_id,
            'status' => $status
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update bet status - no rows affected',
            'affected_rows' => $affected_rows,
            'bet_id' => $bet_id,
            'status' => $status
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
