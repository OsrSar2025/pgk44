<?php
error_reporting(0);
ini_set('display_errors', 0);

// ตรวจสอบว่าเป็น CLI หรือ HTTP request
$isCli = php_sapi_name() === 'cli' || !isset($_SERVER['REQUEST_METHOD']);

// ถ้าไม่ใช่ CLI ให้ส่ง header
if (!$isCli) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

try {
    include 'db_connect.php';
    
    // ตั้ง PHP timezone เป็นเวลาไทย
    date_default_timezone_set('Asia/Bangkok');
    
    // ตั้ง MySQL timezone เป็นเวลาไทยอีกครั้งเพื่อให้แน่ใจ
    $pdo->exec("SET time_zone = '+07:00'");

    // อนุญาตให้อัปเดตเฉพาะตอนหน้า pkg44 ส่ง trigger ว่า "รางวัลหมดแล้ว"
    // เพื่อให้เปลี่ยนสถานะพร้อมกันทั้งรอบ และไม่อัปเดตก่อนเวลา
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) $input = [];
    $trigger = $input['trigger'] ?? ($_GET['trigger'] ?? '');
    if ($trigger !== 'prize_end') {
        echo json_encode([
            'success' => true,
            'message' => 'Skipped: waiting for prize_end trigger',
            'updated_count' => 0,
            'expired_bets_found' => 0,
            'results' => []
        ]);
        exit;
    }
    // เมื่อได้ trigger รางวัลหมด ให้ update pending ทั้งหมดทันที (พร้อมกันทั้งรอบ)
    $stmt = $pdo->prepare("
        SELECT b.*, COALESCE(u.auto_win, 0) AS auto_win
        FROM bet_history b
        LEFT JOIN user u ON b.user_id = u.id
        WHERE b.status = 'pending'
        ORDER BY b.date ASC
    ");
    $stmt->execute();
    $expiredBets = $stmt->fetchAll();
    
    $updatedCount = 0;
    $results = [];
    $lastAutoWinSide = 'blue';
    
    foreach ($expiredBets as $bet) {
        $autoWinFlag = intval($bet['auto_win']);
        $betting_red = intval($bet['betting_red']);
        $betting_blue = intval($bet['betting_blue']);
        $percentage = floatval($bet['percentage']);
        $percentage_multiplier = $percentage / 100;

        if ($autoWinFlag === 1) {
            if ($betting_red > 0 && $betting_blue > 0) {
                $lastAutoWinSide = ($lastAutoWinSide === 'red') ? 'blue' : 'red';
                $winStatus = $lastAutoWinSide === 'red' ? 'red win' : 'blue win';
            } elseif ($betting_red > 0) {
                $winStatus = 'red win';
                $lastAutoWinSide = 'red';
            } elseif ($betting_blue > 0) {
                $winStatus = 'blue win';
                $lastAutoWinSide = 'blue';
            } else {
                $lastAutoWinSide = ($lastAutoWinSide === 'red') ? 'blue' : 'red';
                $winStatus = $lastAutoWinSide === 'red' ? 'red win' : 'blue win';
            }
        } else {
            $winStatus = 'lose';
            $lastAutoWinSide = 'blue';
        }
        
        try {
            // คำนวณ profit ตาม percentage จากฐานข้อมูล (เหมือนกับ update_bet_status_by_date.php)
            $calculated_profit = 0;
            if ($winStatus === 'red win' && $betting_red > 0) {
                $calculated_profit = floor($betting_red * $percentage_multiplier);
            } else if ($winStatus === 'blue win' && $betting_blue > 0) {
                $calculated_profit = floor($betting_blue * $percentage_multiplier);
            }
            
            // อัปเดตสถานะ profit และเวลา (ใช้เวลาปัจจุบันเมื่อเปลี่ยนสถานะ) ใน bet_history
            // ใช้ NOW() เพื่อให้เป็นเวลาไทย (MySQL ตั้ง timezone เป็น +07:00 แล้ว)
            $updateStmt = $pdo->prepare("UPDATE bet_history SET status = ?, profit = ?, date = NOW() WHERE user_id = ? AND date = ? AND betting_red = ? AND betting_blue = ? AND status = 'pending'");
            $result = $updateStmt->execute([
                $winStatus, 
                $calculated_profit, 
                $bet['user_id'], 
                $bet['date'],
                $bet['betting_red'],
                $bet['betting_blue']
            ]);
            
            if ($result && $updateStmt->rowCount() > 0) {
                $updatedCount++;
                
                // คำนวณ total_return = ทุนทั้งสองฝั่ง + กำไรทั้งสองฝั่ง
                // ตัวอย่าง: เดิมพันฝั่งละ 1,000,000 (รวม 2,000,000)
                // กำไรฝั่งละ 350,000 (รวม 700,000)
                // ยอดคืน = 2,000,000 + 700,000 = 2,700,000
                
                // คำนวณกำไรของแต่ละฝั่ง
                $profit_red = 0;
                $profit_blue = 0;
                
                if ($betting_red > 0) {
                    $profit_red = floor($betting_red * $percentage_multiplier);
                }
                
                if ($betting_blue > 0) {
                    $profit_blue = floor($betting_blue * $percentage_multiplier);
                }
                
                // คำนวณยอดคืน = ทุนทั้งสองฝั่ง + กำไรทั้งสองฝั่ง
                $total_return = ($betting_red + $betting_blue) + ($profit_red + $profit_blue);
                
                // อัปเดต balance ด้วย total_return
                if ($total_return > 0 && ($winStatus === 'red win' || $winStatus === 'blue win')) {
                    $check_sql = "SELECT user_id, amount FROM balance WHERE user_id = ?";
                    $check_stmt = $pdo->prepare($check_sql);
                    $check_stmt->execute([$bet['user_id']]);
                    $existing_user = $check_stmt->fetch();
                    
                    if ($existing_user) {
                        $new_amount = $existing_user['amount'] + $total_return;
                        $update_sql = "UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_sql);
                        $update_result = $update_stmt->execute([$new_amount, $bet['user_id']]);
                        
                        if ($update_result) {
                            error_log("Balance updated: user_id={$bet['user_id']}, old={$existing_user['amount']}, total_return={$total_return}, new={$new_amount}");
                        }
                    } else {
                        $user_name = "user_" . $bet['user_id'];
                        $insert_sql = "INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())";
                        $insert_stmt = $pdo->prepare($insert_sql);
                        $insert_result = $insert_stmt->execute([$bet['user_id'], $user_name, $total_return]);
                        
                        if ($insert_result) {
                            error_log("Balance created: user_id={$bet['user_id']}, amount={$total_return}");
                        }
                    }
                }
                
                $results[] = [
                    'user_id' => $bet['user_id'],
                    'date' => $bet['date'],
                    'old_status' => 'pending',
                    'new_status' => $winStatus,
                    'profit_red' => $profit_red,
                    'profit_blue' => $profit_blue,
                    'total_profit' => $profit_red + $profit_blue,
                    'total_return' => $total_return
                ];
                
                error_log("Auto-updated bet: user_id={$bet['user_id']}, status={$winStatus}, profit_red={$profit_red}, profit_blue={$profit_blue}, total_return={$total_return}");
            }
        } catch (Exception $e) {
            error_log("Error updating bet for user_id={$bet['user_id']}: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Auto-update completed',
        'updated_count' => $updatedCount,
        'expired_bets_found' => count($expiredBets),
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    error_log('Database error in auto_update_pending_bets.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error in auto_update_pending_bets.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
