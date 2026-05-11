<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    include 'db_connect.php';

    // ดึง bets ทั้งหมดที่มี status เป็น red win หรือ blue win
    $sql = "SELECT id, betting_red, betting_blue, percentage, status FROM bet_history WHERE status IN ('red win', 'blue win') ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated_count = 0;
    $errors = [];
    
    foreach ($bets as $bet) {
        $bet_id = $bet['id'];
        $betting_red = intval($bet['betting_red']);
        $betting_blue = intval($bet['betting_blue']);
        $percentage = floatval($bet['percentage']);
        $status = $bet['status'];
        
        // คำนวณ profit ใหม่
        $percentage_multiplier = $percentage / 100;
        $calculated_profit = 0;
        
        if ($status === 'red win') {
            $calculated_profit = $betting_red * $percentage_multiplier;
        } else if ($status === 'blue win') {
            $calculated_profit = $betting_blue * $percentage_multiplier;
        }
        
        // อัปเดต profit ใน database
        $update_sql = "UPDATE bet_history SET profit = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $result = $update_stmt->execute([$calculated_profit, $bet_id]);
        
        if ($result) {
            $updated_count++;
            echo "Updated ID {$bet_id}: betting_red={$betting_red}, betting_blue={$betting_blue}, percentage={$percentage}%, profit={$calculated_profit}\n";
        } else {
            $errors[] = "Failed to update bet ID {$bet_id}";
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Total bets found: " . count($bets) . "\n";
    echo "Successfully updated: {$updated_count}\n";
    echo "Errors: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\nErrors:\n";
        foreach ($errors as $error) {
            echo "- {$error}\n";
        }
    }
    
    echo json_encode([
        'success' => true,
        'total_bets' => count($bets),
        'updated_count' => $updated_count,
        'errors' => count($errors),
        'errors_list' => $errors
    ]);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

