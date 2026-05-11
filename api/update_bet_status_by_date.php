<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';
    
    // ตั้ง PHP timezone เป็นเวลาไทย
    date_default_timezone_set('Asia/Bangkok');
    
    // ตั้ง MySQL timezone เป็นเวลาไทยอีกครั้งเพื่อให้แน่ใจ
    $pdo->exec("SET time_zone = '+07:00'");

    // รับข้อมูลจาก POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug log
    error_log('Update bet status by date request: ' . json_encode($input));
    
    $user_id = $input['user_id'] ?? '';
    $status = $input['status'] ?? '';
    $bet_id = $input['bet_id'] ?? null;  // รับ bet_id ที่ส่งมา (id จริงจาก database)
    // ใช้ profit ที่ส่งมาเฉพาะเมื่อส่งมาคลาดเคลื่อน (จะให้ API คำนวณเองเสมอ)
    $sent_profit = $input['profit'] ?? null;

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($user_id) || empty($status)) {
        error_log('Missing required data: user_id=' . $user_id . ', status=' . $status);
        echo json_encode(['success' => false, 'message' => 'User ID and status are required', 'received' => $input]);
        exit;
    }

    // ตรวจสอบค่า auto_win ของผู้ใช้
    $autoWinStmt = $pdo->prepare("SELECT auto_win FROM user WHERE id = ?");
    $autoWinStmt->execute([$user_id]);
    $autoWinValue = intval($autoWinStmt->fetchColumn() ?? 0);

    if ($autoWinValue === 1) {
        if ($status !== 'red win' && $status !== 'blue win') {
            $status = 'red win';
        }
    } else {
        $status = 'lose';
    }

    // ดึง pending bet ล่าสุดของ user นี้ (ไม่มี id column ต้องใช้ user_id + date + status)
    error_log('Fetching latest pending bet for user_id=' . $user_id);
    $bet_sql = "SELECT user_id, betting_red, betting_blue, percentage, date, status FROM bet_history WHERE user_id = ? AND status = 'pending' ORDER BY date DESC LIMIT 1";
    $bet_stmt = $pdo->prepare($bet_sql);
    $bet_stmt->execute([$user_id]);
    $bet_data = $bet_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bet_data) {
        error_log('❌ No pending bet found for user_id=' . $user_id);
        echo json_encode(['success' => false, 'message' => 'No pending bet found']);
        exit;
    }
    
    error_log('✅ Bet data found - user_id: ' . $bet_data['user_id'] . ', date: ' . $bet_data['date'] . ', betting_red: ' . $bet_data['betting_red'] . ', betting_blue: ' . $bet_data['betting_blue'] . ', percentage: ' . $bet_data['percentage']);
    
    // คำนวณ profit ใหม่ตาม percentage และยอดเดิมพัน - ALWAYS CALCULATE, NEVER USE SENT VALUE
    $calculated_profit = 0;
    if ($bet_data) {
        $percentage = floatval($bet_data['percentage']);
        $betting_red = intval($bet_data['betting_red']);
        $betting_blue = intval($bet_data['betting_blue']);
        
        // percentage ใน database เป็นเปอร์เซ็นต์ (เช่น 5, 15, 25 = 5%, 15%, 25%)
        // ต้องหารด้วย 100 เพื่อแปลงเป็นทศนิยม (0.05, 0.15, 0.25)
        $percentage_multiplier = $percentage / 100;
        
        error_log('=== Profit Calculation ===');
        error_log('Status: ' . $status);
        error_log('Percentage: ' . $percentage . '%');
        error_log('Percentage multiplier: ' . $percentage_multiplier);
        error_log('Betting Red: ' . $betting_red);
        error_log('Betting Blue: ' . $betting_blue);
        
        // คำนวณ profit ตามทีมที่ชนะ (เก็บ profit ของฝั่งที่ชนะไว้ใน database)
        if ($status === 'red win') {
            // แดงชนะ - คำนวณจากยอดเดิมพันแดง × เปอร์เซ็นต์
            // ตัวอย่าง: 250,000 × 25% = 250,000 × 0.25 = 62,500
            if ($betting_red > 0) {
                $calculated_profit = floor($betting_red * $percentage_multiplier);
                error_log('Red win - Profit = ' . $betting_red . ' × ' . $percentage . '% = ' . $calculated_profit);
                
                // ตรวจสอบค่าที่คำนวณได้
                if ($calculated_profit != ($betting_red * $percentage_multiplier)) {
                    error_log('WARNING: Floor function changed result from ' . ($betting_red * $percentage_multiplier) . ' to ' . $calculated_profit);
                }
            } else {
                $calculated_profit = 0;
                error_log('Red win but betting_red = 0, no profit');
            }
        } else if ($status === 'blue win') {
            // น้ำเงินชนะ - คำนวณจากยอดเดิมพันน้ำเงิน × เปอร์เซ็นต์
            if ($betting_blue > 0) {
                $calculated_profit = floor($betting_blue * $percentage_multiplier);
                error_log('Blue win - Profit = ' . $betting_blue . ' × ' . $percentage . '% = ' . $calculated_profit);
            } else {
                $calculated_profit = 0;
                error_log('Blue win but betting_blue = 0, no profit');
            }
        } else {
            // ไม่ชนะ - ไม่มีกำไร
            $calculated_profit = 0;
            error_log('Loss - no profit');
        }
        
        error_log('Final calculated profit: ' . $calculated_profit);
        error_log('=======================');
    } else {
        // ถ้าไม่เจอข้อมูล bet ให้ส่ง error (ไม่ควรเกิดขึ้น)
        error_log('❌ ERROR: No bet data found but bet_id exists');
        $calculated_profit = 0;
        echo json_encode(['success' => false, 'message' => 'No bet data found']);
        exit;
    }
    
    if ($autoWinValue === 1) {
        if (!in_array($status, ['red win', 'blue win'], true)) {
            $status = 'red win';
        }
    } else {
        $status = 'lose';
    }
    
    // อัปเดตสถานะการเดิมพัน กำไร และเวลา (ใช้เวลาปัจจุบันเมื่อเปลี่ยนสถานะ)
    // ใช้ NOW() เพื่อให้เป็นเวลาไทย (MySQL ตั้ง timezone เป็น +07:00 แล้วใน db_connect.php)
    $update_sql = "UPDATE bet_history SET status = ?, profit = ?, date = NOW() WHERE user_id = ? AND date = ? AND betting_red = ? AND betting_blue = ? AND status = 'pending'";
    
    error_log('🔧 EXECUTING UPDATE:');
    error_log('   UPDATE bet_history SET status = "' . $status . '", profit = ' . $calculated_profit . ', date = NOW()');
    error_log('   WHERE user_id = ' . $user_id . ', date = ' . $bet_data['date'] . ', betting_red = ' . $bet_data['betting_red'] . ', betting_blue = ' . $bet_data['betting_blue']);
    
    $stmt = $pdo->prepare($update_sql);
    $result = $stmt->execute([
        $status, 
        $calculated_profit, 
        $user_id, 
        $bet_data['date'],
        $bet_data['betting_red'],
        $bet_data['betting_blue']
    ]);
    
    error_log('✅ Update executed - user_id: ' . $user_id . ', date: ' . $bet_data['date'] . ', calculated_profit: ' . $calculated_profit . ', status: ' . $status);
    
    // ตรวจสอบจำนวนแถวที่ถูกอัปเดต
    $affected_rows = $stmt->rowCount();
    
    error_log('📊 Update result: affected_rows=' . $affected_rows . ', user_id=' . $user_id . ', status=' . $status);
    
    // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if (!$result || $affected_rows === 0) {
        error_log('❌ UPDATE FAILED! user_id=' . $user_id . ', affected_rows=' . $affected_rows);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update bet status',
            'affected_rows' => $affected_rows
        ]);
        exit;
    }
    
    error_log('✅ UPDATE SUCCESS! user_id=' . $user_id . ' updated with profit=' . $calculated_profit);

    if ($result && $affected_rows > 0) {
        // ถ้าสถานะเป็น red win หรือ blue win และมีกำไร ให้อัปเดตยอดเงินในตาราง balance
        error_log('🔍 Checking if balance should be updated: status=' . $status . ', calculated_profit=' . $calculated_profit);
        if (($status === 'red win' || $status === 'blue win') && $calculated_profit > 0) {
            error_log('Updating user balance for winning bet: user_id=' . $user_id . ', calculated_profit=' . $calculated_profit);
            
            try {
                // ดึงข้อมูลการเดิมพันเพื่อคำนวณยอดเงินที่ต้องคืน (ใช้ bet_data ที่ query ไว้แล้ว)
                if ($bet_data) {
                // คำนวณยอดเงินที่ต้องคืน = ทุนทั้งสองฝั่ง + กำไรทั้งสองฝั่ง
                // ตัวอย่าง: เดิมพันฝั่งละ 1,000,000 (รวม 2,000,000) 
                // กำไรฝั่งละ 350,000 (รวม 700,000)
                // ยอดคืน = 2,000,000 + 700,000 = 2,700,000
                
                $betting_red = intval($bet_data['betting_red']);
                $betting_blue = intval($bet_data['betting_blue']);
                $percentage_multiplier = floatval($bet_data['percentage']) / 100;
                
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
                
                if ($status === 'red win') {
                    error_log('Red win: Returning both sides capital + both sides profit');
                } else if ($status === 'blue win') {
                    error_log('Blue win: Returning both sides capital + both sides profit');
                } else {
                    $total_return = 0;
                }
                    
                    error_log('📊 CALCULATING BALANCE UPDATE:');
                    error_log('  - Status: ' . $status);
                    error_log('  - Betting red: ' . $betting_red);
                    error_log('  - Betting blue: ' . $betting_blue);
                    error_log('  - Profit red: ' . $profit_red);
                    error_log('  - Profit blue: ' . $profit_blue);
                    error_log('  - Total capital (both sides): ' . ($betting_red + $betting_blue));
                    error_log('  - Total profit (both sides): ' . ($profit_red + $profit_blue));
                    error_log('  - Total return (capital + profit): ' . $total_return);
                    error_log('  - Will add this to balance table');
                    
                    if ($total_return > 0) {
                        // ตรวจสอบว่าผู้ใช้มีข้อมูลในตาราง balance หรือไม่
                        $check_sql = "SELECT user_id, amount FROM balance WHERE user_id = ?";
                        $check_stmt = $pdo->prepare($check_sql);
                        $check_stmt->execute([$user_id]);
                        $existing_user = $check_stmt->fetch();
                        
                        if ($existing_user) {
                            // ผู้ใช้มีข้อมูลอยู่แล้ว - อัปเดตยอดเงิน
                            $new_amount = $existing_user['amount'] + $total_return;
                            
                            $update_sql = "UPDATE balance SET amount = ?, date = NOW() WHERE user_id = ?";
                            $update_stmt = $pdo->prepare($update_sql);
                            $update_result = $update_stmt->execute([$new_amount, $user_id]);
                            
                            if ($update_result) {
                                error_log('Balance updated successfully: user_id=' . $user_id . ', old_amount=' . $existing_user['amount'] . ', total_return=' . $total_return . ', new_amount=' . $new_amount);
                            } else {
                                error_log('Failed to update balance for user_id=' . $user_id);
                            }
                        } else {
                            // ผู้ใช้ไม่มีข้อมูล - สร้างข้อมูลใหม่
                            $user_name = "user_" . $user_id;
                            
                            $insert_sql = "INSERT INTO balance (user_id, user_name, amount, date) VALUES (?, ?, ?, NOW())";
                            $insert_stmt = $pdo->prepare($insert_sql);
                            $insert_result = $insert_stmt->execute([$user_id, $user_name, $total_return]);
                            
                            if ($insert_result) {
                                error_log('New balance record created: user_id=' . $user_id . ', user_name=' . $user_name . ', amount=' . $total_return);
                            } else {
                                error_log('Failed to create new balance record for user_id=' . $user_id);
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
            'user_id' => $user_id,
            'status' => $status
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update bet status - no rows affected',
            'affected_rows' => $affected_rows,
            'user_id' => $user_id,
            'status' => $status
        ]);
    }

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
