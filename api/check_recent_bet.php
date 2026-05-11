<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_connect.php';

try {
    // ดึง bet ล่าสุดของ user_id = 24551
    $stmt = $pdo->prepare("SELECT user_id, betting_red, betting_blue, percentage, status, profit, date FROM bet_history WHERE user_id = 24551 ORDER BY date DESC LIMIT 1");
    $stmt->execute();
    $bet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bet) {
        // คำนวณกำไรที่ควรได้
        $percentage = floatval($bet['percentage']);
        $betting_amount = $bet['status'] === 'red win' ? intval($bet['betting_red']) : intval($bet['betting_blue']);
        $expected_profit = floor($betting_amount * ($percentage / 100));
        $expected_total_return = $betting_amount + $expected_profit;
        
        echo json_encode([
            'success' => true,
            'bet' => $bet,
            'calculation' => [
                'percentage' => $percentage . '%',
                'betting_amount' => $betting_amount,
                'expected_profit' => $expected_profit,
                'expected_total_return' => $expected_total_return,
                'actual_profit' => floatval($bet['profit']),
                'actual_total_return' => $betting_amount + floatval($bet['profit'])
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'No bet found']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

