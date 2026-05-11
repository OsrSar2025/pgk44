<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_connect.php';

try {
    // ดึง bet ล่าสุด
    $stmt = $pdo->query("SELECT user_id, betting_red, betting_blue, percentage, status, profit, date FROM bet_history ORDER BY date DESC LIMIT 5");
    $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ดึง VIP levels
    $stmt2 = $pdo->query("SELECT level, percentage FROM level_data ORDER BY id ASC");
    $levels = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'last_bets' => $bets,
        'vip_levels' => $levels,
        'message' => 'Check percentage values'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

