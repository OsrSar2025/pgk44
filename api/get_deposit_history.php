<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    // Get deposit history (type = เติมเงิน)
    $stmt = $pdo->prepare("
        SELECT 
            user_id,
            user_name,
            type,
            date,
            status,
            amount
        FROM history_payment 
        WHERE type = 'เติมเงิน'
        ORDER BY date DESC
    ");
    $stmt->execute();
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $deposits
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
