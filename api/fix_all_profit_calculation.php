<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

try {
    // ดึง bet ทั้งหมดที่มี status = 'red win' หรือ 'blue win'
    $stmt = $pdo->query("SELECT user_id, betting_red, betting_blue, percentage, status, profit, date FROM bet_history WHERE status IN ('red win', 'blue win') ORDER BY date DESC");
    $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    $errors = 0;
    $results = [];
    
    foreach ($bets as $bet) {
        $user_id = $bet['user_id'];
        $betting_red = intval($bet['betting_red']);
        $betting_blue = intval($bet['betting_blue']);
        $percentage = floatval($bet['percentage']);
        $status = $bet['status'];
        $date = $bet['date'];
        $old_profit = floatval($bet['profit']);
        
        // คำนวณ profit ใหม่
        $percentage_multiplier = $percentage / 100;
        $calculated_profit = 0;
        
        if ($status === 'red win') {
            if ($betting_red > 0) {
                $calculated_profit = floor($betting_red * $percentage_multiplier);
            }
        } else if ($status === 'blue win') {
            if ($betting_blue > 0) {
                $calculated_profit = floor($betting_blue * $percentage_multiplier);
            }
        }
        
        // ถ้า profit ที่คำนวณได้ต่างจาก profit เดิม ให้อัปเดต
        if ($calculated_profit != $old_profit) {
            try {
                $update_sql = "UPDATE bet_history SET profit = ? WHERE user_id = ? AND date = ? AND betting_red = ? AND betting_blue = ? AND status = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_result = $update_stmt->execute([
                    $calculated_profit,
                    $user_id,
                    $date,
                    $betting_red,
                    $betting_blue,
                    $status
                ]);
                
                if ($update_result && $update_stmt->rowCount() > 0) {
                    $updated++;
                    $results[] = [
                        'date' => $date,
                        'user_id' => $user_id,
                        'betting_red' => $betting_red,
                        'betting_blue' => $betting_blue,
                        'percentage' => $percentage,
                        'status' => $status,
                        'old_profit' => $old_profit,
                        'new_profit' => $calculated_profit
                    ];
                } else {
                    $errors++;
                }
            } catch (Exception $e) {
                $errors++;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'total_bets' => count($bets),
        'updated' => $updated,
        'errors' => $errors,
        'results' => $results,
        'message' => 'Profit recalculation completed'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

